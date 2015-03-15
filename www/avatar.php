<?php

require_once("db.php");
require_once("helper.php");
require_once("config.php");
require_once("session.php");

function send_avatar($path)
{
	header("Content-Type: image/png");
	cache_headers(12 * 3600);
	send_file($path);
}

if (isset($_GET["username"]))
{
	$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
	$stmt->bind_param("s", $_GET["username"]);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows == 1)
	{
		$id = $result->fetch_row()[0];
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
		http_response_code(404);
		echo "User not found";
	}
}
else
{
	http_response_code(400);
	echo "Username not set";
}

?>
