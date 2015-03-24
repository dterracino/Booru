<?php

require_once("db.php");
require_once("session.php");

$query = "SELECT id FROM posts WHERE ";
if (!session_loggedin())
	$query .= "private = 0";
else if (session_has_perm("admin"))
	$query .= "1";
else $query .= "(private = 0 OR user_id = " . session_user_id() . ")";

$query_order_by = "RAND()";
if (isset($_GET["type"]))
	if ($_GET["type"] == "newest")
		$query = "created DESC";

$result = $db->query($query . " ORDER BY " . $query_order_by . " LIMIT 1");
$id = $result->fetch_row()[0];

header("Location: post.php?id=" . $id);
http_response_code(307);

?>
