<div class="col-sm-3">
	<div class="info-text">
		<div id="online-users"></div>
		<div id="chatbox">
			<div id="chat-message">
				<ul class="message">
							
				</ul>
			</div>
		</div>
		<input id="chat-input" placeholder="Type a message.">
		<br/>
		<button class="rules" onClick="displayChatRules()">Chat Rules</button>
		<?php 
			if (isModerator())
				echo '<button class="rules" onClick="displayChatSettings()">Admin</button>';
		?>
	</div>
			
	<div class="info-text">
		<!-- Display Twitter Follow Button for CSGOCrave. -->
		<a href="https://twitter.com/Crave_CSGO" class="twitter-follow-button" data-show-count="false" data-size="large" target="_blank"></a>
		<script>
			// Sourced via Twitter.
			!function(d, s, id) {
				var javascript, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location)?'http':'https';
				if (!d.getElementById(id)) {
					javascript = d.createElement(s);
					javascript.id = id;
					javascript.src = p + '://platform.twitter.com/widgets.js';
					fjs.parentNode.insertBefore(javascript, fjs);
				}
			} (document, 'script', 'twitter-wjs');
		</script>
		<ul id="sidebar-contact">
			<li><a href="index.php" class="link">Jackpot</a></li>
			<li><a href="coinflip.php" class="link">Coinflip</a></li>
			<!-- <li><a href="coinflip.php" class="link">Coin Flip</a></li> -->
			<li><a href="contact.php" class="link">Contact</a></li>
			<li><a href="terms-of-service.php" class="link">Terms of Service</a></li>
			<li><a href="about.php" class="link">About Us</a></li>
			<li><a href="provably-fair.php" class="link">Provably Fair</a></li>
		</ul>
	</div>
	<?php
		$url = '';
		if ($url != '')
			echo '<div class="info-text"><h2>Giveaway</h2><a href="'.$url.'">Enter Giveaway</a></div>';
	?>
</div>