<?php

require_once("config.php");

//TODO Implement thumbnail engine
function thumb_engine($post_id, $image_data, $mime)
{
	global $thumb_dir;
	$thumb_file = $thumb_dir . "thumb" . $post_id . ".jpg";

	if ($mime == "application/x-shockwave-flash")
	{
		$flash_thumb = $thumb_dir . "thumb_flash.jpg";
		copy($flash_thumb, $thumb_file);
	}
	else
	{
		$default_thumb = $thumb_dir . "thumb_default.jpg";
		copy($default_thumb, $thumb_file);
	}
}

?>
