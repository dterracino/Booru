<?php

require_once("_db.php");
require_once("_helper.php");
require_once("_config.php");
require_once("_session.php");

$is_image = false;
if (isset($_GET["type"]))
	if ($_GET["type"] == "image")
		$is_image = true;

if (isset($_GET["id"]))
{
	$id = $_GET["id"];
	$stmt = $db->x_prepare("SELECT id, user_id, private, mime, hash FROM posts WHERE id = ?");
	$db->x_check_bind_param($stmt->bind_param("i", $id));
	$result = $db->x_execute($stmt, true);

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
				cache_headers(24 * 3600); // 1 day
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
			else http_error(404, "Image file not found");
		}
		else http_error(403, "Access denied");
	}
	else http_error(404, "Image not found");
}
else http_error(400, "ID not set");

?>
