#!/usr/bin/php
<?php

if (isset($argv[1]))
{
	$info = getimagesize($argv[1]);
	echo "Image size: " . $info[0] . " x " . $info[1];
}
else echo "Usage: image_size.php <file>";
echo "\n";

?>
