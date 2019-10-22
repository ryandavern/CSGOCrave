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
	    	echo 'Incorrect Password, Your password='.$_POST['password'].', Your message='.$_POST['message'].'.';
	    	return;
	    }
	    $message = $_POST['message'];
	   
	    if ($message === 'update-coinflip')
			joinCoinflip($_POST['user_64'], $_POST['game_id'], $_POST['items']);
	}

	function joinCoinflip($join_user_64, $game_id, $items) {
		global $dbc;

		$coinflip_empty = mysqli_query($dbc, 'SELECT coinflip_t_user_64, coinflip_ct_user_64, coinflip_game_id FROM coinflip WHERE coinflip_game_id="'.$game_id.'" LIMIT 1;');
		$row = mysqli_fetch_assoc($coinflip_empty);
		$ct_user = $row['coinflip_ct_user_64'];
		$t_user = $row['coinflip_t_user_64'];

		if ($ct_user != '' && $t_user != '') {
			echo '{"message":"Game in progress."}';
			return;
		}

		$message = addItem($game_id, $join_user_64, $items);
		if ($message == 'error') {
			echo '{"message":"Items less than $2."}';
			return;
		}

		$side_joining = mysqli_query($dbc, 'SELECT coinflip_t_user_64, coinflip_ct_user_64, coinflip_game_id, coinflip_status FROM coinflip WHERE coinflip_game_id="'.$game_id.'";');
		$side_row = mysqli_fetch_assoc($side_joining);

		if ($side_row['coinflip_status'] == 'WAITING_FOR_ITEMS') {
			echo '{"message":"coinflip_create"}';
			return;
		}

		// Is the coinflip creator CT or T?
		$query = 'UPDATE coinflip SET coinflip_ct_user_64="'.$join_user_64.'" WHERE coinflip_game_id="'.$game_id.'";';

		if ($side_row['coinflip_ct_user_64'] != '') {
			$query = 'UPDATE coinflip SET coinflip_t_user_64="'.$join_user_64.'" WHERE coinflip_game_id="'.$game_id.'";';
		}

		// Add the user to the coinflip.
		$result = mysqli_query($dbc, $query);
		endCoinflip($game_id);

		echo $message;
	}

	function getUserSide($game_id, $user_64) {
		$side_joining = mysqli_query($dbc, 'SELECT coinflip_t_user_64, coinflip_ct_user_64, coinflip_game_id FROM coinflip WHERE coinflip_game_id="'.$game_id.'";');
		$side_row = mysqli_fetch_assoc($side_joining);
		if ($side_row['coinflip_t_user'] === $user_64)
			return 'T';
		else
			return 'CT';
	}

	function endCoinflip($coinflip_game_id) {
		global $dbc;
		$debug = false;

		# Get current coinflip id.
		$result = mysqli_query($dbc, 'SELECT coinflip_id, coinflip_game_id, coinflip_secret, coinflip_hash FROM coinflip WHERE coinflip_game_id="'.$coinflip_game_id.'" LIMIT 1;');
		$row = mysqli_fetch_assoc($result);
		$coinflip_id = $row['coinflip_id'];
		$secret = $row['coinflip_secret'];
		$hash = $row['coinflip_hash'];
		# Generate winning percentage.

		$combined_hash = $secret.$hash;
		$calculated = hash('sha256', $combined_hash);
		
		$hex_value = substr($calculated, 0, 8);

		$decimal = hexdec($hex_value);
		$decimal = $decimal / 4294967296;
		$winning_percentage = round($decimal, 14);

		// Get the winner of the pot, ticket chosen and winner percentage.
		$winner_information = getEndGameInformation($coinflip_id, $coinflip_game_id, $winning_percentage);
		if ($winner_information == null)
			return;
		// Get winner steam 64 id.
		$ticket_chosen = $winner_information[0];
		$total_tickets = $winner_information[1];
		$percentage = $winner_information[2];
		$winner_steam_64 = $winner_information[3];
		$winner_chance = $winner_information[4];
		$winner_investment = $winner_information[5];
		$total_pot_prize = $winner_information[6];

		# Update coinflip with winner information.
		mysqli_query($dbc, 'UPDATE coinflip SET coinflip_winner_64="'.$winner_steam_64.'", coinflip_winning_percentage="'.$winning_percentage.'", coinflip_status="COMPLETED", coinflip_win_date=NOW(), coinflip_ticket_number="'.$ticket_chosen.'", coinflip_total_tickets="'.$total_tickets.'" WHERE coinflip_id='.$coinflip_id.';');
	
		# Update User Win Stats
		mysqli_query($dbc, 'UPDATE users SET user_total_coinflip_win_amount=user_total_coinflip_win_amount+'.$total_pot_prize.' WHERE user_64="'.$winner_steam_64.'";');
	}

	function getEndGameInformation($coinflip_id, $coinflip_game_id, $winning_percentage) {
		global $dbc;
		$max_ticket = 0;

		$ticket_array = array();

		$items_in_coinflip = 'SELECT item_coinflip_id, item_user_64, item_price FROM coinflip_item WHERE item_coinflip_id='.$coinflip_id.';';
		$item_result = mysqli_query($dbc, $items_in_coinflip);
		if (mysqli_num_rows($item_result) <= 0)
			return null;
		$item_count = 0;
		$total_pot_prize = 0;
		$max_ticket = 0;

		while ($row = mysqli_fetch_assoc($item_result)) {
			$item_count+=1;
			$item_user_64 = $row['item_user_64'];
			$item_price = $row['item_price'];
			$total_pot_prize+=$item_price;

			$price_in_cents = $item_price / 100;
			$min_ticket = $max_ticket;

			for ($i = 0; $i < $price_in_cents; $i++) {
				$max_ticket += 1;
			}
			array_push($ticket_array, array('user_64' => $item_user_64, 'min_ticket' => $min_ticket, 'max_ticket' => $max_ticket));
		}

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

		$items_in_coinflip = 'SELECT item_coinflip_id, item_user_64, item_price FROM coinflip_item WHERE item_coinflip_id='.$coinflip_id.' AND item_user_64="'.$winner_64.'";';
		$item_result = mysqli_query($dbc, $items_in_coinflip);
		$total_investment = 0;
		while ($items_in_jackpot_row = mysqli_fetch_assoc($item_result))
			$total_investment += $items_in_jackpot_row['item_price'];

		$winner_chance = ($total_investment / $total_pot_prize) * 100;
		$formatted_winner_percentage = number_format((float)$winner_chance, 2, '.', '');

		return array($ticket_chosen, $max_ticket, $percentage, $winner_64, $formatted_winner_percentage, $total_investment, $total_pot_prize);
	}

	function addItem($game_id, $user_64, $items) {
		global $dbc;

		$coinflip_query = mysqli_query($dbc, 'SELECT coinflip_id FROM coinflip WHERE coinflip_game_id="'.$game_id.'";');
		if (mysqli_num_rows($coinflip_query) <= 0)
			return 'error';
		$row = mysqli_fetch_assoc($coinflip_query);
		$coinflip_id = $row['coinflip_id'];

		# Loop through each item and get their name and price, and add them to the database and current coinflip.

		$add_items_to_coinflip_query = 'INSERT INTO coinflip_item (item_id, item_coinflip_id, item_user_64, item_class_id, item_context_id, item_asset_id, item_steam_instance, item_name, item_price) VALUES';

		$price = 0;
		$skins = 0;

		$error = '';

		foreach ($items as $item) {
			$class = $item['classid'];
			$instance = $item['instanceid'];
			$item_name = $item['market_name'];
			$assetid = $item['assetid'];
			$contextid = $item['contextid'];
			//$rarity_name = $item['rarityName'];
			//$rarity_color = $item['rarityColor'];
			$price_query = 0;

			if (strpos($item_name, 'StatTrak') !== false)
			    $price_query = mysqli_query($dbc, 'SELECT item_name, item_price FROM items WHERE item_name AND item_name LIKE "%'.$item_name.'%";');
			else
				$price_query = mysqli_query($dbc, 'SELECT item_name, item_price FROM items WHERE item_name NOT LIKE "%StatTrak%" AND item_name LIKE "%'.$item_name.'%";');

			$row = mysqli_fetch_assoc($price_query);
			$item_price = 5; // $row['item_price']
			
			$price+=$item_price;
			$skins+=1;

			if ($item_price < 2)
				return 'error';

			$add_items_to_coinflip_query.=' (NULL, "'.$coinflip_id.'", "'.$user_64.'", "'.$class.'", "'.$contextid.'", "'.$assetid.'", "'.$instance.'", "'.$item_name.'", "'.$item_price.'"), ';
		}

		$add_items_to_coinflip_query = substr($add_items_to_coinflip_query, 0, -2);
		# Execute the add_items_to_coinflip query.

		mysqli_query($dbc, $add_items_to_coinflip_query);

		$update_user_stats_query = mysqli_query($dbc, 'UPDATE users SET user_total_deposit_amount=user_total_deposit_amount+'.$price.', user_skins_deposited=user_skins_deposited+'.$skins.' WHERE user_64='.$user_64.';');
		include '../steam/settings.php';
		@$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$user_64);
		$content = json_decode($url, true);
		$name = $content['response']['players'][0]['personaname'];
		$image = $content['response']['players'][0]['avatarfull'];

		$str = '{ "name": "'.$name.'", "image": "'.$image.'", "skins": "'.$skins.'", "price": "'.$price.'" }';

		return $str;
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

?>