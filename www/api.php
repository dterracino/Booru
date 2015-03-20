<?php

echo '<?xml version="1.0" ?>' . "\n";
echo "<Response>\n";

require_once("db.php");
require_once("config.php");
require_once("upload_engine.php");

function api_result_noerror() { echo "\t<Error></Error>\n"; }
function api_result_error($error_msg) { echo "\t<Error>" . $error_msg . "</Error>\n"; }

$body = file_get_contents("php://input");

try
{
	$xml = new SimpleXMLElement($body);

	$user_id = NULL;
	$user_name = NULL;
	$user_perms = array();

	if (isset($xml->Login->Username) && isset($xml->Login->Password))
	{
		$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
		$stmt->bind_param("s", $xml->Login->Username);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows == 1)
		{
			$user = $result->fetch_assoc();
			if (password_verify($xml->Login->Password, $user["pw_hash"]))
			{
				$user_loggedin = true;
				$user_id = $user["id"];
				$user_name = $user["username"];
				foreach ($user as $key => $value)
					if (substr($key, 0, 2) == "p_")
						if ($value > 0)
							$user_perms[] = $key;
			}
			else throw new Exception("Authentication failed");
		}
		else throw new Exception("Authentication failed");
	}
	else throw new Exception("Login credentials not provided");

	switch ($xml->Type)
	{
		default: throw new Exception("Unknown request type");

		case "Test":
			echo "<Error></Error>";
			break;

		case "Upload":
			if (in_array("p_upload", $user_perms))
			{
				$image_data = base64_decode($xml->Image, true);
				$private = $xml->Post->Private > 0;
				$source = (string)$xml->Post->Source;
				$info = (string)$xml->Post->Info;
				$rating = (int)$xml->Post->Rating;
				$tags = array();
				foreach ($xml->Post->Tags->children() as $tag)
					$tags[] = (string)$tag;
				$result = upload_engine($image_data, $user_id, $private, $source, $info, $rating, $tags);
				if (is_numeric($result))
				{
					api_result_noerror();
					echo "\t<ID>" . $result . "</ID>\n";
				}
				else throw new Exception($result);
			}
			else throw new Exception("No upload permission");
			break;

		case "Delete":
			if (in_array("p_delete", $user_perms))
			{
				$post_id = (int)$xml->ID;
				$result = $db->query("SELECT user_id, private, mime FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row["private"] == 0 || $row["user_id"] == $user_id || in_array("p_admin", $user_perms))
					{
						$mime = $row["mime"];
						$db->query("DELETE FROM post_tags WHERE post_id = " . $post_id);
						$db->query("DELETE FROM posts WHERE id = " . $post_id);
						unlink($image_dir . "image" . $post_id . $mime_types[$mime]);
						unlink($thumb_dir . "thumb" . $post_id . ".jpg");
						api_result_noerror();
					}
					else throw new Exception("Access denied");
				}
				else throw new Exception("Post not found");
			}
			else throw new Exception("No delete permission");
			break;

		case "TagExists":
			{
				$tag = (string)$xml->Tag;
				$stmt = $db->prepare("SELECT COUNT(*) FROM tags WHERE tag = ?");
				$stmt->bind_param("s", $tag);
				$stmt->execute();
				$result = $stmt->get_result();
				api_result_noerror();
				if ($result->fetch_row()[0] == 1)
					echo "\t<Bool>1</Bool>\n";
				else echo "\t<Bool>0</Bool>\n";
			} break;

		case "Edit":
			if (in_array("p_edit", $user_perms))
			{
				$post_id = (int)$xml->ID;
				$result = $db->query("SELECT user_id, private FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row["private"] == 0 || $row["user_id"] == $user_id || in_array("p_admin", $user_perms))
					{
						if (isset($xml->Post->Source))
						{
							$nsource = (string)$xml->Post->Source;
							$stmt = $db->prepare("UPDATE posts SET source = ? WHERE id = ?");
							$stmt->bind_param("si", $nsource, $post_id);
							$stmt->execute();
						}
						if (isset($xml->Post->Info))
						{
							$ninfo = (string)$xml->Post->Info;
							$stmt = $db->prepare("UPDATE posts SET info = ? WHERE id = ?");
							$stmt->bind_param("si", $ninfo, $post_id);
							$stmt->execute();
						}
						if (isset($xml->Post->Rating))
						{
							$nrating = (int)$xml->Post->Rating;
							$stmt = $db->prepare("UPDATE posts SET rating = ? WHERE id = ?");
							$stmt->bind_param("ii", $nrating, $post_id);
							$stmt->execute();
						}
						if (isset($xml->Post->Private))
						{
							if ($row["user_id"] == $user_id || in_array("p_admin", $user_perms))
							{
								$nprivate = ($xml->Post->Private > 0) ? 1 : 0;
								$stmt = $db->prepare("UPDATE posts SET private = ? WHERE id = ?");
								$stmt->bind_param("ii", $nprivate, $post_id);
								$stmt->execute();
							}
							else throw new Exception("Only the owner can make the post private");
						}
						if (isset($xml->Post->Tags))
						{
						}
						api_result_noerror();
					}
					else throw new Exception("Access denied");
				}
				else throw new Exception("Post not found");
			}
			else throw new Exception("No edit permission");
			break;
	}
}
catch (Exception $ex) { api_result_error($ex->getMessage()); }

echo "</Response>";

?>
