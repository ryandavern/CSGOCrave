<?php
	session_start();
	require('../steam/openid.php');

	try {
		require('../steam/settings.php');
		require('../database.php');
		$openid = new LightOpenID($steamauth['domainname']);

		if ($openid->validate()) { 
			$id = $openid->identity;
			$ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
			preg_match($ptn, $id, $matches);
				  
			$_SESSION['steamid'] = $matches[1];

			$location = $steamauth['loginpage'];
			
			include '../steam/user.php';

			$steam64Id = $steamprofile['steamid'];

			# Check if the user has entered their trade link.
			$query = 'SELECT user_id, user_64, user_trade_token, user_role FROM users WHERE user_64="'.$steam64Id.'";';
			$result = mysqli_query($connection, $query);
			$_SESSION['trade_url'] = 'null';
			# Check if there is an account and trade url associated with the account.
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				# Grab the trade url from the query to the database.
				$trade_url = $row['user_trade_token'];
				$_SESSION['role'] = $row['user_role'];
				# If the trade url is some how empty, set the session trade_url equal to null. Otherwise set the session trade_url equal to the trade_url from the database.
				if ($trade_url === '')
					$_SESSION['trade_url'] = 'null';
				else
					$_SESSION['trade_url'] = $row['user_trade_token'];
			} else {
				$_SESSION['trade_url'] = 'null';
			}

			$identifier = md5($steam64Id.time().rand(1, 50));
			mysqli_query($connection, 'UPDATE users SET user_identifier="'.$identifier.'"');
			setcookie('identifier', $identifier, time() + 3600 * 24 * 7, '/');
			header('Location: '.$location);
		} else {
			echo "User is not logged in.\n";
		}
	} catch(ErrorException $e) {
		echo $e->getMessage();
	}
?>