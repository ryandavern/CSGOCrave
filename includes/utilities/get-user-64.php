<?php
		
	require('../database.php');
	include '../steam/user.php';

	if (!isset($_SESSION)) {
		session_start();
	}

	$steam64Id = $_SESSION['steamid'];

	echo json_encode(array("id"=>$steam64Id));
?>