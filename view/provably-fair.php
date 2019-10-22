<?php 

	if (!isset($_SESSION)) {
		session_start();
	}
	if (isset($_POST['hash'])) {
		$hash = $_POST['hash'];
		$secret = $_POST['secret'];
		require_once('../includes/header.php');
		echo '<script>displayWinningData("'.$hash.'", "'.$secret.'");</script>';
	}
?>
<!DOCTYPE html>
<html lang="en">
	<?php require_once('../includes/header.php'); ?>
	<body>
		<?php require_once('../includes/navigation.php');?>
		<div class="col-sm-6" style="margin-left: 25%; margin-top: 10px; padding-top: 10px; color: #FFFFFF;">
			<div class="item-block">
				<h1>Provably Fair</h1>

				<h2>Items you can bet:</h2>
				<p>You can only bet Counter Strike | Global Offensive (CS:GO) items. If there is any other items in the trade the trade will be declined! The max value of skins for Jackpot is 10 items and a minimum of $2/trade, with a maximum of two trades per round.</p>

				<h2>Selecting the winner:</h2>
				<p>A ticket represents $0.01 in bet value (1 ticket = $0.01 of the skin you submitted, Example: $10.00 skin = 1000 tickets).</p>
				<h3>Our calculation:</h3>
				<ul>
					<li>1. Combine round hash and round secret.</li>
					<li>2. Calculate a hash value from the combined value using SHA-256.</li>
					<li>3. Using the calculated hash value, we convert the first 8 digits of the hash into a hexadecimal format.</li>
					<li>4. We then divide the hexadecimal value by '4294967296', which gives us the round winning percentage.</li>
				</ul>
			</div>
			<div class="item-block">
				<div id="verify">
					<h2>Round Data:</h2>
					<div class="group">
						<h3>Hash:</h3>
						<input name="hash" class="form-control" id="hash" placeholder="Round Hash" autocomplete="off" value="">
					</div>
					<div class="group">
						<h3>Secret:</h3>
						<input name="secret" class="form-control" id="secret" placeholder="Round Secret" autocomplete="off" value="">
					</div>
					<br>
					<div class="group">
						<input type="submit" value="Find Winning Data" onClick="display();">
					</div>
				</div>
			</div>
			<br>
			<div class="item-block">
				<p>Alternatively you can copy and paste the PHP code below into <a href="http://phptester.net/" target="_blank">PHPTester</a>, and replace the secret and hash values for your own. Execute the code to verify the percentage.</p>
				<pre class="pre-code">$hash = '1majGdIvgtqOizd';<br>$secret = 'lLUd6OQmi3nitja';<br>$combined_hash = $secret.$hash;<br>$calculated = hash('sha256', $combined_hash);<br>$hex_value = substr($calculated, 0, 8);<br>$decimal = hexdec($hex_value) / 4294967296;<br>$winning_percentage = round($decimal, 14);<br>echo $winning_percentage;</pre>
			</div>
		</div>
		<script>
			function display() {
				var hash = document.getElementById('hash').value;
				var secret = document.getElementById('secret').value;

				document.getElementById('hash').value = '';
				document.getElementById('secret').value = '';

				displayWinningData(hash, secret);
			}
		</script>
	</body>
</html>
