<?php
	
	include('../database.php');
	
	if (!isset($_POST['item_name'])) {
		exit("Incorrect Item Name");
	}

	echo getPrice($_POST['item_name']);
?>