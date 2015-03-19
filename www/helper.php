<?php

require_once("config.php");

function cache_headers($seconds)
{
	header("Cache-Control: public, max-age=" . $seconds, true);
	header("Expires: " . gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT");
	header_remove("Pragma");
}

function etag_header($etag)
{
	header("ETag: " . $etag);
}

function etag_check($etag)
{
	if (isset($_SERVER["HTTP_IF_NONE_MATCH"]))
		return $_SERVER["HTTP_IF_NONE_MATCH"] == $etag;
}


function send_file($filepath, $mime)
{
	header("Content-Type: " . $mime);
	header("Content-Length: " . filesize($filepath));

	global $sendfile_method;
	if ($sendfile_method == 0)
		readfile($filepath);
	else if ($sendfile_method == 1)
		header("X-LIGHTTPD-send-file: " . $filepath);
	else
	{
		http_response_code(500);
		echo "Sendfile method not implemented";
	}
}

function embed_image($id, $mime, $width, $height)
{
	$mime_category = explode("/", $mime)[0];
	$source_url = "image.php?type=image&amp;id=" . $id;

	if ($mime_category == "image")
		echo '<img id="mimg" class="mimg" alt="Main Image" src="' . $source_url . '">';
	else if ($mime_category == "video")
	{
		echo '<video class="mimg" id="mimg" loop autoplay>';
		echo '<source src="' . $source_url . '" type="' . $mime;
		echo '" width="' . $width . 'px" height="' . $height . 'px">';
		echo "Video not supported</video>";
	}
	else
	{
		echo '<object data="' . $source_url . '" type="' . $mime;
		echo '" width="' . $width . 'px" height="' . $height . 'px">';
		echo "Object not supported</object>";
	}
}

?>
