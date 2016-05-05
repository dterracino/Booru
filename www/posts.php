<?php

require_once("_db.php");
require_once("_html.php");
require_once("_config.php");
require_once("_search_engine.php");

$tags = "";
if (!empty($_GET["tags"]))
{
	$tags = $_GET["tags"];
	html_begin($tags);
}
else html_begin("Search", "");

html_nav_element_begin("Search Help");
echo "Tag term - <i>panties</i><br>";
echo "Special term - <i>:u=eggy</i><br>";
html_nav_element_end();

html_body();

if (isset($_GET["page"]))
{
	$page = $_GET["page"] - 1;
	if (!is_numeric($page))
		$page = 0;
	if ($page < 0)
		$page = 0;
}
else $page = 0;

$search_result = search_engine($tags, $page * $thumbs_per_page, $thumbs_per_page);
if (!is_string($search_result))
{
	$total_pages = floor(($search_result->count_all - 1) / $thumbs_per_page) + 1;
	if ($search_result->count > 0)
	{
		if (!empty($tags))
			$tags = "&amp;tags=" . $tags;

		// Image cards
		foreach ($search_result->ids as $id)
		{
			$post_info = $search_result->info[$id];

			// Card div and card body
			echo '<div class="card"><div class="card_body">';
			echo '<a href="post.php?id=' . $id . $tags . '">';
			echo '<img class="thumb" alt="#' . $id;
			echo '" src="image.php?id=' . $id . '"></a></div>';

			// Card size
			echo '<div class="card_size">';
			echo $post_info["width"] . "x" . $post_info["height"];
			echo "</div>";

			// Card icons
			echo '<div class="card_icons">';
			if (session_loggedin())
			{
				if ($post_info["user_id"] == session_user_id())
					echo '<img class="icon" title="Post Owner" alt="O" src="res/icon_crown.png">';
				if ($post_info["private"] > 0)
					echo '<img class="icon" title="Private" alt="PRIV" src="res/icon_lock.png">';
				if ($post_info["favorite"] > 0)
					echo '<img class="icon" title="Favorite" alt="FAV" src="res/icon_heart.png">';
			}
			if ($post_info["esoa"] > 0)
				echo '<img class="icon" title="Eggy Seal of Approval" alt="ESOA" src="res/icon_tick.png">';
			if ($post_info["animated"] > 0)
				echo '<img class="icon" title="Animated" alt="ANI" src="res/icon_video.png">';
			echo "</div></div>"; // also close card div
		}

		// Page chooser
		echo '<div class="page_chooser">';
		if ($total_pages > 0)
			if ($page > 0)
			{
				echo '<span class="pc_page"><span class="pc_arrow">';
				echo '<a href="?page=' . $page . $tags . '">&#x21ab;</a></span></span>';
			}
		for ($i = 0; $i < $total_pages; $i++)
		{
			echo '<span class="pc_page">';
			if ($i != $page)
				echo '<a href="?page=' . ($i + 1) . $tags . '">' . ($i + 1) . "</a>";
			else echo '<span class="pc_selected">' . ($i + 1) . "</span>";
			echo "</span>";
		}
		if ($total_pages > 0)
			if ($page < $total_pages - 1)
			{
				echo '<span class="pc_page"><span class="pc_arrow">';
				echo '<a href="?page=' . ($page + 2) . $tags . '">&#x21ac;</a></span></span>';
			}
		echo "</div>";
	}
	else echo "Nobody here but us chickens! - Why chickens?!";
}
else echo "Search error: " . $search_result;

html_end();

?>
