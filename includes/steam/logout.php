<?php
	include("settings.php");
	# header("Location: ../".$steamauth['logoutpage']);
	session_start();
	unset($_SESSION['steamid']);
	unset($_SESSION['steam_uptodate']);
	session_destroy();

	$location = $steamauth['logoutpage'];
	header('Location: '.$location);
?>