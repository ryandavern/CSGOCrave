<?php 
	if (!isset($_SESSION)) {
		session_start();
	}
	require_once('../includes/database.php');

	if (!isset($_GET['id'])) {
		header('Location: coinflip.php');
		exit;
	}
	$id = $_GET['id'];
	$game_data = getCoinflipFromURL($id);
	$ct_user_64 = $game_data[0];
	$ct_name = $game_data[1];
	$ct_image = $game_data[2];
	$t_user_64 = $game_data[3];
	$t_name = $game_data[4];
	$t_image = $game_data[5];
	$hash = $game_data[6];
	$secret = $game_data[7];
	$winner_64 = $game_data[8];
	$winning_percentage = $game_data[9];
	$ct_items = $game_data[10];
	$t_items = $game_data[11];

	$game_over = false;
	$win_side = '';
	if ($secret != '') {
		$game_over = true;
		if ($winner_64 == $ct_user_64)
			$win_side = 'CT';
		else
			$win_side = 'T';
	}
	$ct_investment = 0;
	$t_investment = 0;
	if (sizeof($ct_items) > 0) {
		foreach ((array) $ct_items as $item) {
			$item_price = $item[2];
			$ct_investment+=$item_price;
		}
	}
	if (sizeof($t_items) > 0) {
		foreach ((array) $t_items as $item) {
			$item_price = $item[2];
			$t_investment+=$item_price;
		}
	}
?>
<!DOCTYPE html>
<html>
	<?php require_once('../includes/header.php'); ?>
	<body>
		<?php
			require_once('../includes/navigation.php');
			require_once('../includes/sidebar.php');
		?>
		
		<div class="col-sm-8" id="coinflip-game-wrapper">
			<div class="col-sm-2 break-point" id="t_player">
				<div class="coinflip-data">
					<img src="../images/t_coin.png" alt="Head Coin" class="coinitem"/>
					<br><br>
					<?php
						if ($t_user_64 == '') {
							echo '<p class="coinitem"><strong>Waiting...</strong></p>';
						} else {
							echo '<img src="'.$t_image.'" width="35" height="35" alt="Player Image" class="image player-image coinitem"/>';
							echo '<p class="coinitem"><strong>'.$t_name.'</strong></p>';
							echo '<p class="coinflip-individual-investment">$'.$t_investment.'</p>';
						}
					?>
				</div>
				
				<?php
					$item_count = count($t_items);
					if ($item_count > 5)
						echo '<div class="items scrollbar">';
					else
						echo '<div class="items">';
					for ($i = 0; $i < $item_count; $i++) {
						$item_name = $t_items[$i][0];
						$classid = $t_items[$i][1];
						$price = $t_items[$i][2];
						echo '<img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/'.$classid.'/70fx50f" alt="Item Image" class="tooltip-borderless coinflip-item-image item" title="'.$item_name.'<br>Price: $'.$price.'" />';
					}
					echo '</div>';
				?>
			</div>
			<div class="col-sm-2 break-point">
				<br>
				<?php 
					if ($game_over)
						echo '<div class="flip-container"><div class="flip-animation"><div class="front"><img src="../images/ct_large_coin.png" alt="Coinflip Image" width="100" height="100" /></div><div class="back"><img src="../images/t_large_coin.png" alt="Coinflip Image" width="100" height="100" /></div></div></div><br><br><br><br>';
					else
						echo '<p><strong>Waiting...</strong></p>';
				?>
				
				<div style="text-align: center;">
					<p style="text-align: left; display: inline-block;">
						<?php echo '<span id="hash"><strong>Hash:</strong> <span style="color:#f39c12;">'.$hash.'</span></span><br>'; ?>
						<span id="secret" style="display: none;"><strong>Secret:</strong> <span style="color:#f39c12;" id="secret_value"></span></span><br>
						<span id="ticket_percentage" style="display: none;"><strong>Ticket (%):</strong> <span style="color:#f39c12;" id="ticket_percentage_value"></span></span>
					</p>
				</div>
			</div>
			<div class="col-sm-2 break-point" id="ct_player">
				<div class="coinflip-data">
					<img src="../images/ct_coin.png" alt="Tail Coin" class="coinitem" />
					<br><br>
					<?php
						if ($ct_user_64 == '') {
							echo '<p class="coinitem"><strong>Waiting...</strong></p>';
						} else {
							echo '<img src="'.$ct_image.'" width="35" height="35" alt="Player Image" class="image player-image coinitem"/>';
							echo '<p class="coinitem"><strong>'.$ct_name.'</strong></p>';
							echo '<p class="coinflip-individual-investment">$'.$ct_investment.'</p>';
						}
					?>
				</div>
				<?php
					$item_count = count($ct_items);
					if ($item_count > 5)
						echo '<div class="items scrollbar">';
					else
						echo '<div class="items">';
					for ($i = 0; $i < $item_count; $i++) {
						$item_name = $ct_items[$i][0];
						$classid = $ct_items[$i][1];
						$price = $ct_items[$i][2];
						echo '<img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/'.$classid.'/70fx50f" alt="Item Image" class="tooltip-borderless coinflip-item-image item" title="'.$item_name.'<br>Price: $'.$price.'" />';
					}
					echo '</div>';
				?>
			</div>
		</div>
		<?php 
			$total_value = ($t_investment + $ct_investment);
			if ($game_over)
				echo '<script>spin("'.$win_side.'", "'.$secret.'", "'.$winning_percentage.'", "'.$winner_64.'", "'.$total_value.'");</script>';
		?>
	</body>
</html>