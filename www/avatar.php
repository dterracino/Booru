<?php

require_once("_helper.php");
require_once("_config.php");

function send_avatar($path)
{
	cache_headers(12 * 3600);
	send_file($path, "image/png");
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
			if (file_exists($path))
				send_avatar($path);
			else http_error(404, "Avatar file not found");
		}
		else send_avatar($path);
	}
	else http_error(400, "ID not numeric");
}
else http_error(400, "ID not set");

?>
