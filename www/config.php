<?php

$booru_name = "Booru";
// $motd = "Under Construction";

$mysql_host = "127.0.0.1";
$mysql_username = "booru";
$mysql_password = "booru";
$mysql_database = "booru";

// Must end with /
$thumb_dir = "/opt/booru/thumbs/";
$image_dir = "/opt/booru/images/";
$avatar_dir = "/opt/booru/avatars/";

// 0 = PHP readfile
// 1 = X-Sendfile (lighttpd) (untested)
// 2 = Header method (nginx) (not yet supported)
$sendfile_method = 0;

$special_days = array(
	"01.05.",
	"31.10.",
	"24.12.",
	"25.12.",
	"31.12.",
	"01.01."
);

$thumbs_per_page = 60;
$thumb_size = 120;

$max_search_terms = 8;

$mime_types = array(
	"image/jpeg" => ".jpg",
	"image/png" => ".png",
	"image/gif" => ".gif",
	"video/webm" => ".webm",
	"application/x-shockwave-flash" => ".swf"
);

// If your server doesn't provide the SERVER_NAME variable,
// please set it manually (instead of booru.example.com)
if (isset($_SERVER["HTTPS"]))
	$server_base_url = "https://";
else $server_base_url = "http://";
if (isset($_SERVER["SERVER_NAME"]))
	$server_base_url .= $_SERVER["SERVER_NAME"];
else $server_base_url .= "booru.example.com";

?>
