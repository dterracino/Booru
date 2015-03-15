<?php

require_once("helper.php");
require_once("config.php");

function send_avatar($path)
{
	header("Content-Type: image/png");
	cache_headers(12 * 3600);
	send_file($path);
}

if (isset($_GET["id"]))
{
	$id = $_GET["id"];
	if (is_numeric($id))
	{
		$path = $avatar_dir . "avatar" . $id . ".png";
		if (!file_exists($path))
		{
			$path = $avatar_dir . "default.png";
			if (!file_exists($path))
			{
				http_response_code(404);
				echo "File not found";
			}
			else send_avatar($path);
		}
		else send_avatar($path);
	}
	else
	{
		http_response_code(400);
		echo "ID not numeric";
	}
}
else
{
	http_response_code(400);
	echo "ID not set";
}

?>
