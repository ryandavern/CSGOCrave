//The most recent ID for the chat, pot count, and last game ID
var localMostRecentID = 0, potCount = -1, mLastGameID = 0;

//Whether or not the call of update() is the first call
var firstUpdate = true;

//The most recent ID for the user sending the chat.
//Used if they want to send multiple messages before the chat refreshes.
var localChatIDForColor = 0;

// Whether or not the user is logged in
var loggedIn = false;

//The user's information
var mUserInfo = null;
var logged_in = "<?php echo isset($_SESSION['steamid]) ?>";
var steam_64_id = '';

var updateWaitTime = 1000;
var cached_current_round_amount = 0;
var cached_current_round_skins = 0;
var your_items = 0;
var your_item_worth = 0;

var socket = io.connect("http://localhost:1210");

$.ajax({
	type: "POST",
	url: "http://localhost/CSGOCrave/includes/utilities/get-user-64.php",
	success: function(data) {
		steam_64_id = data['id'];
	},
	dataType: "json"
});
$(function() {
	$(window).resize(function() {
		if ($(this).width() < 500) {
			$('.navigation-right').css('float','left');
		} else {
			$('.navigation-right').css('float','right');
		}
	});
	socket.on('error', function() {
        if (!socket.socket.connected) {
            $.notify("You lost connection to the server. Trying to reconnect...", "error");
        }
    });
    var identifier = getCookie('identifier');
    $(window).on('load', function() {
    	socket.emit('identifier', {identifier: identifier});
    	$('.tooltip-borderless').tooltipster({
    		theme: 'tooltipster-borderless',
    		animation: 'grow',
   			delay: 1,
   			contentAsHTML: true
		});
    });
    socket.on('connect', function(msg) {
        socket.emit('identifier', {identifier: identifier});
    });
	socket.on('update connections', function(data) {
		var connections = data.connections;
		$('#online-users').text(connections + " online");
	});
	socket.on('deposit', function(data) {
		var message = data.message;
		notify(message, "error");
	});
	socket.on('alert', function(data) {
		var message = data.message;
		notify(message, 'info');
	});
	socket.on('success', function(data) {
		var message = data.message;
		notify(message, 'success');
	});
	socket.on('trade-token', function(data) {
		var message = data.message;
		notify(message, "error");
	});
	socket.on('trade-sent', function(data) {
		receivedTrade(data.trade_url)
	});
	socket.on('new-message', function(data) {
		var chatColor = '';
		if (data.role === 'OWNER')
			chatColor = '#E74C3C';
		else if (data.role === 'MODERATOR')
			chatColor = '#9B59B6';
		else if (data.role === 'VERIFIED')
			chatColor = '#2ECC71';
		else
			chatColor = '#FFFFFF';

		$('.message').append('<li class="msg"><div class="header"><img src="' + data.avatar + '" alt="" width="32" height="32" rel="nofollow"><span class="name" style="color: ' + chatColor + ';"><strong>' + data.steam_name + '</strong></span></div><p class="content">' + data.message + '</p></li>');
	});
	$('#chat-input').on('keydown', function (event) {
		if (event.which === 13) {
			var text = this.value;

			if (text.length === 0)
				return;
			if (steam_64_id == '') {
				notify('Please login before typing in the chat!', 'error');
				return;
			}
			$('#chat-input').val('');
			socket.emit('chat-message', {user_64:steam_64_id, message:text});
		}
	});
});

function runJackpotFunctions() {
	socket.on('jackpot game over', function(data) {
		$.ajax({
			type: "GET",
			url: "http://localhost/CSGOCrave/includes/utilities/jackpot-win-event.php",
			success: function(data) {
			    if (data.name == '') {
					//displayError('Deposit Error', data.message);
				} else {
			    	progress_bar.set(0);
			    	doGambleImageSpin(data.user_64, data.name, data.image, data.secret, data.next_round_hash, data.round, data.winning_percentage, data.winner_percentage);
			    }
			},
			dataType: "json"
		});
	});
	socket.on('update jackpot timer', function(data) {
		var time = data.countdown;
		$('#time-left').text(time);
	});
	socket.on('new bet', function(data) {
		var user_64 = data.user_64;
		var steam_name = data.steam_name;
		var avatar = data.avatar;
		var skin_amount = data.skin_amount;
		var value = data.worth;
		var items = data.items;

		addDeposit(steam_name, avatar, Number(skin_amount), Number(value), items);
		if (user_64 == steam_64_id) {
			your_items += Number(skin_amount);
			your_item_worth += Number(value);
			$.notify("Your deposit of " + skin_amount + " skins valued at " + value + " was successful!", "success");
			var chance_of_winning = (your_item_worth / cached_current_round_amount) * 100;
			chance_of_winning = Number(Math.round(chance_of_winning + 'e2') + 'e-2').toFixed(2);

			document.getElementById("join-info").innerHTML = "Items: " + your_items + "/20 ($" + your_item_worth + ") - Chance of winning: - " + chance_of_winning + "%";
		}
		updateJackpotInformation(value, skin_amount);
	});
}

function runCoinflipFunctions() {
	socket.on('inventory', function(data) {
		var inventory = data.inventory;
		var game_id = data.game_id;
		var item = JSON.stringify(inventory);
		if (game_id == '') {
			$.post('http://localhost/CSGOCrave/includes/utilities/get-inventory.php', {items:item}, function(items, status) {
				displayInventory(game_id, 0, items);
			});
		} else {
			$.post('http://localhost/CSGOCrave/includes/utilities/get-coinflip-investment.php', {game_id:game_id}, function(data, status) {
				$.post('http://localhost/CSGOCrave/includes/utilities/get-inventory.php', {items:item}, function(items, status) {
					displayInventory(game_id, data, items);
				});
			});
		}
	});
	socket.on('game-end', function(data) {
		var game_id = data.game_id;
		setTimeout(function () {
			deleteCoinflip();
		}, 10000);
	});
	socket.on('countdown-timer', function(data) {

	});
}

var spinning = false;

function spin(side_won, secret, winning_percentage, winner_64, total_value) {
	setTimeout(function () {
		if (side_won == 'CT') {
			$('.flip-animation').css('transform', 'rotateY(4320deg)');
			$('.flip-animation').css('-webkit-transform', 'rotateY(4320deg)');
			$('.flip-animation').css('-moz-transform', 'rotateY(4320deg)');
			$('.flip-animation').css('-o-transform', 'rotateY(4320deg)');
		}
		else {
			$('.flip-animation').css('transform', 'rotateY(4860deg)');
			$('.flip-animation').css('-webkit-transform', 'rotateY(4860deg)');
			$('.flip-animation').css('-moz-transform', 'rotateY(4860deg)');
			$('.flip-animation').css('-o-transform', 'rotateY(4860deg)');
		}
	}, 100);
	setTimeout(function () {
		$('#secret_value').text(secret);
		$('#ticket_percentage_value').text(winning_percentage + "%");
		$('#secret').css('display', 'initial');
		$('#ticket_percentage').css('display', 'initial');
		if (side_won == 'T') {
			$('#ct_player').css("opacity", "0.5");
		} else {
			$('#t_player').css("opacity", "0.5");
		}
		if (steam_64_id == winner_64)
			notify('Congratulations you won the coinflip valued at $' + total_value + '!', 'success');
	}, 3900);
}

function doGambleImageSpin(user_64, name, winner_image, secret, next_round_hash, round, winning_percentage, winner_percentage) {
	$.ajax({
		type: "POST",
		url: "http://localhost/CSGOCrave/includes/utilities/get-jackpot-players.php",
		success: function(data) {
			var image = [];
			for (var i = 0; i < data.length; i++) {
				for (i2 = 0; i2 < 20; i2++) {
					var obj = data[i];
					image.push(obj);
				}
			}
			shuffle(image);
			var left = image.slice(0, 24);
			var right = image.slice(24);

			for (i = 0; i < left.length; i++) {
			    $('.owl-carousel').append('<div><img src="' + left[i] + '" alt="Player Image"/></div>');
			}

			$('.owl-carousel').append('<div><img src="' + winner_image + '" /></div>');

			for (i = 0; i < right.length; i++) {
			    $('.owl-carousel').append('<div><img src="' + right[i] + '" alt="Player Image"/></div>');
			}

			var owl = $('.owl-carousel');
			owl.owlCarousel({
				items: 10,
				autoHeight: false,
				center: true,
				nav: false,
			    dotsData: false,
				mouseDrag: false,
				slideSpeed : 500,
			});
			var i = 0;
			var equation = (image.length / 2) + 1;
			console.log(equation);
			
			var i = 0;
			while (i < 25) {
				if (i == 24) {
					setTimeout(function () {
						$('.owl-carousel').empty();
						$('#roulette').css("display: none;");
						notifyWinner(user_64, cached_current_round_amount, winning_percentage, round);
						addWinner(user_64, name, winner_image, secret, next_round_hash, round, winning_percentage, winner_percentage);
					}, 3500);
					return;
				}
				i++;
				owl.trigger('next.owl.carousel', [1500]);
			}			
		},
		dataType: "json"
	});
}

function addDeposit(steam_name, avatar, skin_amount, value, items) {
	if (spinning == true) {
		setTimeout(function () {
			var skin_title = skin_amount <= 1 ? 'skin' : 'skins';
			$('.round:first').prepend('<div class="bet"><img src="' + avatar + '" width="35" height="35" class="image player-image"/><strong>' + steam_name + '</strong> deposited ' + skin_amount + ' ' + skin_title + ' valued at $' + value + '</div>').hide().fadeIn(1000);
			$('#pot-price').prop('number', cached_current_round_amount).animateNumber({number: (cached_current_round_amount + value)}, 5000);
			$('#pot-items').prop('number', cached_current_round_skins).animateNumber({number: (cached_current_round_skins + skin_amount)}, 5000);
			cached_current_round_amount += value;
			cached_current_round_skins += skin_amount;
		    document.title = '$' + Number(Math.round(cached_current_round_amount + 'e2') + 'e-2').toFixed(2) + " - CSGOCrave";
		   
			var percentage = (cached_current_round_skins / 50) * 100;
			progress_bar.set(percentage);

		    if (items != null) {
		    	for (i = 0; i < items.length; i++) {
		    		var price = getPrice(items[i]['item_class_id'], items[i]['item_name']);
		    	}
		    }
		}, 5000);
	} else {
		var skin_title = skin_amount <= 1 ? 'skin' : 'skins';
		$('.round:first').prepend('<div class="bet"><img src="' + avatar + '" width="35" height="35" class="image player-image"/><strong>' + steam_name + '</strong> deposited ' + skin_amount + ' ' + skin_title + ' valued at $' + value + '</div>').hide().fadeIn(1000);
		
		cached_current_round_amount += value;
		cached_current_round_skins += skin_amount;
		document.title = '$' + Number(Math.round(cached_current_round_amount + 'e2') + 'e-2').toFixed(2) + " - CSGOCrave";

		var percentage = (cached_current_round_skins / 50) * 100;
		progress_bar.set(percentage);

		if (items != null) {
		    for (i = 0; i < items.length; i++) {
		    	var price = getPrice(items[i]['item_class_id'], items[i]['item_name']);
		    }
		}
	}
}

function updateJackpotInformation(value, skin_amount) {
	var decimal_places = 2;
	var decimal_factor = decimal_places === 0 ? 1 : Math.pow(10, decimal_places);
	var start_price_number = 0, start_skin_number = 0;
	if (value > 0)
		start_price_number = cached_current_round_amount;
	if (skin_amount > 0)
		start_skin_number = cached_current_round_skins;

	$('#pot-price').prop('number', start_price_number).animateNumber({
		number: (cached_current_round_amount + value) * decimal_factor,
		numberStep: function(now, tween) {
			var floored_number = Math.floor(now) / decimal_factor, target = $(tween.elem);
			if (decimal_places > 0) {
				floored_number = floored_number.toFixed(decimal_places);
				floored_number = floored_number.toString().replace('.', ',');
		    }
		    target.text('$' + floored_number);
		}
	},5000);
	$('#pot-items').prop('number', start_skin_number).animateNumber({
		number: (cached_current_round_skins + skin_amount),
		numberStep: function(now, tween) {
			var target = $(tween.elem);
		    target.text(Math.floor(now).toFixed(0) + "/50");
		}
	},5000);
	//$('#pot-items').prop('number', cached_current_round_skins).animateNumber({number: (cached_current_round_skins + skin_amount)}, 5000);
}

function addItemsToDisplay(class_id, item_price, item_name) {
	$('#item-collection').prepend('<div class="item"><img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + class_id + '/120fx100f" alt="CSGO Item" rel="nofollow"><div class="price">$' + item_price + '</div><div class="name">' + item_name + '</div></div>');
}

function getPrice(class_id, item_name) {
	var url = "../includes/utilities/get-item-price.php";
	$.post(url, {item_name:item_name}, function(data, status) {
		if (data === 'Incorrect Item Name') {
			console.log(item_name);
			return;
		}
		$('#item-collection').prepend('<div class="item"><img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + class_id + '/120fx100f" alt="CSGO Item" rel="nofollow"><div class="price">$' + data + '</div><div class="name">' + item_name + '</div></div>');
    	
        return data;
    });
}

function displayWinningData(hash, secret) {
	$.post('../includes/utilities/find-winning-percentage.php', {hash:hash, secret:secret}, function(data, status) {
		var percentage = data;
		var rounded_percentage = (percentage * 100);
		rounded_percentage = Number(Math.round(rounded_percentage + 'e2') + 'e-2').toFixed(2);
		swal({
			titleText: "Round Win Percentage",
			type: 'info',
			closeOnClickOutside: true,
			confirmButtonText: 'Close',
			confirmButtonColor: '#2ECC71',
			showCancelButton: false,
			background: '#262626',
			focusConfirm: false,
			html: '<p style="color: #FFFFFF;">' + data + " (" + rounded_percentage + "%)<p>",
			buttonsStyling: true,
		});
	});
}

function displayChatRules() {
	swal({
		titleText: "Chat Rules",
		type: 'info',
		closeOnClickOutside: true,
		confirmButtonText: 'Close',
		confirmButtonColor: '#2ECC71',
		showCancelButton: false,
		background: '#262626',
		focusConfirm: false,
		html: '<ul><li style="color: #FFFFFF;">No advertising.</li><li style="color: #FFFFFF;">No spamming.</li><li style="color: #FFFFFF;">No harrasing others.</li><p>Breaking any of the above rules will result in a chat timeout.</p></ul>',
		buttonsStyling: true,
	});
}

function displayChatSettings() {
	swal({
		titleText: "Chat Settings",
		closeOnClickOutside: true,
		confirmButtonText: 'Close',
		confirmButtonColor: '#2ECC71',
		showCancelButton: false,
		background: '#262626',
		focusConfirm: false,
		html: '<ul><li style="color: #FFFFFF;"><strong>/togglechat</strong> - Mutes / Unmutes the chat (Verified and moderators can still talk in the chat).</li></ul>',
		buttonsStyling: true,
	});
}

var trade_list = [];
var minimum_investment = 0;
var maximum_investment = 0;
var user_invested = 0;

function displayInventory(game_id, investment, items) {
	console.log(items);
	if (game_id == undefined)
		game_id = '';

	minimum = Number(investment) - (Number(investment) * 0.10);
	maximum = Number(investment) + (Number(investment) * 0.10);

	this.user_invested = 0;
	this.minimum_investment = Number(minimum);
	this.maximum_investment = Number(maximum);
	
	var str = items.toString();
	//console.log(str);
	var inventory = JSON.parse(items);
	
 	trade_list = [];

	var string = '';
	if (game_id == '')
		string = '<p style="color: #E74C3C;" id="investment">Deposit: $0.00</p><div id="inventory">';
	else
		string = '<p style="color: #E74C3C;" id="investment">Needs: $' + minimum_investment + '</p><div id="inventory">';
	for (var i = 0; i < inventory.length; i++) {
		var item = inventory[i];
		var name = item[0];
		var assetid = item[1];
		var classid = item[2];
		var instanceid = item[3];
		var price = item['price'];
		string = string + '<div class="inventory-item" id="' + assetid + '"><img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + classid + '/120fx100f" alt="Item Image" class="inventory-select-item" title="' + name + '" onclick=\'addToTrade("' + assetid + '", "' + classid + '", "' + instanceid + '", "' + price + '", "' + game_id + '");\' /><br><small><strong>$' + price + '</strong></small></div>';
	}
	string = string + '</div>';
	swal({
		titleText: "Select Item",
		closeOnClickOutside: true,
		showCancelButton: true,
		confirmButtonText: 'Send Trade',
		confirmButtonColor: '#2ECC71',
		cancelButtonText: 'Cancel',
		cancelButtonColor: '#E74C3C',
		background: '#262626',
		focusConfirm: false,
		html: string,
		buttonsStyling: true,
		reverseButtons: true,
	}).then(function(inputValue) {
		if (trade_list.length <= 0) {
			displayInventory(items);
			return false;
		}
		if (game_id != '') {
			if (user_invested >= minimum_investment)
				socket.emit('join-game', {user_64:steam_64_id, game_id:game_id, items:trade_list});
			else {
				displayInventory(items);
				return false;
			}
		} else
			socket.emit('create-game', {user_64:steam_64_id, items:trade_list});
	});
}

function receivedTrade(trade) {
	swal({
		titleText: "You received a trade from us",
		closeOnClickOutside: true,
		showCancelButton: false,
		confirmButtonText: 'Open Trade',
		confirmButtonColor: '#2ECC71',
		background: '#262626',
		focusConfirm: false,
		buttonsStyling: true,
		reverseButtons: true,
	}).then(function(inputValue) {
		$('<a>').attr('href', "https://steamcommunity.com/tradeoffer/" + trade + "/").attr('target', '_blank')[0].click();
	});	
}

function addToTrade(asset_id, class_id, instance_id, price, game_id) {
	price = Number(Math.round(price + 'e2') + 'e-2').toFixed(2);
	var removing_item = false;
	if (trade_list.includes(asset_id)) {
		for(var i = trade_list.length - 1; i >= 0; i--) {
		    if(trade_list[i] === asset_id) {
		       trade_list.splice(i, 1);
		    }
		}
		$("#" + asset_id).css("border-bottom", "3px solid #262626").hide().fadeIn(250);
		removing_item = true;
	} else {
		trade_list.push(asset_id);
		$("#" + asset_id).css("border-bottom", "3px solid #2ECC71").hide().fadeIn(250);
	}

	if (removing_item)
		this.user_invested = Number(this.user_invested) - Number(price);
	else
		this.user_invested = Number(this.user_invested) + Number(price);

	this.user_invested = Number(Math.round(this.user_invested + 'e2') + 'e-2').toFixed(2);
	
	if (game_id == '') {
		// Making coinflip.
		if (this.user_invested <= 0)
			$("#investment").text("Deposit: $0.00");
		else
			$("#investment").text("Deposit: $" + Number(Math.round(this.user_invested + 'e2') + 'e-2').toFixed(2));
		if (this.user_invested >= 10)
			$("#investment").css("color", "#2ECC71");
		else
			$("#investment").css("color", "#E74C3C");
	} else {
		// Joining coinflip.
		var reached_minimum = (this.minimum_investment - this.user_invested) <= 0;
		var above_maximum = (this.maximum_investment - this.user_invested) < 0;

		if (reached_minimum && !above_maximum) {
			$("#investment").text("Valid Offer");
			$("#investment").css("color", "#2ECC71");
		} else {
			if (above_maximum) {
				$("#investment").text("Needs: -$" + Number(Math.round((this.user_invested - this.maximum_investment) + 'e2') + 'e-2').toFixed(2));
				$("#investment").css("color", "#E74C3C");
			} else {
				$("#investment").text("Needs: $" + Number(Math.round((this.minimum_investment - this.user_invested) + 'e2') + 'e-2').toFixed(2));
				$("#investment").css("color", "#E74C3C");
			}
		}
	}
}

function createNewCoinflip(user_64, url, steam_name, avatar, hash, side, value, items) {
	console.log('URL: ' + url);
	var side_image = 't_coin.png';
	if (side == 'ct')
		side_image = 'ct_coin.png';

	var image_string = '';

	for (var i = 0; i < items.length; i++) {
		if (i < 5) {
			var item_name = items[i][0];
			var classid = items[i][1];
			var price = items[i][2];
			image_string = image_string + '<img src="https://steamcommunity-a.akamaihd.net/economy/image/class/730/' + classid + '/70fx50f" alt="Item Image" class="tooltip-borderless" title="' + item_name + '<br>Price: $' + price + '" />';
		}
	}
	var remaining_item_count = items.length - 5;
	var extra_items_string = '<small> +' + remaining_item_count + ' more items</small>';
	if (remaining_item_count <= 0)
		extra_items_string = '';

	value = Number(Math.round(value + 'e2') + 'e-2').toFixed(2);
	var percentage_gap = 0.05;
	var min_amount = Number(Math.round(Number(value) - (Number(value) * percentage_gap) + 'e2') + 'e-2').toFixed(2);
	var max_amount = Number(Math.round(Number(value) + (Number(value) * percentage_gap) + 'e2') + 'e-2').toFixed(2);

	$(".coinflip-container").append('<tr class="coinflip-item" data-value="' + value + '" data-url="' + url + '"><td><img src="../images/' + side_image + '" width="35" height="35"/><img src="' + avatar + '" width="35" height="35" class="player-image"/></td><td><p>' + items.length + ' items</p>' + image_string + extra_items_string + '</td><td><strong>$</strong>' + Number(value) + '<br><small>Needs: $' + min_amount + ' - $' + max_amount + '</small></td><td class="timer"><p>10s</p></td><td><button onclick=\'showInventory("' + url + '")\'>Join</button><button onclick=\'redirect("' + url + '")\'>Watch</button></td></tr>').hide().fadeIn(1000);
}

function deleteCoinflip(url) {
	$('[data-url="' + url + '"]').fadeOut(1000, function(){
		$(this).remove();
	});	
}

function redirect(hash) {
	$('<a>').attr('href', 'game.php?id=' + hash)[0].click();
}

function joinCoinflip(user_64, avatar, hash, value, items) {
	console.log('l');
}

function addWinner(user_64, steam_name, avatar, secret, next_round_hash, round_number, winning_percentage, winner_percentage) {
	$('.round:first').prepend('<div class="winner"><img src="' + avatar + '" width="35" height="35" class="image player-image"/><strong>' + steam_name + '</strong> won $' + Number(Math.round(cached_current_round_amount + 'e2') + 'e-2').toFixed(2) + ' with a chance of ' + Number(Math.round(winner_percentage + 'e2') + 'e-2').toFixed(2) + '%<p class="info" id="round-win-info">Round: #' + round_number + ' | Secret: ' + secret + ' | Winning percentage: ' + (winning_percentage * 100) + '% </p></div>');
	$('#pot-items').text("0/50");
    $('#pot-price').text("$0.00");
    $('#time-left').text("2:00");
    document.title = "CSGOCrave";
    createNewJackpot(next_round_hash);
    
    cached_current_round_skins = 0;
	cached_current_round_amount = 0;
}

function notifyWinner(user_64, total_amount, winning_percentage, round_number) {
	if (user_64 == steam_64_id) {
    	var total_win = Number(Math.round(total_amount + 'e2') + 'e-2').toFixed(2);
    	var percentage = Number(Math.round(winning_percentage + 'e2') + 'e-2').toFixed(2);

    	notify("Congratulations! You won a total of $" + total_win + " with a " + (percentage * 100) + "% chance from the jackpot round #" + round_number + "!", "success");
    }
}

function createNewJackpot(hash) {
	$('#round-bet:first').prepend('<div class="round"></div>');
	$('.round:first').prepend('<p class="new-round">New round started.</p><p class="round-hash">Round Hash: ' + hash + '</p>');
	$('#item-collection').empty();
}

function showEnterTrade(error) {
	var text = '<p style="color:#FFFFFF;">Please enter your Trade URL.<br>Note: Make sure your trade url is correct. If you enter incorrect information you will not receive your winnings. You can find your trade url <a href=http://steamcommunity.com/id/me/tradeoffers/privacy" target="_blank">here</a>. Be sure to make your inventory public. This can be done <a href="http://steamcommunity.com/id/me/edit/settings/" target="_blank">here</a>.</p>';
	if (error) {
		text = '<p style="color:#E74C3C;">You must enter your trade url.</p>';
	}
	swal({
		titleText: "Trade URL",
		type: 'info',
		input: 'text',
		closeOnClickOutside: true,
		confirmButtonText: 'Submit',
  		confirmButtonColor: '#2ECC71',
  		cancelButtonText: 'Cancel',
  		cancelButtonColor: '#E74C3C',
  		reverseButtons: true,
  		showCancelButton: true,
  		background: '#262626',
  		focusConfirm: false,
  		html: text,
  		buttonsStyling: true,
  		inputPlaceholder: 'Trade URL',
	}).then(function(inputValue) {
		if (inputValue === false || inputValue.length === 0) {
			showEnterTrade(true);
			return false;
		}
		$.post('profile.php', { trade_url: inputValue }, function() {
			successMsg('Your trade url was successfully saved. Click OK then Deposit Skins again to deposit.');
		});
	});
}

function notify(message, type) {
	$.notify(message, type);
}

function displayUpdates() {
	swal({
		title: 'Crave Updates',
		type: 'info',
		html: '<p style="color:#FFFFFF;"><strong>CSGOCrave</strong><br>Welcome to CSGOCrave!<br><br><strong>Bugs and Alpha Testing</strong><br>We are currently in Alpha testing; which means there may be a few bugs.<br>For more information on what to do if a bug occurs, your items are eaten, or your win was not sent to you; Please click <a href="help.php">here</a></p>',
		background: '#262626',
		confirmButtonColor: '#2ECC71',
		closeOnClickOutside: true,
	});
}

function displayAcceptWinnings(trade_link) {
	swal({
		titleText: "Winnings Sent",
		type: 'success',
		closeOnClickOutside: true,
		confirmButtonText: 'Accept',
  		confirmButtonColor: '#2ECC71',
  		background: '#262626',
  		focusConfirm: false,
  		html: "<p style='color:#FFFFFF;'>We've sent you your winnings, accept them by clicking the button below.</p>",
  		buttonsStyling: true,
	}).then(function() {
		$('<a>').attr('href', trade_link).attr('target', '_blank')[0].click();
	});
}

function displayError(title, message) {
	swal({
		titleText: title,
		type: 'error',
		closeOnClickOutside: true,
		confirmButtonText: 'Ok',
  		confirmButtonColor: '#2ECC71',
  		background: '#262626',
  		focusConfirm: false,
  		html: "<p style='color:#FFFFFF;'>" + message + "</p>",
  		buttonsStyling: true,
	});
}
		
function startTrade() {
	$('<a>').attr('href', '<Trade Bot URL> - Will have to pick a bot from a list.').attr('target', '_blank')[0].click();
}

function getRandomColour() {
	var items = Array('#2ECC71', '#3498DB', '#E74C3C', '#9B59B6', '#F1C40F');
	var item = items[Math.floor(Math.random()*items.length)];
	console.log(item);
	return item;
}

function getFormattedDate() {
	var date = new Date();
	var month = date.getMonth() + 1;
	if (month < 10)
		month = '0' + month;
	return date.getFullYear() + "-" + month + "-" + date.getDate();
}

function getFormattedTime() {
	var date = new Date();
	return date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
}

function getFormattedPrice (cents) {
	if (typeof cents !== 'number')
		cents = parseInt(cents);
	var price = cents / 100;
	if (cents % 100 === 0)
		price = price + '.00';
	else if (cents % 10 === 0)
		price = price + '0';

	return '$' + price;
}

function getCookie(key){
	var pattern = new RegExp(key+"=([^;]*)");
	var matches = pattern.exec(document.cookie);
	if (matches)
		return matches[1];
	return "";
}

function shuffle(a) {
    var j, x, i;
    for (i = a.length - 1; i > 0; i--) {
        j = Math.floor(Math.random() * (i + 1));
        x = a[i];
        a[i] = a[j];
        a[j] = x;
    }
}