<?php

require_once("db.php");
require_once("html.php");
require_once("config.php");
require_once("search_engine.php");

if (!empty($_GET["tags"]))
{
	$tag_search = $_GET["tags"];
	html_header("Booru - " . $tag_search);
}
else
{
	$tag_search = "";
	html_header("Booru");
}

table_header(NULL);
nav_searchbox($tag_search);
/*
echo "<br>";
subsection_header("Search Help");
echo "Single tag:<br><i>panties</i><br><br>";
echo "More tags:<br><i>panties red_hair</i><br><br>";
echo "Private posts:<br><i>private=true</i><br><br>";
echo "Eggy's images:<br><i>user=eggy</i><br><br>";
echo "<br>";
echo "Operators:<br><i>&lt;, &lt;=, =, &gt;=, &gt;, !=</i><br><br>";
echo "Fields:<br><i>";
echo "ID, User, Private,<br>";
echo "Width, Height,<br>";
echo "Rating, Score";
echo "</i><br><br>";
echo "<br>";
echo "All search strings can<br>be combined into one<br>big query, separated<br>by spaces";
subsection_footer();
*/
table_middle();

$post_ids = search_engine($tag_search);
if (is_string($post_ids))
	echo "Search error: " . $post_ids;
else
{
	$total_pages = floor((count($post_ids) - 1) / $thumbs_per_page) + 1;

	$page = 0;
	if (isset($_GET["page"]))
		$page = $_GET["page"] - 1;
	if (!is_numeric($page))
		$page = 0;
	if ($page > $total_pages - 1)
		$page = $total_pages - 1;
	if ($page < 0)
		$page = 0;

	$count = count($post_ids) - $page * $thumbs_per_page;
	if ($count > $thumbs_per_page)
		$count = $thumbs_per_page;

	$post_ids = array_slice($post_ids, $page * $thumbs_per_page, $count);

	if (count($post_ids) > 0)
	{
		echo '<div class="wrap">';
		if (!empty($tag_search))
			$tag_search = "&amp;tags=" . $tag_search;
		foreach ($post_ids as $id)
		{
			echo '<div class="thumb"><a href="post.php?id=' . $id . $tag_search . '">';
			echo '<img alt="#' . $id . '" src="image.php?id=' . $id . '"></a></div>';
		}
		echo '</div><br><div class="page_chooser">';
		if ($total_pages > 0)
			if ($page > 0)
			{
				echo '<span class="pc_page"><span class="pc_arrow">';
				echo '<a href="?page=' . $page . $tag_search . '">&#x21ab;</a></span></span>';
			}
		for ($i = 0; $i < $total_pages; $i++)
		{
			echo '<span class="pc_page">';
			if ($i != $page)
				echo '<a href="?page=' . ($i + 1) . $tag_search . '">' . ($i + 1) . "</a>";
			else echo '<span class="pc_selected">' . ($i + 1) . "</span>";
			echo "</span>";
		}
		if ($total_pages > 0)
			if ($page < $total_pages - 1)
			{
				echo '<span class="pc_page"><span class="pc_arrow">';
				echo '<a href="?page=' . ($page + 2) . $tag_search . '">&#x21ac;</a></span></span>';
			}
		echo "</div>";
	}
	else echo "Nobody here but us chickens! - Why chickens?!";
}

table_footer();
html_footer();

?>
