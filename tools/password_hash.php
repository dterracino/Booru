#!/usr/bin/php
<?php

if (isset($argv[1]))
{
	$hash = password_hash($argv[1], PASSWORD_DEFAULT);
	if ($hash === FALSE)
		echo "An error occured";
	else echo $hash;
}
else echo "Usage: password_hash.php <password>";
echo "\n";

?>
