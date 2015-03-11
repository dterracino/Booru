<?php

require_once("db.php");
require_once("config.php");

/* Used session variables

--- Used for user session
logged_in
username
user_id

--- (Not yet) used for mobile detection
user_agent
is_mobile

*/

session_start();

function session_login($username, $password)
{
	global $db;
	$user_id = $db->booru_login($username, $password); //TODO Move booru_login method into this file
	if (!($user_id < 0))
	{
		$_SESSION["logged_in"] = true;
		$_SESSION["username"] = $username;
		$_SESSION["user_id"] = $user_id;
		return true;
	}
	return false;
}

function session_logout()
{
	$_SESSION["logged_in"] = false;
	$_SESSION["username"] = NULL;
	$_SESSION["user_id"] = -1;
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
