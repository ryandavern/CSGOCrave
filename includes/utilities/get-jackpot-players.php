<?php
	
	include('../database.php');
	include('../steam/settings.php');
	global $connection;

	$query = 'SELECT jackpot_id FROM jackpot ORDER BY jackpot_id DESC LIMIT 1,1;';
	$result = mysqli_query($connection, $query);
	$row = mysqli_fetch_assoc($result);

	$jackpot_id = $row['jackpot_id'];

	$query = 'SELECT item_jackpot_id, item_user_64 FROM jackpot_item WHERE item_jackpot_id='.$jackpot_id.' GROUP BY item_user_64;';
	$result = mysqli_query($connection, $query);

	$people = array();

	while ($row = mysqli_fetch_assoc($result)) {
		@$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$row['item_user_64']);
		$content = json_decode($url, true);
		array_push($people, $content['response']['players'][0]['avatarfull']);
	}
	echo json_encode($people);
?>