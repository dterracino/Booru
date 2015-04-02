<?php

require_once("_db.php");
require_once("_html.php");
require_once("_helper.php");

try
{
	$result = $db->x_query("SELECT tag, color FROM tags INNER JOIN tag_types ON tags.type_id = tag_types.id ORDER BY tag_types.id, tag");

	cache_headers(2 * 3600); // 2 hours

	html_begin("All Tags");

	html_nav_element_begin("All Tags");
	echo "Refer to this alphabetical list of all tags if you are looking for tag existence";
	html_nav_element_end();

	html_body();

	while ($row = $result->fetch_row())
	{
		$tag = $row[0];
		$color = $row[1];

		echo '<a href="posts.php?tags=' . urlencode($tag) . '">';
		echo '<span style="color: ' . $color . ';">';
		echo htmlspecialchars($tag) . "</span></a><br>";
	}

	html_end();
}
catch (Exception $ex) { html_error("All Tags", 500, $ex->getMessage()); }

?>
