<?php

require_once("config.php");

class BooruDB extends mysqli
{
	public function __construct()
	{
		global $mysql_host, $mysql_username, $mysql_password;
		parent::__construct($mysql_host, $mysql_username, $mysql_password, "booru");
	}

	public function booru_login($username, $password)
	{
		$stmt = $this->prepare("SELECT id, pw_hash FROM users WHERE username = ?");
		$stmt->bind_param("s", $username);
		if ($stmt->execute())
		{
			$result = $stmt->get_result();
			if ($result)
				if ($result->num_rows == 1)
				{
					$row = $result->fetch_row();
					if (password_verify($password, $row[1]))
						return $row[0];
				}
		}
		return -1;
	}
}

$db = new BooruDB();

?>
