<?php

require_once("_db.php");
require_once("_html.php");

try
{
	$counter = (string)$db->booru_get_post_count();
	$counter_len = strlen($counter);

	html_begin_no_nav("Booru");

	$div_width = 68 * ($counter_len + 1);
	echo '<div style="width: ' . $div_width . 'px; margin-left: auto; margin-right: auto; margin-top: 180px; text-align: center;">';

	for ($i = 0; $i < $counter_len; $i++)
		echo '<img alt="' . $counter[$i] . '" src="res/counter/' . $counter[$i] . '.png">';

	echo "<br><br>";

	echo '<form action="posts.php" method="GET">';
	echo '<input style="width: 100%;" class="tagbox" type="text" name="tags">';
	echo "</form>";

	echo '<span style="font-size: 10px;">';
	echo "Running Booru by teamalpha5441";
	echo "</span>";

	echo "</div>";

	html_end();
}
catch (Exception $ex) { html_error("Booru", 500, $ex->getMessage()); }

?>
