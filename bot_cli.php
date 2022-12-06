<?php
include 'ktsApi/config.php'; // connect config
include 'ktsApi/ktsApi.php'; // connect library

$kts = new ktsApi(config::API_URL, config::APP_ID);

clear();
$token = readline('VK access_token or KTS Bearer:');

if(empty($token)){
	die("it cant be empty");
}
	
	if(strpos($token, 'vk1.') !== false){
		$check = $kts->get("https://api.vk.com/method/users.get?v=5.131&access_token=".$token);
		$check = json_decode($check);
		if(!empty($check->error)){
			die("invalid vk token");
		}
		$bear = $kts->auth($token);
	}

clear();

$id = readline('VK user ID:');
if(empty($id)){
	die("it cant be empty");
}





while(true){
	
	$play = $kts->request('complete', $bear, array('coins'=>'120', 'id'=>$id)); //get 120 coins
	clear();
	echo 'Your balance:'.PHP_EOL;
	echo $play->GAME_RESPONSE->data->coins.' coins'; //echo response
}



function clear(){
	echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
}