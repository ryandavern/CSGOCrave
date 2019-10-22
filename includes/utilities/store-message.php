<?php
	
	$pass = false;

	if (isset($_POST['password']) && $_POST['password'] == '') {
		$pass = true;
	}

	if (!isset($_POST['user_64']))
		$pass = false;

	if (!$pass) {
	    header('WWW-Authenticate: Basic realm="Restricted Page"');
	     header('HTTP/1.0 401 Unauthorized');
	    header('Location: http://localhost/CSGOCrave');
	    return;
	}

	$user_64 = $_POST['user_64'];
	$message = $_POST['message'];

	if ($user_64 == '' || $message == '')
		return;

	include '../steam/settings.php';
	include '../database.php';

	addMessage($user_64, $message);

	@$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$user_64);
	$content = json_decode($url, true);
	$name = $content['response']['players'][0]['personaname'];
	$image = $content['response']['players'][0]['avatarfull'];

	$str = '{ "profile_name": "'.$name.'", "avatar": "'.$image.'" }';

	echo $str;

?>