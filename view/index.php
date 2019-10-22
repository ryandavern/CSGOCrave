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
	<script>runJackpotFunctions();</script>
	<body>
		<?php
			require_once('../includes/navigation.php');
			require_once('../includes/sidebar.php');
		?>
		
		<div class="col-sm-6">
			<!-- <img src="images/crave-banner.png"></img> -->
			<!-- Display Pot Header Information -->
			<div id="pot-info">
				<table>
					<tr>
						<td><div class="info-item"><div class="info-text">Minimum Deposit:<br><div class="info">$2.00</div></div></div></td>
						
						<td><div class="info-item"><div class="info-text">Total Jackpots Played<br><div class="info" id="total-games-played">&hellip;</div></div></div></td>
					</tr>
				</table>
			</div>
			<!-- Display Current Pot Information -->
			<div class="info-text" id="jackpot-information">
				<!-- <canvas id="jackpot-circle" width="250" height="250"></canvas> -->
				
				<div class="bar" data-stroke-trail-width="8" data-stroke-trail="#262626" data-stroke-width="8" data-stroke="#337AB7" style="width:100%;height:300px" data-preset="circle">
					<div class="label">
						<div id="pot-items">0/50</div>
						<div id="time-left">2:00</div>
					</div>
				</div>
				<div class="pot-price">$0.00</div>
				<?php
					if (isset($_SESSION['steamid'])) {
						if($_SESSION['trade_url'] === 'null')
							echo '<a class="deposit-button" onclick="showEnterTrade(false)"><strong>Deposit</strong></a><br>';
						else
							echo '<a class="deposit-button" onclick="sendDeposit()"><strong>Deposit</strong></a><br>';
					}
				?>
				<p id="join-info">Items: 0/20 ($0.00) - Chance of winning: - 0%</p>
				<script>
					var progress_bar = new ldBar(".bar");
				</script>
			</div>
			<div id="roulette">
				<img src="../images/winner_selector.png" alt="Winner Image" class="winner-select"/>
				<div class="owl-carousel"></div>
			</div>
			<!-- Display Rounds -->
			<div id="round-bet">
				
				<div class="round"></div>
			</div>
		</div>
		<div class="col-sm-2">
			<div id="item-collection">

			</div>
		</div>
		<?php 
			getLastThreeGames();
			getCurrentJackpotItems();
			if (isset($_SESSION['steamid']))
				getJoinInfo($_SESSION['steamid']);

			$total_games = getTotalGamesPlayed();
			echo '<script>$("#total-games-played").text('.$total_games.');</script>';
			if ($display_updates)
				echo '<script>displayUpdates();</script>';
		?>
		<script>
			var percentage = (cached_current_round_skins / 50) * 100;
			progress_bar.set(percentage);

			function sendDeposit() {
				$('<a>').attr('href', 'https://steamcommunity.com/tradeoffer/new/?partner=471650288&token=Fedx3-bM').attr('target', '_blank')[0].click();
			}
			$('#chat-message').scrollTop($('#chat-message')[0].scrollHeight);
			
		</script>
	</body>
</html>