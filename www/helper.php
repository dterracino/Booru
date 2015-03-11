<?php

require_once("config.php");

function cache_headers($seconds)
{
	header("Cache-Control: public, max-age=" . $seconds, true);
	header("Expires: " . gmdate("D, d M Y H:i:s", time() + $seconds) . " GMT");
	header_remove("Pragma");
}

function send_file($filepath)
{
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

?>
