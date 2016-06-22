<?php

require_once("_db.php");
require_once("_config.php");
require_once("_thumb_engine.php");

require_once("lib_imagehash/src/ImageHash.php");
require_once("lib_imagehash/src/Implementation.php");
require_once("lib_imagehash/src/Implementations/DifferenceHash.php");

use Jenssegers\ImageHash\ImageHash;

function upload_engine($image_data, $user_id, $private, $source, $info, $rating, $tags, $force)
{
	global $db, $thumb_dir, $image_dir, $mime_types;

	$finfo = finfo_open();
	$mime = finfo_buffer($finfo, $image_data, FILEINFO_MIME_TYPE);
	finfo_close($finfo);
	if (!array_key_exists($mime, $mime_types))
		return "MIME Type not allowed";

	if (!$force && filter_var($source, FILTER_VALIDATE_URL))
	{
		$post_id_dupe = $db->booru_post_with_source_exists($source);
		if (!is_null($post_id_dupe))
			return "Post with this source URL already exists (ID " . $post_id_dupe . ")";
	}

	$size = getimagesizefromstring($image_data);
	$width = $size[0];
	$height = $size[1];
	if ($width < 1 || $height < 1)
#TODO WebM image size support
#		return "Couldn't determine image size";
		$width = $height = 0;	// TODO WebM image size support

	$hash = substr(hash("sha256", $image_data), 0, 20);

	if (!$force)
	{
		$post_id_dupe = $db->booru_post_with_hash_exists($hash);
		if (!is_null($post_id_dupe))
			return "Post with this image hash already exists (ID " . $post_id_dupe . ")";
	}

	if (strpos($mime, "image/") === 0) // is image
	{
		$image_resource = imagecreatefromstring($image_data);
		$hasher = new ImageHash();
		$phash = $hasher->hash($image_resource);

		if (!$force)
		{
			$similar_ids = $db->booru_find_similar_images($phash);
			if (count($similar_ids) > 0)
				return "Posts with similar images exist (IDs: " . implode(', ', $similar_ids) . ')';
		}
	}
	else $phash = "";

	$ntags = array();
	foreach ($tags as $otag)
		$ntags[] = strtolower(trim($otag));
	$tags = array_unique($ntags);

	if ($db->begin_transaction())
		try
		{
			$post_id = $db->booru_add_post($user_id, $private, $source, $info, $rating, $width, $height, $mime, $hash, $phash);
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
