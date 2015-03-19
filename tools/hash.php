#!/usr/bin/php
<?php

if (isset($argv[1]))
{
	$data = file_get_contents($argv[1]);
	echo substr(hash("sha256", $data), 0, 20);
}
else echo "Usage: hash.php <file>";
echo "\n";

?>
