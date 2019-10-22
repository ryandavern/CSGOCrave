<?php 

	if (!isset($_SESSION)) {
		session_start();		
	}
?>
<!DOCTYPE html>
<html lang="en">
	<?php require_once('../includes/header.php'); ?>
	<body>
		<?php require_once('../includes/navigation.php');?>
		<div class="col-sm-6" style="margin-left: 25%; margin-top: 10px; padding-top: 10px; color: #FFFFFF;">
			<div class="item-block">
				<h1>CSGOCrave.com</h1>
			</div>
			
			<div class="item-block">
				<h2>Do we use a Provably Fair system?</h2>
				<p><strong>Yes.</strong> Find out more about Provably Fair on CSGOCrave by clicking <a href="provably-fair.php" target="_blank">here</a>.</p>
			</div>

			<div class="item-block">
				<h2>Do we sponsor content creators?</h2>
				<p><strong>Yes.</strong> We will create a system in the future for content creators to apply for a sponsorship. As of right now we are hand-picking potential sponsors.</p>
			</div>
			<div class="item-block">
				<h2>Do we have any other gamemodes?</h2>
				<p><strong>Not Yet.</strong> We are currently in development stages of a coinflip system, which will be released to the public within the next couple of months.</p>
			</div>
			<div class="item-block">
				<h2>How much do we tax?</h2>
				<p>Up to 10%.</p>
			</div>
		</div>
	</body>
</html>