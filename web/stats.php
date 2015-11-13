<?php

require_once("_db.php");
require_once("_html.php");

function chart($name, $keys_name, $values_name, $values)
{
	if (isset($name))
		echo '<span style="font-size: 36px;">' . $name . '</span>';

	$max_bar_width = 1000;
	echo '<table><tr><th style="text-align: right;">' . $keys_name . "</th>";
	echo '<th style="text-align: left; width: ' . $max_bar_width . 'px;">' . $values_name . "</th></tr>";

	$max_value = max(array_values($values));
	foreach ($values as $bar_name => $bar_value)
	{
		echo "<tr><td>" . $bar_name . "</td><td>";
		$width_percent = 100 * $bar_value / $max_value;
		echo '<div style="text-align: right; color: black; background-color: white; width: ' . $width_percent . '%;">';
		echo $bar_value . "</div></td></tr>";
	}
	echo "</table><br>";
}

try
{
	$result = $db->x_query("SELECT username, COUNT(*) AS count FROM users, posts WHERE users.id = posts.user_id GROUP BY username ORDER BY count DESC");
	$uploads_per_user_data = array();
	while ($row = $result->fetch_row())
		$uploads_per_user_data[$row[0]] = $row[1];

	$result = $db->x_query("SELECT rating, COUNT(*) AS count FROM posts GROUP BY rating ORDER BY count DESC");
	$rating_distribution_data = array();
	while ($row = $result->fetch_row())
		$rating_distribution_data[$row[0]] = $row[1];

	html_begin("Stats");
	html_body();

	chart("Uploads per user", "User", "Uploads", $uploads_per_user_data);
	chart("Post count per rating", "Rating", "Post count", $rating_distribution_data);
	//TODO Chart for most used tags
	//TODO Chart for favorites?

	html_end();
}
catch (Exception $ex) { html_error("Stats", 500, $ex->getMessage()); }

?>
