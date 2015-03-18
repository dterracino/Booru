<?php

require_once("db.php");
require_once("config.php");

function get_tag_id($tag)
{
	global $db;
	$stmt = $db->prepare("SELECT id FROM tags WHERE tag = ?");
	$stmt->bind_param("s", $tag);
	if (!$stmt->execute())
		throw new Exception("Couldn't select tag");
	$result = $stmt->get_result();
	if ($result->num_rows < 1)
	{
		$stmt = $db->prepare("INSERT INTO tags (tag) VALUES (?)");
		$stmt->bind_param("s", $tag);
		if (!$stmt->execute())
			throw new Exception("Couldn't add tag");
		return $db->insert_id;
	}
	else return $result->fetch_row()[0];
}

function upload_engine($image_data, $user_id, $private, $source, $info, $rating, $tags)
{
	global $db, $thumb_dir, $image_dir, $mime_types;

	$finfo = finfo_open();
	$mime = finfo_buffer($finfo, $image_data, FILEINFO_MIME_TYPE);
	finfo_close($finfo);
	if (!array_key_exists($mime, $mime_types))
		return "MIME Type not allowed";

	$size = getimagesizefromstring($image_data);
	$width = $size[0];
	$height = $size[1];
	if ($width < 1 || $height < 1)
		return "Couldn't determine image size";

	$hash = substr(hash("sha256", $image_data), 20);
	if ($private)
		$private_int = 1;
	else $private_int = 0;

	$db->begin_transaction();
	try
	{
		$query = "INSERT INTO posts (user_id, private, source, info, rating, width, height, created, mime, hash)";
		$query .= " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $db->prepare($query);
		$stmt->bind_param("iissiiiiss", $user_id, $private_int, $source, $info, $rating, $width, $height, time(), $mime, $hash);
		if (!$stmt->execute())
			throw new Exception("Couldn't add post");

		$post_id = $db->insert_id;
		$tag_id = 0;

		$stmt = $db->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
		$stmt->bind_param("ii", $post_id, $tag_id);
		foreach ($tags as $tag)
		{
			$tag_id = get_tag_id($tag);
			if (!$stmt->execute())
				throw new Exception("Couldn't add post_tag");
		}

		$db->commit();
		return $db->insert_id;
	}
	catch (Exception $ex)
	{
		$db->rollback();
		return $ex->getMessage();
	}

	//Move file to folder
	//Create thumbnail
}

?>
