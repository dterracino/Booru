<?php
require_once("session.php");

function redirect_back()
{
	if (isset($_POST["redirect_url"]))
	{
		$url = base64_decode($_POST["redirect_url"]);
		header("Location: " . $url);
	}
	else header("Location: index.php");
	http_response_code(302);
}

if (isset($_POST["type"]))
{
	if ($_POST["type"] == "login")
	{
		if (isset($_POST["username"]) && isset($_POST["password"]))
		{
			if (!session_login($_POST["username"], $_POST["password"]))
			{
				http_response_code(403);
				echo "Authentication failed";
			}
			else redirect_back();
		}
		else
		{
			http_response_code(401);
			echo "Credentials not defined";
		}
	}
	else if ($_POST["type"] == "logout")
	{
		session_logout();
		redirect_back();
	}
	else
	{
		http_response_code(400);
		echo "Unknown request type";
	}
}
else
{
	http_response_code(400);
	echo "Request type not set";
}

?>
