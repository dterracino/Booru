<?xml version="1.0">
<Response>
<?php

require_once("db.php");
require_once("upload_engine.php");

function api_result_error($error_msg)
{
	echo '<?xml version="1.0"><Response><Error>' . $error_msg;
	echo "</Error></Response>";
}

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
	else throw new Exception("Credentials not provided");

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
					echo "<Error></Error>";
					echo "<ID>" . $result . "</ID>";
				}
				else throw new Exception($result);
			}
			else throw new Exception("No upload permission");
			break;
	}	
}
catch (Exception $ex) { echo "<Error>" . $ex->getMessage() . "</Error>"; }

?>
</Response>
