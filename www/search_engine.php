<?php

require_once("db.php");
require_once("session.php");

function search_engine($search_string, $offset, $count)
{
	global $db;

	// $parts = explode(" ", $search_string);
	// "SELECT * FROM posts ORDER BY created DESC LIMIT " . $id_offset . ", " . $count

	$stmt = $db->prepare("SELECT id FROM posts WHERE posts.private = 0 OR posts.user_id = ? ORDER BY created DESC");
	$stmt->bind_param("i", session_user_id());
	$stmt->execute();
	$result = $stmt->get_result();

	$ids = array();
	while ($row = $result->fetch_row())
		array_push($ids, $row[0]);

	return $ids;
}

?>
