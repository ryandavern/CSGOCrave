<!DOCTYPE html>
<html>
<head>
	<title>Support</title>

	<!-- jQuery -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	
	<!-- Bootstrap -->
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>

	<!-- SweetAlert -->
	<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.js"></script>

	<!-- Favicon -->
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">

	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script src="javascript/script.js"></script>

	<script>
	$(function () {
		var loading = false;
		$('#support-form').on('submit', function () {
			var name = $('#name').val(),
				email = $('#email').val(),
				steamProfileLink = $('#steam-profile').val(),
				desc = $('#desc').val();

			var shouldReturnEarly = false;

			$('.field').each(function (key, value) {
				var $field = $(value);

				if ($field.val().length === 0) {
					$field.css('border', '1px solid red');
				} else {
					$field.css('border', '1px solid rgb(204, 204, 204)');
				}
			});

			if (shouldReturnEarly) {
				return false;
			}

			if (loading) {
				return false;
			}

			$('#submit-button').html('Loading&hellip;');
			loading = true;

			$.post('php/support-ticket.php', {
				name: name,
				email: email,
				steamProfileLink: steamProfileLink,
				desc: desc
			}, function (jsonObj) {
				handleJsonResponse(jsonObj, function (data) {
					loading = false;
					$('#submit-button').html('Submit');

					var message = data['message'];

					successMsg(message);
				});
			}, 'json');

			return false;
		});
	});
	</script>
</head>
<body>
	<div class="navbar navbar-default navbar-static-top">
		<div class="container">
			<a href="/" class="navbar-brand">
				<img src="images/logo-black.png" style="height: 100%;">
			</a>

			<button class="navbar-toggle" data-toggle="collapse" data-target=".navHeaderCollapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<div class="collapse navbar-collapse navHeaderCollapse">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<a href="http://steamcommunity.com/groups/csgo_win_big" title="Join our Steam group!" target="_blank">
							<img src="images/steam.png" style="width: 30px;">
						</a>
					</li>
					<li style="margin-right: 10px;">
						<a href="http://twitter.com/csgowinbig" title="Follow us on Twitter!" target="_blank">
							<img src="images/twitter.png" style="width: 30px;">
						</a>
					</li>
					<li id="loading-menubar">
						<a>loading&hellip;</a>
					</li>
					<li class="login" style="display: none;">
						<a href="https://steamcommunity.com/openid/login?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.return_to=http%3A%2F%2Fwww.csgowinbig.com%2Fphp%2Fsteam-login-handle.php&openid.realm=http%3A%2F%2Fwww.csgowinbig.com&openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select">
							<img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png">
						</a>
					</li>
					<li class="logout" style="display: none;">
						<p class="navbar-btn">
							<a href="php/SteamAuthentication/steamauth/logout.php" class="btn btn-danger">Logout</a>
						</p>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="container" id="main-container">
		<div class="row main-row">
			<div class="col-sm-12">
				<div class="bg" style="padding: 10px;">
					<div style="text-align: center; font-size: 50px;">Support</div>
					If you have experienced some sort of problem, such as your item not showing up in the pot, or not receiving your payout after you have won, then submit a support ticket here, and we will work to fix your problem. After submitting a ticket, you can expect a response within 24 to 48 hours.
					<br><br>
					<form id="support-form">
						<div class="form-group">
							<label for="name">Your Name</label>
							<input class="form-control field" id="name" placeholder="name">
						</div>
						<div class="form-group">
							<label for="email">Your Email Address</label>
							<input type="email" class="form-control field" id="email" placeholder="email">
						</div>
						<div class="form-group">
							<label for="steam-profile">Link to your Steam Profile</label>
							<input class="form-control field" id="steam-profile" placeholder="steam profile url">
						</div>
						<div class="form-group">
							<label for="desc">Please describe your issue in detail (if you are missing items, please list them here in detail, with the name, quality, any stickers, etc&hellip;):</label>
							<textarea class="form-control field" id="desc" style="height: 100px;" placeholder="your issue"></textarea>
						</div>
						<button type="submit" class="btn btn-success btn-lg" id="submit-button">Submit</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="about-modal" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4>About CSGO Win Big</h4>
				</div>
				<div class="modal-body">
					CSGO Win Big is a new Counter-Strike: Global Offensive jackpot-style betting website that is fully open source. You can view the source code for the website <a href="https://github.com/ztizzlegaming/CSGOWinBig" target="_blank">here</a>, and view the source code for the Steam Bot that handles deposits and payouts <a href="https://github.com/ztizzlegaming/CSGOWinBig-SteamBot" target="_blank">here</a>. This website was created by, developed, and is run by Jordan Turley. If you have any questions about this website, email me at <a href="mailto:support@csgowinbig.com">support@csgowinbig.com</a>. And of course, good luck!

					<hr>

					Item prices:
					<br>
					Item prices are gotten from our database, which you can view <a href="prices.html">here</a>. If you find that a price for an item is incorrect, then you can submit a 'Price Change Ticket' there, and we will look into it. If for some reason an item cannot be found in our database, you can submit a 'New Item Ticket', and we will add the item as soon as possible. Until we add the item, the Steam community market will be used for pricing of these items.

					<hr>

					What sets us apart from other betting websites:
					<ul>
						<li>
							Low minimum bet
							<ul>
								<li>
									The minimum bet is only $0.20, in comparison to $2 and $3 from similar sites; low enough that anyone can participate, but high enough that the pot doesn't fill up with 4 cent skins.
								</li>
							</ul>
						</li>
						<li>
							Only 2% cut taken by website
							<ul>
								<li>
									This site will only take about a 2% cut, and will never take more than 5%. Similar sites take as much as 5% and no more than 10%.
								</li>
							</ul>
						</li>
						<li>
							Open Source
							<ul>
								<li>
									CSGO Win Big is fully open source. All of the code for both <a href="https://github.com/ztizzlegaming/CSGOWinBig" target="_link">the website</a> and <a href="https://github.com/ztizzlegaming/CSGOWinBig-SteamBot" target="_blank">the Steam bot</a> can be viewed on Github.
								</li>
							</ul>
						</li>
						<li>
							Not a Scam
							<ul>
								<li>
									These days, there are so many people trying to scam you. However, that is not me. This site is 100% legitimate; the source code for the website and the bot is public, and can be viewed by anyone.
								</li>
							</ul>
						</li>
					</ul>

					<hr>

					The way it works is simple:
					<ol>
						<li>Deposit your skins by clicking the button in the top right</li>
						<li>Your tickets will be calculated. For example, if you deposit $1 of skins, you get 100 tickets; $10 gets you 1000 tickets. 1 cent is 1 ticket.</li>
						<li>When the pot reaches 60 skins, a winner will be randomly chosen, and ~98% of the skins will be send to you in a trade offer (Learn more about this in 'Rules and other information' below).</li>
					</ol>

					<hr>

					"Why was my trade offer declined?" Your trade offer may have been declined because:
					<ul>
						<li>Your inventory is set to private.</li>
						<li>Your offer didn't meet the minimum bet of $0.20.</li>
						<li>Your offer was not a gift (you tried to get items from the bot instead of just giving).</li>
						<li>Your offer contained more than the 10 item limit.</li>
						<li>One or more of the items in your offer was not for CS: GO.</li>
					</ul>

					<hr>

					Rules and other information:
					<ul>
						<li>The pot can be a bit larger than 60 skins, depending on how many skins are in the last deposit.</li>
						<li>The site will keep about 2% of the skins in every pot, but no more than 5%. The amount taken will be as close to 2% as it can be, depending on the items in the pot, but will never be more than 5%. This is so that the staff of CSGO Win Big can keep the site running as best as it can, without having any annoying ads on the site.</li>
						<li>If you experience any lost items, such as depositing an item but not seeing it in the pot, or winning a pot but not receiving a trade offer, please submit a <a href="support.html">support ticket</a> and we will get back to you as soon as possible.</li>
					</ul>

					<hr>

					Copyright &copy; 2015 Jordan Turley, CSGO Win Big. All Rights Reserved. Background image is from Steam, and can be found <a href="http://cdn.akamai.steamstatic.com/steamcommunity/public/images/items/730/955e56085e78606ce8b56d7d912e2cd738af4215.png">here</a>.
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>