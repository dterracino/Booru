<?php

require_once("db.php");
require_once("session.php");

if (!session_loggedin())
	$query_where = "private = 0";
else if (session_has_perm("admin"))
	$query_where = "1";
else $query_where = "(private = 0 OR user_id = " . session_user_id() . ")";

$query_order_by = "RAND()";
if (isset($_GET["type"]))
	if ($_GET["type"] == "newest")
		$query_order_by = "created DESC";

$result = $db->query("SELECT id FROM posts WHERE " . $query_where . " ORDER BY " . $query_order_by . " LIMIT 1");
$id = $result->fetch_row()[0];

header("Location: post.php?id=" . $id);
http_response_code(307);

?>
