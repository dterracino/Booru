<?php

require_once("_config.php");

function thumb_engine($post_id, $image_data, $mime, $width, $height)
{
	global $thumb_dir;
	$thumb_file = $thumb_dir . "thumb" . $post_id . ".jpg";

	if ($mime == "application/x-shockwave-flash")
	{
		// Copy the generic flash thumbnail
		$flash_thumb = $thumb_dir . "thumb_flash.jpg";
		copy($flash_thumb, $thumb_file);
	}
	else
	{
		// ALWAYS copy default thumbnail
		$default_thumb = $thumb_dir . "thumb_default.jpg";
		copy($default_thumb, $thumb_file);

		// Maybe we can generate the thumbnail right away
		// If the script fails, we already have the default thumb
		if ($width * $height * 4 < 300 * 1024 * 1024) // Max 300MB RAM uncompressed
			if (in_array($mime, array("image/jpeg", "image/png", "image/gif")))
				create_image_thumb($image_data, $width, $height, $thumb_file);
	}
}

function create_image_thumb($image_data, $width, $height, $path)
{
	global $thumb_gen_size;
	if ($width > $height)
	{
		$n_width = $thumb_gen_size;
		$n_height = $height / $width * $thumb_gen_size + 0.5;
	}
	else if ($width < $height)
	{
		$n_width = $width / $height * $thumb_gen_size + 0.5;
		$n_height = $thumb_gen_size;
	}
	else
	{
		$n_width = $thumb_gen_size;
		$n_height = $thumb_gen_size;
	}

	$image = imagecreatefromstring($image_data);
	$thumb = imagecreatetruecolor($n_width, $n_height);
	imagecopyresampled($thumb, $image, 0, 0, 0, 0, $n_width, $n_height, $width, $height);
	imagejpeg($thumb, $path);
}

?>
