<?php
	if (!isset($_POST['items']))
		return;

	require('../database.php');

	$items = json_decode($_POST['items'], true);

	//$item_list = array();

	$string = '';
	$item_array = array();

	foreach ($items as $key => $value) {
		$name = $value['name'];
		$assetid = $value['assetid'];
		$classid = $value['classid'];
		$instanceid = $value['instanceid'];
		$price = getPrice($name);
		if ($price >= 2)
			array_push($item_array, array($name, $assetid, $classid, $instanceid, "price"=>$price));
	}
	// This will sort the array by highest priced items to lowest priced items.
	usort($item_array, function ($item1, $item2) {
	    if ($item1['price'] == $item2['price'])
	    	return 0;
	    return $item2['price'] < $item1['price'] ? -1 : 1;
	});
	echo json_encode($item_array);
?>