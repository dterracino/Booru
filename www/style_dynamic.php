<?php
header("Content-Type: text/css");

require_once("helper.php");
require_once("config.php");

cache_headers(12 * 3600);

$current_date = date("d.m.", time());

if (in_array($current_date, $special_days))
	$background = "linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet)";
else $background = "black";

?>

div.thumb img {
	max-width: <?php echo $thumb_size; ?>px;
	max-height: <?php echo $thumb_size; ?>px;
	vertical-align: middle;
}

div.thumb {
	width: <?php echo $thumb_size; ?>px;
	height: <?php echo $thumb_size; ?>px;
	float: left;
	margin: 6px;
	line-height: <?php echo $thumb_size; ?>px;
	text-align: center;
}

div.header {
	position: fixed;
	left: 0px;
	top: 0px;
	width: 100%;
	height: 52px;
	white-space: nowrap;
	overflow: hidden;
	box-shadow: 0px 0px 16px black;
	background: <?php echo $background; ?>;
}

body.index {
	background-image: url('images/confetti.svg');
	background-size: 7% auto;
	background-repeat: repeat;
}
