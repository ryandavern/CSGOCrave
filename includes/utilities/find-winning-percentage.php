<?php
	if (!isset($_POST['hash'])) {
		echo '0';
		return;
	}
	$hash = $_POST['hash'];
	$secret = $_POST['secret'];

	$combined_hash = $secret.$hash;
	$calculated = hash('sha256', $combined_hash);
		
	$hex_value = substr($calculated, 0, 8);

	$decimal = hexdec($hex_value);
	$decimal = $decimal / 4294967296;
	$winning_percentage = round($decimal, 14);

	echo $winning_percentage;
?>