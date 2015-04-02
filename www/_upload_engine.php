<?php

require_once("_db.php");
require_once("_config.php");
require_once("_thumb_engine.php");

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

	$hash = substr(hash("sha256", $image_data), 0, 20);

	$ntags = array();
	foreach ($tags as $otag)
		$ntags[] = strtolower(trim($otag));
	$tags = array_unique($ntags);

	if ($db->begin_transaction())
		try
		{
			$post_id = $db->booru_add_post($user_id, $private, $source, $info, $rating, $width, $height, $mime, $hash);
			$db->booru_add_tags_to_post($post_id, $tags);

			$image_file = $image_dir . "image" . $post_id . $mime_types[$mime];
			file_put_contents($image_file, $image_data);

			thumb_engine($post_id, $image_data, $mime, $width, $height);

			if ($db->commit())
				return $post_id;
			else return "Couldn't commit to database";
		}
		catch (Exception $ex)
		{
			if ($db->rollback())
				return $ex->getMessage();
			else return "Couldn't rollback transaction\n" . $ex->getMessage();
		}
	else return "Couldn't begin transaction";
}

?>
