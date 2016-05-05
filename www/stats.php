<?php

require_once("_db.php");
require_once("_html.php");

function draw_bars($name, $keys_name, $values_name, $values)
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
		if ($width_percent > 50)
		{
			echo '<div style="text-align: right; color: black; background-color: white; width: ' . $width_percent . '%;">';
			echo $bar_value . " </div></td></tr>";
		}
		else
		{
			echo '<div style="float: left;background-color: white; width: ' . $width_percent . '%;">';
			echo "|</div> " . $bar_value . "</td></tr>";
		}
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

	$tags = array("todo_ugoira", "todo_info", "esoa", "todo_source", "potential", "todo_tags", "todo_image", "todo_character", "todo_copyright", "todo_artist");
	sort($tags);
	$t5_tags_distribution_data = array();
	$stmt = $db->x_prepare("SELECT COUNT(DISTINCT post_id) FROM post_tags WHERE tag_id = (SELECT id FROM tags WHERE tag = ?)");
	$tag = NULL;
	$db->x_check_bind_param($stmt->bind_param("s", $tag));
	for ($i = 0; $i < count($tags); $i++)
	{
		$tag = $tags[$i];
		$t5_tags_distribution_data[$tag] = $db->x_scalar($db->x_execute($stmt, true));
	}

	html_begin("Stats");
	html_body();

	draw_bars("Uploads per user", "User", "Uploads", $uploads_per_user_data);
	draw_bars("Post count per rating", "Rating", "Post count", $rating_distribution_data);
	draw_bars("Post count of type 5 tags", "Tag", "Post count", $t5_tags_distribution_data);
	//TODO Chart for most used tags
	//TODO Chart for favorites?

	html_end();
}
catch (Exception $ex) { html_error("Stats", 500, $ex->getMessage()); }

?>
