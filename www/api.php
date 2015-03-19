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

	if (count($xml->Login->children()) == 2)
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
				$result = $db->query("SELECT mime FROM posts WHERE id = " . $post_id);
				if ($result->num_rows == 1)
				{
					$mime = $result->fetch_row()[0];
					$db->query("DELETE FROM post_tags WHERE post_id = " . $post_id);
					$db->query("DELETE FROM posts WHERE id = " . $post_id);
					unlink($image_dir . "image" . $post_id . $mime_types[$mime]);
					unlink($thumb_dir . "thumb" . $post_id . ".jpg");
					api_result_noerror();
				}
				else throw new Exception("Post not found");
			}
			else throw new Exception("No delete permission");
			break;
	}
}
catch (Exception $ex) { api_result_error($ex->getMessage()); }

echo "</Response>";

?>
