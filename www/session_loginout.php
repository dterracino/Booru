<?php

require_once("_html.php");
require_once("_session.php");

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
				html_error("Login", 403, "Authentication failed");
			else redirect_back();
		}
		else html_error("Login", 400, "Credentials not defined");
	}
	else if ($_POST["type"] == "logout")
	{
		session_destroy();
		redirect_back();
	}
	else html_error("Session", 400, "Unknown session request type");
}
else html_error("Session", 400, "Session request type not set");

?>
