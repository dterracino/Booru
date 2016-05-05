<?php

require_once("_db.php");
require_once("_html.php");
require_once("_config.php");
require_once("_helper.php");
require_once("_session.php");

if (isset($_GET["tags"]))
	$tag_search = $_GET["tags"];
else $tag_search = "";

if (!isset($_GET["id"]))
	html_error("Post", 400, "ID not set");
else if (!is_numeric($_GET["id"]))
	html_error("Post", 400, "ID not numeric");
else
{
	$id = $_GET["id"];
	$query = "SELECT posts.*, users.username AS user FROM posts INNER JOIN users";
	$query .= " ON posts.user_id = users.id WHERE posts.id = ?";
	$stmt = $db->x_prepare($query);
	$db->x_check_bind_param($stmt->bind_param("i", $id));
	$result = $db->x_execute($stmt, true);

	if ($result->num_rows == 1)
	{
		$post = $result->fetch_assoc();
		if ($post["private"] == 0 || $post["user_id"] == session_user_id() || session_has_perm("admin"))
		{
			$query = "SELECT tag, color FROM tags INNER JOIN tag_types ON tags.type_id = tag_types.id WHERE tags.id IN";
			$query .= " (SELECT DISTINCT tag_id FROM post_tags WHERE post_id = ?) ORDER BY type_id DESC, tag ASC";
			$stmt = $db->x_prepare($query);
			$db->x_check_bind_param($stmt->bind_param("i", $id));
			$result = $db->x_execute($stmt, true);
			$tags = array();
			while ($row = $result->fetch_row())
				$tags[$row[0]] = $row[1];

			html_begin("Post " . $id);

			html_nav_element_begin("Tags");
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
			html_nav_element_end();

			if (session_loggedin())
			{
				html_nav_element_begin("Favorite");
				$heart = "&#x2764;";
				$is_favorite = $db->booru_post_is_favorite($id, session_user_id());
				$color = $is_favorite ? "red" : "gray"; // change also color values in script.js
				echo '<a id="favheart" href="javascript:void(0)" style="color:' . $color . ';font-size:32px;"';
				echo ' onclick="toggle_favorite(this, ' . $id . ');">';
				echo $heart . "</a>";
				html_nav_element_end();
			}

			html_nav_element_begin("User");
			echo '<a href="user.php?id=' . $post["user_id"] . '">';
			echo '<img alt="" src="avatar.php?id=' . $post["user_id"] . '">';
			echo "<br>" . $post["user"];
			if ($post["private"] != 0)
				echo "</a> <i>(private)</i>";
			else echo "</a> <i>(public)</i>";
			html_nav_element_end();

			html_nav_element_begin("Source");
			$source = htmlentities($post["source"]);
			if (filter_var($post["source"], FILTER_VALIDATE_URL))
				echo '<a href="' . $source . '">' . $source . "</a>";
			else echo $source;
			html_nav_element_end();

			if (!empty($post["info"]))
			{
				html_nav_element_begin("Info");
				echo htmlentities($post["info"]);
				html_nav_element_end();
			}

			html_nav_element_begin("Rating");
			echo $post["rating"];
			html_nav_element_end();

			html_nav_element_begin("Size");
			echo $post["width"] . "x" . $post["height"];
			html_nav_element_end();

			html_nav_element_begin("Upload Date");
			date_default_timezone_set('UTC');
			echo date("d.m.Y H:i:s", $post["created"]) . " UTC";
			html_nav_element_end();

			html_nav_element_begin("Hash");
			echo $post["hash"];
			html_nav_element_end();

			html_nav_element_begin("IQDB");
			echo '<a href="https://iqdb.org/?url=';
			echo urlencode($server_base_url . "/image.php?id=" . $id);
			echo '">Search with thumbnail</a>';
			echo '<br><a href="https://iqdb.org/?url=';
			echo urlencode($server_base_url . "/image.php?type=image&id=" . $id);
			echo '">Search with image</a>';
			html_nav_element_end();

			html_body();

			embed_image($id, $post["mime"], $post["width"], $post["height"]);
		}
		else html_error("Post", 403, "Access denied");
	}
	else html_error("Post", 404, "Post not found");
}

?>
