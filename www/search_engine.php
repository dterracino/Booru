<?php

require_once("db.php");

function search_engine($search_string, $offset, $count)
{
	global $db;

	//TODO Implement search engine
	// $parts = explode(" ", $search_string);
	// "SELECT * FROM posts ORDER BY created DESC LIMIT " . $id_offset . ", " . $count

	$stmt = $db->prepare("SELECT id FROM posts ORDER BY created DESC");
	$stmt->execute();
	$result = $stmt->get_result();

	$ids = array();
	while ($row = $result->fetch_row())
		array_push($ids, $row[0]);
	return $ids;
}

?>
