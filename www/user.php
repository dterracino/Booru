<?php

require_once("html.php");
require_once("helper.php");
require_once("config.php");

if (isset($_GET["id"]))
{
	$id = $_GET["id"];
	if (is_numeric($id))
	{
		$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows == 1)
		{
			$user = $result->fetch_assoc();
			html_header("Booru - " . $user["username"]);

			echo '<img alt="" src="avatar.php?id=' . $id . '"><br><br>';
			echo '<span style="font-size: 40px">' . $user["username"] . "</span>";
			if ($user["job"] != "")
				echo "<br>" . $user["job"];

			echo "<br><br><b>Permissions</b>";
			foreach ($user as $key => $value)
				if (substr($key, 0, 2) == "p_" && $value == 1)
					echo "<br>" . $key;

			echo "<br><br><b>Tools</b>";
			echo '<a href="posts.php?tags=user%3D' . $user["username"] . '">';
			echo "Search for posts uploaded by " . $user["username"] . "</a>";

			html_footer();
		}
		else
		{
			http_response_code(404);
			echo "User not found";
		}
	}
	else
	{
		http_response_code(400);
		echo "ID not numeric";
	}
}
else
{
	http_response_code(400);
	echo "ID not set";
}

?>
