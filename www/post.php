<?php

require_once("db.php");
require_once("html.php");
require_once("config.php");

if (isset($_GET["tags"]))
	$tag_search = $_GET["tags"];
else $tag_search = "";

$id = $_GET["id"];
if (!isset($id))
{
	http_response_code(400);
	echo "ID not set";
}
else if (!is_numeric($id))
{
	http_response_code(400);
	echo "ID is not numeric";
}
else
{
	$stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();

	$result = $stmt->get_result();
	$post = $result->fetch_assoc();

	$query = "SELECT tag, color FROM tags INNER JOIN tag_types ON tags.type_id = tag_types.id WHERE tags.id IN ";
	$query .= "(SELECT DISTINCT tag_id FROM post_tags WHERE post_id = ?) ORDER BY type_id DESC, tag ASC");
	$stmt = $db->prepare($query);
	$stmt->bind_param("i", $id);
	$stmt->execute();

	$result = $stmt->get_result();
	$tags = array();
	while ($row = $result->fetch_row())
		$tags[$row[0]] = $row[1];

	html_header("Booru - Post " . $id);

	table_header(NULL);
	nav_searchbox($tag_search);

	echo "<br>";
	subsection_header("Tags");

	$contains_esoa = false;
	foreach ($tags as $tag => $color)
		if ($tag == "esoa")
		{
			$contains_esoa = true;
			break;
		}
	if ($contains_esoa)
	{
		echo '<div style="margin:26px auto 10px auto;width:100px;">';
		echo '<a href="posts.php?tags=esoa">';
		echo '<img alt="APPROVED" src="res/seal.png" width="100px" height="100px">';
		echo "</a></div>";
	}

	echo '<ul class="tags">';
	foreach ($tags as $tag => $color)
	{
		echo '<li><span style="color:' . $color . '">';
		echo '<a href="posts.php?tags=' . urlencode($tag) . '">';
		echo htmlspecialchars($tag) . "</a></span></li>";
	}
	echo "</ul>";
	subsection_footer();
	subsection_header("User");
	echo '<a href="posts.php?tags=user%3D' . $post["user"] . '">';
	echo htmlentities($post["user"]);
	if ($post["private"] != 0)
		echo "</a> <i>(private)</i>";
	else echo "</a> <i>(public)</i>";
	subsection_footer();
	$source = htmlentities($post["source"]);
	if (filter_var($post["source"], FILTER_VALIDATE_URL))
		$source = '<a href="' . $source . '">' . $source . "</a>";
	subsection("Source", $source);
	if (!empty($post["info"]))
	{
		$info = htmlentities($post["info"]);
		subsection("Info", $post->info);
	}
	subsection("Rating", $post["rating"]);
	subsection("Size", $post["width"] . "x" . $post["height"]);
	$cdate = date("d.m.Y H:i", $post["created"]);
	subsection("Date", $cdate);

	subsection_header("IQDB");
	echo '<a href="http://iqdb.org/?url=';
	echo urlencode($server_base_url . "/image.php?id=" . $id);
	echo '">Search with thumbnail</a>';
	echo '<br><a href="http://iqdb.org/?url=';
	echo urlencode($server_base_url . "/image.php?type=image&id=" . $id);
	echo '">Search with image</a>';
	subsection_footer();

	table_middle();
	echo '<img id="mimg" class="mimg" alt="Image" src="image.php?type=image&amp;id=' . $id . '">';
	table_footer();
	html_footer();
}

?>
