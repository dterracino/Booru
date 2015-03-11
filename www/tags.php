<?php

require_once("db.php");

$result = $db->query("SELECT tag, color FROM tags INNER JOIN tag_types ON tags.type_id = tag_types.id ORDER BY tag_types.id, tag");

while ($row = $result->fetch_row())
{
	// echo '<a href="' . $search_page . '?tags=' . urlencode($tag->tag) . '">';
	echo '<span style="color: ' . $row[1] . ';">';
	echo htmlspecialchars($row[0]) . "</span><br>";
}

?>
