<?php
	
	include('../database.php');
	
	if (!isset($_POST['game_id'])) {
		exit("Incorrect Game ID");
	}

	echo getInvestmentIntoCoinflip($_POST['game_id']);
?>