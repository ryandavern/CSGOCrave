<ul id="navigation">
	<li class="navigation-list"><a href="index.php"><strong>Jackpot</strong></a></li>
	<!-- <li class="navigation-list"><a href="head-hunter.php"><strong>Head Hunter</strong></a></li> -->
	<li class="navigation-list"><a href="coinflip.php"><strong>Coinflip</strong></a></li>
	<li class="navigation-list"><a href="contact.php"><strong>Support</strong></a></li>
	<?php
		if (!isset($_SESSION['steamid'])) {
			echo '<li class="navigation-right"><a href="https://steamcommunity.com/openid/login?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.return_to=http://localhost/CSGOCrave/%2Fincludes%2Futilities%2Fsteam-login-handle.php&openid.realm=http://localhost/CSGOCrave/&openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select"><strong>Login</strong></a></li>';
		} else {
			echo '<li class="navigation-right"><a href="../includes/steam/logout.php"><strong>Logout</strong></a></li>';
			echo '<li class="navigation-right"><a href="profile.php"><strong>My Profile</strong></a></li>';
		}
	?>

</ul>