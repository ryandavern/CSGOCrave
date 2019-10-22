<?php
	$debug = false;

	include '../database.php';
	$dbc = $connection;

	if (!$debug) {
		$pass = false;
	    if (isset($_POST['password']) && $_POST['password'] == '') {
	    	$pass = true;
	    }

	    if (!isset($_POST['message']))
	    	$pass = false;

	    if (!$pass) {
	    	header('WWW-Authenticate: Basic realm="Restricted Page"');
	     	header('HTTP/1.0 401 Unauthorized');
	    	header('Location: http://localhost/CSGOCrave');
	    	return;
	    }

	    $message = $_POST['message'];
	   
	    if ($message === 'end-game') {
	    	endGame();
	    	echo 'Game Ended';
	    } else if ($message === 'deposit-items') {
			addItem($_POST['user_64'], $_POST['items'], $_POST['trade_id']);
	    }
	}
	if ($debug)
    	endGame();

	function endGame() {
		global $dbc;
		$debug = false;

		# Get current jackpot id.
		$result = mysqli_query($dbc, 'SELECT jackpot_id, jackpot_secret, jackpot_hash FROM jackpot ORDER BY jackpot_id DESC LIMIT 1;');
		$row = mysqli_fetch_assoc($result);
		$jackpot_id = $row['jackpot_id'];
		$secret = $row['jackpot_secret'];
		$hash = $row['jackpot_hash'];

		createGame();

		//$update_status = mysqli_query($dbc, 'UPDATE jackpot SET jackpot_status="ACCEPTING_LAST" WHERE jackpot_id="'.$jackpot_id.'";');		

		# Generate winning percentage.

		$combined_hash = $secret.$hash;
		$calculated = hash('sha256', $combined_hash);
		
		$hex_value = substr($calculated, 0, 8);

		$decimal = hexdec($hex_value);
		$decimal = $decimal / 4294967296;
		$winning_percentage = round($decimal, 14);

		// Get the winner of the pot, ticket chosen and winner percentage.
		$winner_information = getEndGameInformation($jackpot_id, $winning_percentage);
		if ($winner_information == null) {
			return;
		}
		// Get winner steam 64 id.
		$ticket_chosen = $winner_information[0];
		$total_tickets = $winner_information[1];
		$percentage = $winner_information[2];
		$winner_steam_64 = $winner_information[3];
		$winner_chance = $winner_information[4];
		$winner_investment = $winner_information[5];
		$total_pot_prize = $winner_information[6];

		if (!$debug) {
			echo $ticket_chosen.'<br>';
			echo $percentage.'<br>';
			echo $winner_steam_64.'<br>';
			echo $winner_chance.'<br>';
			echo $winner_investment.'<br>';
			echo $total_pot_prize.'<br>';
		}

		$trade_information = getTradeInformation($jackpot_id, $winner_steam_64);

		# Get trade token for winner
		$result = mysqli_query($dbc, 'SELECT user_id, user_64, user_trade_token FROM users WHERE user_64='.$winner_steam_64.' LIMIT 1;');
		$row = mysqli_fetch_assoc($result);
		$trade_token = $row['user_trade_token'];

		// $random_ticket, $formatted_percentage, $ticket_array[$random_ticket]['64'] - getEndGameDetails
		// $taxation_items, $winning_items, $keepPercentage - getTradeInformation

		# Update jackpot with winner information.
		mysqli_query($dbc, 'UPDATE jackpot SET jackpot_winner_64="'.$winner_steam_64.'", jackpot_winner_investment="'.$winner_investment.'", jackpot_total_investment="'.$total_pot_prize.'", jackpot_winning_percentage="'.$winning_percentage.'", jackpot_win_date=NOW(), jackpot_ticket_number="'.$ticket_chosen.'", jackpot_total_tickets="'.$total_tickets.'" WHERE jackpot_id='.$jackpot_id.';');
	
		# Update User Win Stats
		mysqli_query($dbc, 'UPDATE users SET user_total_jackpot_win_amount=user_total_jackpot_win_amount+'.$total_pot_prize.' WHERE user_64="'.$winner_steam_64.'";');
	}

	function addItem($user_64, $items, $trade_id) {
		global $dbc;
		$trade_steam_64 = $user_64;
		$message = $trade_id;

		$result = mysqli_query($dbc, 'SELECT jackpot_id, jackpot_status FROM jackpot ORDER BY jackpot_id DESC LIMIT 1;');
		$row = mysqli_fetch_assoc($result);
		$jackpot_id = $row['jackpot_id'];
		$jackpot_status = $row['jackpot_status'];
		if ($jackpot_status == 'COMPLETE' || $jackpot_status == 'ROLLING') {
			$jackpot_id++;
		}

		# Loop through each item and get their name and price, and add them to the database and current jackpot.

		$add_items_to_jackpot_query = 'INSERT INTO jackpot_item (item_id, item_jackpot_id, item_user_64, item_class_id, item_steam_instance, item_name, item_price, item_rarity, item_collection) VALUES';

		$price = 0;
		$skins = 0;

		$random_id = uniqid();
		$error = '';

		foreach ($items as $item) {
			$class = $item['classId'];
			$instance = $item['instanceId'];
			$item_name = $item['marketName'];
			$rarity_name = $item['rarityName'];
			$rarity_color = $item['rarityColor'];
			$price_query = 0;

			if (strpos($item_name, 'StatTrak') !== false)
			    $price_query = mysqli_query($dbc, 'SELECT item_name, item_price FROM items WHERE item_name AND item_name LIKE "%'.$item_name.'%";');
			else
				$price_query = mysqli_query($dbc, 'SELECT item_name, item_price FROM items WHERE item_name NOT LIKE "%StatTrak%" AND item_name LIKE "%'.$item_name.'%";');

			$row = mysqli_fetch_assoc($price_query);
			$item_price = $row['item_price'];
			
			$price+=$item_price;
			$skins+=1;

			if ($item_price < 2) {
				$error = 'We do not accept items less which are than $2 USD.';
				echo '{"message":"'.$error.'"}';
				return;
			}

			$add_items_to_jackpot_query.=' (NULL, "'.$jackpot_id.'", "'.$trade_steam_64.'", "'.$class.'", "'.$instance.'", "'.$item_name.'", "'.$item_price.'", "'.$rarity_name.'", "'.$random_id.'"), ';
		}

		$add_items_to_jackpot_query = substr($add_items_to_jackpot_query, 0, -2);
		# Execute the add_items_to_jackpot query.

		mysqli_query($dbc, $add_items_to_jackpot_query);

		$update_user_stats_query = mysqli_query($dbc, 'UPDATE users SET user_total_jackpot_deposit_amount=user_total_jackpot_deposit_amount+'.$price.', user_skins_deposited=user_skins_deposited+'.$skins.' WHERE user_64='.$trade_steam_64.';');
		include '../steam/settings.php';
		@$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$trade_steam_64);
		$content = json_decode($url, true);
		$name = $content['response']['players'][0]['personaname'];
		$image = $content['response']['players'][0]['avatarfull'];

		//echo '<script>addDeposit("'.$name.'", "'.$image.'", '.$skins.', '.$price.');</script>';

		$str = '{ "name": "'.$name.'", "image": "'.$image.'", "skins": "'.$skins.'", "price": "'.$price.'" }';

		echo $str;
	}

	function generateRoundSecret($length = 15) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	    $charactersLength = strlen($characters);
	    $secret = '';
	    for ($i = 0; $i < $length; $i++) {
	        $secret .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $secret;
	}

	function createGame() {
		global $dbc;

		$last_game_result = mysqli_query($dbc, 'SELECT jackpot_id, jackpot_status FROM jackpot ORDER BY jackpot_id DESC LIMIT 1;');
		$row = mysqli_fetch_assoc($result);
		$jackpot_status = $row['jackpot_status'];
		if ($jackpot_status == 'WAITING' || $jackpot_status == 'ACCEPTING_LAST') {
			echo 'A game already is in progress!';
			return;
		}

		// Hide from user until round is over.
		$round_secret = generateRoundSecret();
		// Show the user.
		$round_hash = generateRoundSecret();

		# Start a new pot.
		mysqli_query($dbc, "INSERT INTO jackpot (jackpot_id, jackpot_creation_date, jackpot_secret, jackpot_hash, jackpot_status) VALUES (NULL, 'NOW()', '".$round_secret."', '".$round_hash."', 'WAITING');");
	}

	function getEndGameInformation($jackpot_id, $winning_percentage) {
		global $dbc;
		$max_ticket = 0;

		$ticket_array = array();

		$items_in_jackpot = 'SELECT jackpot_id, item_jackpot_id, item_user_64, item_price FROM jackpot, jackpot_item WHERE jackpot_id='.$jackpot_id.' AND item_jackpot_id='.$jackpot_id.';';
		$jackpot_item_result = mysqli_query($dbc, $items_in_jackpot);
		if (mysqli_num_rows($jackpot_item_result) <= 0) {
			return null;
		}
		$item_count = 0;
		$total_pot_prize = 0;
		$max_ticket = 0;

		while ($items_in_jackpot_row = mysqli_fetch_assoc($jackpot_item_result)) {
			$item_count+=1;
			$item_user_64 = $items_in_jackpot_row['item_user_64'];
			$item_price = $items_in_jackpot_row['item_price'];
			$total_pot_prize+=$item_price;

			$price_in_cents = $item_price / 100;
			$min_ticket = $max_ticket;

			for ($i = 0; $i < $price_in_cents; $i++) {
				$max_ticket += 1;
			}
			array_push($ticket_array, array('user_64' => $item_user_64, 'min_ticket' => $min_ticket, 'max_ticket' => $max_ticket));
			//echo $price_in_cents.' : '.$min_ticket.' : '.$max_ticket.'<br>';
		}

		//print_r($ticket_array);

		$ticket_chosen = $max_ticket * $winning_percentage;
		$winner_64 = '';

		foreach ($ticket_array as $ticket_user) {
			$user_64 = $ticket_user['user_64'];
			$min_ticket = $ticket_user['min_ticket'];
			$max_ticket = $ticket_user['max_ticket'];

			if ($ticket_chosen >= $min_ticket && $ticket_chosen <= $max_ticket)
				$winner_64 = $user_64;
		}
		
		$percentage = ($ticket_chosen / $max_ticket) * 100;

		$items_in_jackpot = 'SELECT jackpot_id, item_jackpot_id, item_user_64, item_price FROM jackpot, jackpot_item WHERE jackpot_id='.$jackpot_id.' AND item_jackpot_id='.$jackpot_id.' AND item_user_64="'.$winner_64.'";';
		$jackpot_item_result = mysqli_query($dbc, $items_in_jackpot);
		$total_investment = 0;
		while ($items_in_jackpot_row = mysqli_fetch_assoc($jackpot_item_result))
			$total_investment += $items_in_jackpot_row['item_price'];

		$winner_chance = ($total_investment / $total_pot_prize) * 100;
		$formatted_winner_percentage = number_format((float)$winner_chance, 2, '.', '');

		if ($debug) {
			echo $max_ticket.'<br>';
			echo $ticket_chosen.'<br>';
			echo $percentage.'<br>';
			echo $winner_64.'<br>';
			echo $formatted_winner_percentage.'<br>';
			echo $total_investment.'<br>';
			echo $total_pot_prize.'<br>';
		}

		return array($ticket_chosen, $max_ticket, $percentage, $winner_64, $formatted_winner_percentage, $total_investment, $total_pot_prize);
	}

	function getTradeInformation($jackpot_id, $winner_steam_64) {
		global $dbc;
		$debug = false;

		$items_in_jackpot = 'SELECT jackpot_id, item_jackpot_id, item_user_64, item_price, item_class_id, item_steam_instance, item_name, item_collection FROM jackpot, jackpot_item WHERE jackpot_id='.$jackpot_id.' AND item_jackpot_id='.$jackpot_id.';';
		$result = mysqli_query($dbc, $items_in_jackpot);

		$total_pot_prize = 0;
		while ($row = mysqli_fetch_assoc($result))
			$total_pot_prize += $row['item_price'];

		if ($debug)
			echo 'TOTAL POT PRIZE: '.$total_pot_prize.'<br><br>';

		# Get all items in jackpot, take a cut of the jackpot, send winnings to user.
				
		$keepPercentage = 0;
		$taxation_items = array();
		$winning_items = array();

		$items_in_jackpot = 'SELECT jackpot_id, item_jackpot_id, item_user_64, item_price, item_class_id, item_steam_instance, item_name, item_collection FROM jackpot, jackpot_item WHERE jackpot_id='.$jackpot_id.' AND item_jackpot_id='.$jackpot_id.';';
		$result = mysqli_query($dbc, $items_in_jackpot);

		while ($row = mysqli_fetch_assoc($result)) {
			$itemPrice = intval($row['item_price']);
			$itemPercentage = ($itemPrice / $total_pot_prize) * 100;
			if ($debug)
				echo $itemPercentage.'<br>';

			if ($keepPercentage + $itemPercentage < 10) {
				array_push($taxation_items, $row);
				$keepPercentage += $itemPercentage;
			} else {
				array_push($winning_items, $row);
			}
		}

		if ($debug) {
			echo '<br><br>Keep Percentage: '.$keepPercentage.'<br><br>';
			echo '<br><br>TAXED ITEMS<br><br>'.sizeof($taxation_items);
			print_r($taxation_items);
			echo '<br><br>WON ITEMS<br><br>'.sizeof($winning_items);
			print_r($winning_items);
		}

		return array($taxation_items, $winning_items, $keepPercentage);
	}
?>