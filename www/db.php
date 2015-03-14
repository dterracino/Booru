<?php

require_once("config.php");

class BooruDB extends mysqli
{
	public function __construct()
	{
		global $mysql_host, $mysql_username, $mysql_password;
		parent::__construct($mysql_host, $mysql_username, $mysql_password, "booru");
	}
}

$db = new BooruDB();

?>
