<?php 
	if (!isset($_SESSION)) {
		session_start();
	}
	require_once('../includes/database.php');

	$cookie_update_id = '1';
	$display_updates = false;

	if (!isset($_COOKIE['update-displayed'.$cookie_update_id])) {
	    setcookie('update-displayed'.$cookie_update_id, $cookie_update_id, time() + (10 * 365 * 24 * 60 * 60), "/");
		$display_updates = true;
	}
	
?>
<!DOCTYPE html>
<html>
	<?php require_once('../includes/header.php'); ?>
	<script>runCoinflipFunctions();</script>
	<body>
		<?php require_once('../includes/navigation.php'); require_once('../includes/sidebar.php'); ?>
		
		<div class="col-sm-6">
			
			<div id="pot-info">
				<table>
					<tr>
						<td><div class="info-item"><div class="info-text">Minimum Deposit:<br><div class="info">$10.00</div></div></div></td>
						
						<td><div class="info-item"><div class="info-text">Total CoinFlips Played<br><div class="info" id="total-games-played">&hellip;</div></div></div></td>
					</tr>
				</table>
			</div>	
			<?php
				if (isset($_SESSION['steamid'])) {
					if($_SESSION['trade_url'] === 'null')
						echo '<a class="deposit-button" onclick="showEnterTrade(false)"><strong>Create</strong></a><br>';
					else
						echo '<a class="deposit-button" onclick="showInventory()"><strong>Create</strong></a><br>';
				}
			?>
			<table id="coinflip">
				<thead>
					<tr>
						<th>Players</th>
						<th>Items</th>
						<th>Total</th>
						<th>Status</th>
						<th>&nbsp;</th>
		            </tr>
				</thead>
				<tbody class="coinflip-container"></tbody>
			</table>
		</div>
		<script>
			function showInventory(game_id) {
				if (steam_64_id == '') {
					console.log("Not logged in");
					return;
				}
				socket.emit('load inventory', {game_id:game_id, steam_64:steam_64_id});
			}
			$('#chat-message').scrollTop($('#chat-message')[0].scrollHeight);
			//createNewCoinflip("732847293299901", "Ryan", "https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/32/3270947c86d40a6f26ad8d72305ff64150cec490_full.jpg", "12345", "ct", "523.25", ['a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a']);
			//createNewCoinflip("732847293299901", "Ryan", "https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/32/3270947c86d40a6f26ad8d72305ff64150cec490_full.jpg", "123", "t", "256.11", ['a', 'a', 'a']);
			//createNewCoinflip("732847293299901", "Ryan", "https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/32/3270947c86d40a6f26ad8d72305ff64150cec490_full.jpg", "1234", "ct", "1000", ['a', 'a', 'a']);
		</script>
		<?php
			getCoinflips();
		?>
	</body>
</html>