<?php

echo '<?xml version="1.0" ?>' . "\n";
echo "<Response>\n";

require_once("_db.php");
require_once("_config.php");
require_once("_upload_engine.php");
require_once("_thumb_engine.php");

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
			api_result_noerror();
			echo "\t<Test />";
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
				$result = $db->x_query("SELECT user_id, private, mime FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row["private"] == 0 || $row["user_id"] == $user_id || in_array("p_admin", $user_perms))
					{
						$mime = $row["mime"];
						if ($db->begin_transaction())
							try
							{
								$db->x_query("DELETE FROM post_tags WHERE post_id = " . $post_id);
								$db->x_query("DELETE FROM favorites WHERE post_id = " . $post_id);
								$db->x_query("DELETE FROM posts WHERE id = " . $post_id);

								unlink($image_dir . "image" . $post_id . $mime_types[$mime]);
								unlink($thumb_dir . "thumb" . $post_id . ".jpg");

								if ($db->commit())
									api_result_noerror();
							}
							catch (Exception $ex)
							{
								if ($db->rollback())
									throw $ex;
								else throw new Exception("Couldn't rollback transaction\n" . $ex->getMessage());
							}
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
				$stmt = $db->x_prepare("SELECT COUNT(*) FROM tags WHERE tag = ?");
				$db->x_check_bind_param($stmt->bind_param("s", $tag));
				$result = $db->x_execute($stmt, true);
				api_result_noerror();
				if ($result->fetch_row()[0] == 1)
					echo "\t<Bool>1</Bool>\n";
				else echo "\t<Bool>0</Bool>\n";
			} break;

		case "GetImage":
			{
				$post_id = (int)$xml->ID;
				$result = $db->x_query("SELECT user_id, private, mime FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row["private"] == 0 || $row["user_id"] == $user_id || in_array("p_admin", $user_perms))
					{
						$image_file = $image_dir . "image" . $post_id . $mime_types[$row["mime"]];
						$image_data_b64 = base64_encode(file_get_contents($image_file));
						api_result_noerror();
						echo '\t<Image type="' . $row["mime"] . '">';
						echo $image_data_b64 . "</Image>";
					}
					else throw new Exception("Access denied");
				}
				else throw new Exception("Post not found");
			} break;

		case "SetImage":
			if (in_array("p_edit", $user_perms))
			{
				$post_id = (int)$xml->ID;
				$result = $db->x_query("SELECT user_id, private FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row["private"] == 0 || $row["user_id"] == $user_id || in_array("p_admin", $user_perms))
					{
						$image_data = base64_decode($xml->Image, true);
						$finfo = finfo_open();
						$mime = finfo_buffer($finfo, $image_data, FILEINFO_MIME_TYPE);
						finfo_close($finfo);
						if (!array_key_exists($mime, $mime_types))
							return "MIME Type not allowed";
						$size = getimagesizefromstring($image_data);
						$width = $size[0];
						$height = $size[1];
						$hash = substr(hash("sha256", $image_data), 0, 20);
						$image_file = $image_dir . "image" . $post_id . $mime_types[$mime];
//TODO Encapsulate in transaction
						file_put_contents($image_file, $image_data);
						$db->booru_post_update_size_hash($post_id, $width, $height, $hash);
						thumb_engine($post_id, $image_data, $mime, $width, $height);
						api_result_noerror();
					}
					else throw new Exception("Access denied");
				}
				else throw new Exception("Post not found");
			}
			else throw new Exception("No edit permission");
			break;

		case "Edit":
			if (in_array("p_edit", $user_perms))
			{
				$post_id = (int)$xml->ID;
				$result = $db->x_query("SELECT user_id, private FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_assoc();
					if ($row["private"] == 0 || $row["user_id"] == $user_id || in_array("p_admin", $user_perms))
					{
						if (isset($xml->Post->Source))
						{
							$nsource = (string)$xml->Post->Source;
							$stmt = $db->x_prepare("UPDATE posts SET source = ? WHERE id = ?");
							$db->x_check_bind_param($stmt->bind_param("si", $nsource, $post_id));
							$db->x_execute($stmt, false);
						}
						if (isset($xml->Post->Info))
						{
							$ninfo = (string)$xml->Post->Info;
							$stmt = $db->x_prepare("UPDATE posts SET info = ? WHERE id = ?");
							$db->x_check_bind_param($stmt->bind_param("si", $ninfo, $post_id));
							$db->x_execute($stmt, false);
						}
						if (isset($xml->Post->Rating))
						{
							$nrating = (int)$xml->Post->Rating;
							$stmt = $db->prepare("UPDATE posts SET rating = ? WHERE id = ?");
							$db->x_check_bind_param($stmt->bind_param("ii", $nrating, $post_id));
							$db->x_execute($stmt, false);
						}
						if (isset($xml->Post->Private))
						{
							if ($row["user_id"] == $user_id || in_array("p_admin", $user_perms))
							{
								$nprivate = ($xml->Post->Private > 0) ? 1 : 0;
								$stmt = $db->x_prepare("UPDATE posts SET private = ? WHERE id = ?");
								$db->x_check_bind_param($stmt->bind_param("ii", $nprivate, $post_id));
								$db->x_execute($stmt, false);
							}
							else throw new Exception("Only the owner can make the post private");
						}
						$tags_no_delta = isset($xml->Post->Tags);
						if (isset($xml->Post->TagsAdd) || $tags_no_delta)
						{
							$tag_ids = array();
							if ($tags_no_delta)
							{
								//TODO Delete and insert within a transaction
								$db->x_query("DELETE FROM post_tags WHERE post_id = " . $post_id);
								foreach ($xml->Post->Tags->children() as $xml_tag)
									$tag_ids[] = $db->booru_get_tag_id((string)$xml_tag, true);
							}
							else foreach ($xml->Post->TagsAdd->children() as $xml_tag)
								$tag_ids[] = $db->booru_get_tag_id((string)$xml_tag, true);
							$tag_id = 0;
							$stmt = $db->x_prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
							$db->x_check_bind_param($stmt->bind_param("ii", $post_id, $tag_id));
							foreach ($tag_ids as $_tag_id)
							{
								$tag_id = $_tag_id;
								$db->x_execute($stmt, false);
							}
						}
						if (isset($xml->Post->TagsRemove) && !$tags_no_delta)
						{
							$tag_ids = array();
							foreach ($xml->Post->TagsRemove->children() as $xml_tag)
							{
								$tag = (string)$xml_tag;
								$stmt = $db->x_prepare("SELECT id FROM tags WHERE tag = ?");
								$db->x_check_bind_param($stmt->bind_param("s", $tag));
								$result = $db->x_execute($stmt, true);
								if ($result->num_rows == 1)
									$tag_ids[] = $result->fetch_row()[0];
							}
							$tag_id = 0;
							$stmt = $db->x_prepare("DELETE FROM post_tags WHERE post_id = ? AND tag_id = ?");
							$db->x_check_bind_param($stmt->bind_param("ii", $post_id, $tag_id));
							foreach ($tag_ids as $_tag_id)
							{
								$tag_id = $_tag_id;
								$db->x_execute($stmt, false);
							}
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
