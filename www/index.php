<?php

require_once("db.php");
require_once("html.php");

$result = $db->query("SELECT COUNT(*) FROM posts");
$counter = (string)$result->fetch_row()[0];
$counter_len = strlen($counter);

html_header("Booru");

$div_width = 68 * ($counter_len + 1);
echo '<div style="width: ' . $div_width . 'px; margin-left: auto; margin-right: auto; margin-top: 180px; text-align: center;">';

for ($i = 0; $i < $counter_len; $i++)
	echo '<img alt="' . $counter[$i] . '" src="res/counter/' . $counter[$i] . '.png">';

echo "<br><br>";

echo '<form action="search.php" method="GET">';
echo '<input style="width: 100%;" class="tagbox" type="text" name="tags">';
echo "</form>";

echo '<span style="font-size: 10px;">';
echo "Running Booru by teamalpha5441";
echo "</span>";

echo "</div>";

html_footer();

?>
