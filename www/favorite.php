<?php

require_once("_db.php");
require_once("_helper.php");
require_once("_session.php");

if (isset($_POST["id"]) && isset($_POST["fav"]))
{
	$post_id = $_POST["id"];
	$set_fav = $_POST["fav"];

	if (is_numeric($post_id) && is_numeric($set_fav))
	{
		if (session_loggedin())
		{
			$user_id = session_user_id();
			try
			{
				$db->booru_user_toggle_favorite($user_id, $post_id, $set_fav > 0);
				echo "OK";
			}
			catch (Exception $e) { http_error(500, $e->getMessage()); }
		}
		else http_error(403, "Access denied");
	}
	else http_error(400, "ID or FAV not numeric");
}
else http_error(400, "ID or FAV not set");

?>
