<?php
	
	include('../database.php');
	global $connection;

	$result = mysqli_query($connection, 'SELECT jackpot_id, jackpot_winner_64, jackpot_winner_investment, jackpot_total_investment, jackpot_ticket_number, jackpot_secret, jackpot_winning_percentage FROM jackpot ORDER BY jackpot_id DESC LIMIT 1,1;');
	if (mysqli_num_rows($result) == 0)
		return;

	include($_SERVER['DOCUMENT_ROOT'].'CSGOCrave/includes/steam/settings.php');
		
	$row = mysqli_fetch_assoc($result);
	$jackpot_id = $row['jackpot_id'];
	
	$winner_64 = $row['jackpot_winner_64'];
	if ($winner_64 != '') {
		$total_pot = $row['jackpot_total_investment'];
		$chance = $row['jackpot_winner_investment'] / $total_pot * 100;
		$secret = $row['jackpot_secret'];
		$jackpot_winning_percentage = $row['jackpot_winning_percentage'];
				
		@$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$winner_64);
		$content = json_decode($url, true);

		$next_round_hash = mysqli_query($connection, 'SELECT jackpot_id, jackpot_hash FROM jackpot ORDER BY jackpot_id DESC LIMIT 1;');
		$next_row = mysqli_fetch_assoc($next_round_hash);

		$return = array("user_64" => $winner_64, "name" => $content['response']['players'][0]['personaname'], "image" => $content['response']['players'][0]['avatarfull'], "secret" => $secret, "next_round_hash" => $next_row['jackpot_hash'], "round" => $jackpot_id, "winning_percentage" => $jackpot_winning_percentage, "winner_percentage" => $chance);
		echo json_encode($return);
	}
?>