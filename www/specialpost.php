<?php

require_once("db.php");

$query = "SELECT id FROM posts ORDER BY RAND() LIMIT 1";
if (isset($_GET["type"]))
	if ($_GET["type"] == "newest")
		$query = "SELECT id FROM posts ORDER BY created DESC LIMIT 1";

$result = $db->query($query);
$id = $result->fetch_row()[0];

header("Location: post.php?id=" . $id);
http_response_code(307);

?>
