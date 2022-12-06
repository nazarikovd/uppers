<?php
/*

KTSSTUDIO API php library
php 7.4 support confirmed

GitHub:
https://github.com/nazarikovd/ktsapi

Functions:

ktsApi::request(*method, *bearer, params) - send simple request, like getShop, buy, getRating, complete, or other

ktsApi::getShopAvailibility(*bearer, *item) - get availability of any item in shop

ktsApi::auth(*access_token) - get Auhtentification Bearer by vk aceess_token

ktsApi::get(*url, headers, POST data) - simple curl with headers and POST support

ktsApi::signBeeline(*coins, *user id, *game id) - create request sign for Beeline Uppers request('complete'...)

ktsApi::error(*type, *text) - throws an error in class

* - required arguments

*/


class ktsApi
{
	protected $app_id; // VK App ID
	protected $api_url; // KTS backend endpoint
	
	public function __construct($api_url, $app_id)
	{
		
		if (empty($api_url) or empty($app_id))
		{
			$this->error('e', 'One of the arguments missing in class construct, args:' . json_encode(func_get_args()));	
		}
		
		$this->api_url = $api_url;
		$this->app_id = $app_id;
	}
	
	public function request($method, $token, $params = null)
	{
		
		$headers[] = "Authorization: Bearer " . $token;
		if (empty($token) or empty($method))
		{
			$this->error('e', 'One of the arguments missing in request(), args:' . json_encode(func_get_args()));
		}
		switch ($method)
		{
			case 'getShop':
				$a = $this->get($this->api_url . "/shop/list", $headers);
				$a = json_decode($a);
				$response['SHOP_RESPONSE'] = $a;
			break;
			case 'buy':
				if (empty($params))
				{
					$this->error('e', 'One of the arguments missing in request(), missing buy $params');
				}
				$postData = $params;
				$a = $this->get($this->api_url . "/shop/buy", $headers, $postData);
				$a = json_decode($a);
				$response['BUY_RESPONSE'] = $a;
			break;
			case 'getRating':
				$a = $this->get($this->api_url . "/game/rating", $headers);
				$a = json_decode($a);
				$response['RATING_RESPONSE'] = $a;
			break;
			case 'complete':
				switch ($this->app_id)
				{
					case 51430978:
						foreach (["data_center", "truth_lie"] as $game)
						{
							$postData = array(
								"type" => $game
							);
							$a = $this->get($this->api_url . "/game/create", $headers, $postData);
							$a = json_decode($a);
							for ($i = 1;$i <= $a
								->data->questions_number;$i++)
							{
								if ($game == "truth_lie")
								{
									$postData = array(
										"question_id" => $i,
										"answer" => true
									);
								}
								else
								{
									$postData = array(
										"question_id" => $i,
										"answer_id" => 0
									);
								}
								$b = $this->get($this->api_url . "/game/" . $game . "/answer", $headers, $postData);
							}
							$b = json_decode($b);
							$response[$game . '_RESPONSE'] = $b;
						}
						foreach (["nuclear_lab", "green_center", "improbability_center"] as $game)
						{
							$postData = array(
								"type" => $game
							);
							$a = $this->get($this->api_url . "/game/create", $headers, $postData);
							$a = json_decode($a);
							$postData = array(
								"type" => $game,
								"success" => true
							);
							$a = $this->get($this->api_url . "/game/finish", $headers, $postData);
							$a = json_decode($a);
						}
					break;
					case 51436679:
						if (empty($params))
						{
							$this->error('e', 'One of the arguments missing in request(), missing beeline uppers $params');
						}
						$a = $this->get($this->api_url . "/game/start", $headers, "{}");
						$a = json_decode($a);
						$game_id = $a
							->data->id;
						$postData = array(
							"id" => $game_id,
							"victory" => "true",
							"coins" => $params['coins'],
							"tutorial" => false,
							"sign" => $this->signBeeline($params['coins'], $params['id'], $game_id)
						);
						$a = $this->get($this->api_url . "/game/finish", $headers, $postData);
						$a = json_decode($a);
						$response['GAME_RESPONSE'] = $a;
					break;
					case 51437421:
						foreach ([1, 2, 3, 4, 5] as $level)
						{
							$postData = array(
								"level" => $level
							);
							$this->get($this->api_url . "/game/start", $headers, $postData);
							$postData = array(
								"level" => $level,
								"is_success" => true
							);
							$a = $this->get($this->api_url . "/game/finish", $headers, $postData);
							$a = json_decode($a);
							$response['LEVEL' . $level . '_RESPONSE'] = $a;
						}
						foreach ([2, 3, 4, 5, 6, 7, 8, 9, 10] as $open)
						{
							$postData = array(
								"id" => $open
							);
							$a = $this->get($this->api_url . "/article/open", $headers, $postData);
							$a = json_decode($a);
							$response['ARTICLE' . $open . '_RESPONSE'] = $a;
						}
						$postData = array(
							"name" => "allow_messages",
							"value" => true
						);
						$a = $this->get($this->api_url . "/user/permission", $headers, $postData);
						$a = json_decode($a);
						$response['PERMISSION_RESPONSE'] = $a;
						$postData = array(
							"name" => "onboarding",
							"value" => true
						);
						$a = $this->get($this->api_url . "/user/flag", $headers, $postData);
						$a = json_decode($a);
						$response['FLAG_RESPONSE'] = $a;
					break;
				}
			break;
			default:
				$a = $this->get($this->api_url . $method, $headers, $params);
				$a = json_decode($a);
				$response['API_RESPONSE'] = $a;
			break;
		}
		return json_decode(json_encode($response));
	}
	
	public function getShopAvailibility($token, $item)
	{
		
		if (empty($token) or empty($item))
		{
			$this->error('e', 'some arguments missing in getShopAvailibility(), args:' . json_encode(func_get_args()));
		}
		switch ($this->app_id)
		{
			case 51437421:
				$a = $this->request('getShop', $token)
					->SHOP_RESPONSE
					->data
					->$item->status;
			break;
			case 51430978:
				foreach ($this->request('getShop', $token)
					->SHOP_RESPONSE
					->data->prizes as $prize)
				{
					if ($prize->type == $item)
					{
						$a = $prize->available;
						break;
					}
				}
			break;
			case 51436679:
				//no need to check beeline for anything
				$this->error('w', 'no need to check beeline for anything!');
				return false;
			break;
			default:
				$this->error('w', $this->app_id . ' shop is not supported');
				return false;
			break;
		}
		if (!isset($a))
		{
			$this->error('w', $this->app_id . ' shop does not have ' . $item . '!');
			return false;
		}
		switch ($a)
		{
			case 'purchased':
				return false;
			break;
			case 'not_available':
				return false;
			break;
			case false:
				return false;
			break;
			default:
				return true;
			break;
		}
	}
	
	public function auth($access_token)
	{
		
		if (empty($access_token))
		{
			$this->error('e', 'User authorization failed: no access_token passed');
		}
		$a = $this->get("https://api.vk.com/method/apps.get?access_token=" . $access_token . "&v=5.131&app_id=" . $this->app_id);
		$b = json_decode($a);
		if(empty($b->response->items[0]->webview_url)){
			
			$this->error("e", "Can't auth with this token");
		}
		$b = $b
			->response
			->items[0]->webview_url;
		$VKSign = substr($b, strpos($b, "?") + 1);
		$headers = array(
			'origin' => 'https://prod-app51437421-b98559b05694.pages-ac.vk-apps.com',
			'access-control-allow-methods' => '*',
			'Access-Control-Request-Method' => 'GET'
		);
		$a = $this->get($this->api_url . "/user/auth?" . $VKSign, $headers);
		$b = json_decode($a);
		if(empty($b->data->token)){
			
			$this->error("e", "Auth error! ".$a);
		}
		$b = $b
			->data->token;
		return $b;
	}
	
	public function get($url, $headers = null, $postData = null)
	{
		
		$agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36';
		$ch = curl_init($url);
		if ($headers)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		if ($postData)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($ch);
		return $output;
	}
	
	public function signBeeline($coins, $id, $game_id)
	{
		
		$s1 = "leaderboard";
		$s2 = "ping";
		$s3 = "catalog";
		$ot = $coins . ($id ^ $game_id);
		$nt = hash('sha256', $s1 . $ot . $s2) . $s3;
		return hash('sha256', $nt);
	}
	
	private function error($type, $text)
	{
		
		switch ($type)
		{
			case 'w':
				trigger_error($text, E_USER_WARNING);
			break;
			case 'e':
				throw new Exception($text);
			break;
		}
	}
}

?>