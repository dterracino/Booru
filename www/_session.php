<?php

require_once("_db.php");
require_once("_config.php");

/* Used session variables

--- Used for user session
logged_in
username
user_id
p_*

--- (Not yet) used for mobile detection
user_agent
is_mobile

*/

session_start();

function session_login($username, $password)
{
	global $db;
	try
	{
		$user = $db->booru_get_user_by_username($username);
		if (password_verify($password, $user["pw_hash"]))
		{
			$_SESSION["logged_in"] = true;
			$_SESSION["username"] = $user["username"];
			$_SESSION["user_id"] = $user["id"];
			foreach ($user as $key => $value)
				if (substr($key, 0, 2) == "p_")
					$_SESSION[$key] = $value > 0;
			return true;
		}
	}
	catch (Exception $ex) { }
	return false;
}

function session_loggedin()
{
	if (isset($_SESSION["logged_in"]))
		return $_SESSION["logged_in"] === true;
	return false;
}

function session_username()
{
	if (session_loggedin())
		return $_SESSION["username"];
	return NULL;
}

function session_user_id()
{
	if (session_loggedin())
		return $_SESSION["user_id"];
	else return -1;
}

function session_has_perm($perm_name)
{
	if (session_loggedin())
		return $_SESSION["p_" . $perm_name];
	else return false;
}

function session_is_mobile()
{
	//TODO Implement mobile detection
	return false;
}

function session_printform()
{
	echo '<form method="POST" action="session_loginout.php">';
	echo '<input type="hidden" name="redirect_url" value="';
	echo base64_encode($_SERVER["REQUEST_URI"]) . '">';
	if (session_loggedin())
	{
		echo '<input type="hidden" name="type" value="logout">';
		echo session_username();
		echo ' <input type="submit" value="Logout">';
	}
	else
	{
		echo '<input type="hidden" name="type" value="login">';
		echo '<input style="width: 80px;" type="text" name="username">';
		echo ' <input style="width: 80px;" type="password" name="password">';
		echo ' <input type="submit" value="Login">';
	}
	echo "</form>";
}

?>
