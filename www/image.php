<?php

require_once("db.php");
require_once("helper.php");
require_once("config.php");
require_once("session.php");

$is_image = false;
if (isset($_GET["type"]))
	if ($_GET["type"] == "image")
		$is_image = true;

if (isset($_GET["id"]))
{
	$id = $_GET["id"];
	$stmt = $db->prepare("SELECT id, user_id, private, mime, hash FROM posts WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows == 1)
	{
		$post = $result->fetch_assoc();
		$id = $post["id"];
		if ($post["private"] == 0 || $post["user_id"] == session_user_id() || session_has_perm("admin"))
		{
			if ($is_image)
			{
				$path = $image_dir . "image" . $id;
				if (array_key_exists($post["mime"], $mime_types))
				{
					$mime = $post["mime"];
					$path .= $mime_types[$mime];
				}
				else
				{
					$mime = "application/octet-stream";
					$path .= ".bin";
				}
			}
			else
			{
				$mime = "image/jpeg";
				$path = $thumb_dir . "thumb" . $id . ".jpg";
			}

			if (file_exists($path))
			{
				cache_headers(12 * 3600);
				if ($post["hash"] != "")
				{
					if (!etag_check($post["hash"]))
					{
						etag_header($post["hash"]);
						send_file($path, $mime);
					}
					else http_response_code(304);
				}
				else send_file($path);
			}
			else
			{
				http_response_code(404);
				echo "File not found";
			}
		}
		else
		{
			http_response_code(403);
			echo "Access denied";
		}
	}
	else
	{
		http_response_code(404);
		echo "Image not found";
	}
}
else
{
	http_response_code(400);
	echo "ID not set";
}

?>
