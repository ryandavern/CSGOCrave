<?php 
	if (!isset($_SESSION)) {
		session_start();
	}
	
	require_once('../includes/database.php');
	require_once('../includes/steam/user.php');
	require_once('../includes/header.php');

	if (isset($_POST['trade_url'])) {
		$trade_url = addslashes($_POST['trade_url']);
		$steam64Id = $steamprofile['steamid'];
		if ($_SESSION['trade_url'] != $trade_url)
			setTradeToken($steam64Id, $trade_url);
	}
?>
<!DOCTYPE html>
<html>
	<body>
		<?php require_once('../includes/navigation.php');?>
		<div class="col-sm-6 container">
			<form action="" method="post">
				<h1 class="hidden">#</h1>
				<?php
					echo '<img src="'.$_SESSION['steam_avatarfull'].'" alt="Profile Image" />';
					echo '<h2>'.$_SESSION['steam_personaname'].'\'s Settings</h2>';
				?>
				<div class="profile-item-wrapper">
					<h3 class="hidden">#</h3>
					<h4><strong>Trade URL:</strong></h4>
					<?php
						$trade_url = $_SESSION['trade_url'];
						if (strlen($trade_url) < 20)
							$trade_url = '';

						echo '<input type="text" minlength="51" class="text_field" placeholder="Enter Trade URL" value="'.$trade_url.'" name="trade_url"><br>';
						echo '<a href="https://steamcommunity.com/profiles/'.$steamprofile['steamid'].'/tradeoffers/privacy#trade_offer_access_url" target="_blank">Find your Trade URL here.</a>';
					?>
					<br><br>
					<input type="submit" value="Save Trade URL">
				</div>
			</form>
			<div class="profile-item-wrapper">
				<h3 class="hidden">#</h3>
				<h4><strong>Your Stats:</strong></h4>
				<?php

					$stats = getUserStats();

					$deposit_amount = round($stats[0], 2);
					$win_amount = round($stats[1], 2);

					$profit = $win_amount - $deposit_amount;

					echo '<p class="stats"><strong>Total Bet</strong> <br>$'.$deposit_amount.'</p>';
					echo '<p class="stats"><strong>Total Won</strong> <br>$'.$win_amount.'</p>';
					if ($profit >= 0)
						echo '<p class="stats positive"><strong>Profit</strong> <br>$'.$profit.'</p>';
					else
						echo '<p class="stats negative"><strong>Profit</strong> <br>$'.$profit.'</p>';
				?>
			</div>
		</div>
	</body>
</html>