<?php

require_once("_config.php");

class BooruDB extends mysqli
{
	public function __construct()
	{
		global $mysql_host, $mysql_username, $mysql_password;
		parent::__construct($mysql_host, $mysql_username, $mysql_password, "booru");
	}

	public function x_prepare($query)
	{
		$stmt = $this->prepare($query);
		if ($stmt === FALSE)
			throw new Exception("Couldn't prepare query");
		else return $stmt;
	}

	public function x_check_bind_param($bp_result)
	{
		if ($bp_result === FALSE)
			throw new Exception("Couldn't bind params");
	}

	public function x_execute($stmt, $get_result)
	{
		if ($stmt->execute() === FALSE)
			throw new Exception("Couldn't execute prepared statement");
		if ($get_result)
		{
			$result = $stmt->get_result();
			if ($result === FALSE)
				throw new Exception("Couldn't get result");
			else return $result;
		}
	}

	public function x_query($query)
	{
		$result = $this->query($query);
		if ($result === FALSE)
			throw new Exception("Couldn't execute query");
		else return $result;
	}

	public function x_scalar($result) { return $result->fetch_row()[0]; }

	public function x_found_rows()
	{
		$result = $this->x_query("SELECT FOUND_ROWS()");
		return (int)$this->x_scalar($result);
	}

	public function booru_get_user_by_id($id)
	{
		$stmt = $this->x_prepare("SELECT * FROM users WHERE id = ?");
		$this->x_check_bind_param($stmt->bind_param("i", $id));
		$result = $this->x_execute($stmt, true);
		if ($result->num_rows == 1)
			return $result->fetch_assoc();
		else throw new Exception("User not found");
	}

	public function booru_get_user_by_username($username)
	{
		$stmt = $this->x_prepare("SELECT * FROM users WHERE username = ?");
		$this->x_check_bind_param($stmt->bind_param("s", $username));
		$result = $this->x_execute($stmt, true);
		if ($result->num_rows == 1)
			return $result->fetch_assoc();
		else throw new Exception("User not found");
	}

	public function booru_get_tag_id($tag, $insert)
	{
		// Look for an alias
		$stmt = $this->x_prepare("SELECT tags.id FROM aliases INNER JOIN tags on aliases.tag_id = tags.id WHERE alias = ?");
		$this->x_check_bind_param($stmt->bind_param("s", $tag));
		$result = $this->x_execute($stmt, true);
		if ($result->num_rows == 0)
		{
			// Look for a tag
			$stmt = $this->x_prepare("SELECT id FROM tags WHERE tag = ?");
			$this->x_check_bind_param($stmt->bind_param("s", $tag));
			$result = $this->x_execute($stmt, true);
			if ($result->num_rows == 0)
			{
				if ($insert)
				{
					// Insert a new tag
					$stmt = $this->x_prepare("INSERT INTO tags (tag) VALUES (?)");
					$this->x_check_bind_param($stmt->bind_param("s", $tag));
					$result = $this->x_execute($stmt, false);
					return $this->insert_id;
				}
				else throw new Exception("Tag or Alias not found");
			}
			else return $this->x_scalar($result);
		}
		else return $this->x_scalar($result);
	}

	public function booru_add_tags_to_post($post_id, $tags)
	{
		$tag_id = 0;
		$stmt = $this->x_prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
		$this->x_check_bind_param($stmt->bind_param("ii", $post_id, $tag_id));
		foreach ($tags as $tag)
		{
			$tag_id = $this->booru_get_tag_id($tag, true);
			$this->x_execute($stmt, false);
		}
	}

	public function booru_add_post($user_id, $private, $source, $info, $rating, $width, $height, $mime, $hash)
	{
		$query = "INSERT INTO posts (user_id, private, source, info, rating, width, height, created, mime, hash)";
		$query .= " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->x_prepare($query);
		$private_int = $private ? 1 : 0; //must be saved as a variable, without bind_param complains
		$this->x_check_bind_param($stmt->bind_param("iissiiiiss", $user_id, $private_int, $source, $info, $rating, $width, $height, time(), $mime, $hash));
		$this->x_execute($stmt, false);
		return $this->insert_id;
	}

	public function booru_get_post_count()
	{
		$result = $this->x_query("SELECT COUNT(*) FROM posts");
		return (int)$this->x_scalar($result);
	}

	public function booru_posts_have_tag($post_ids, $tag)
	{
		$post_id = 0;
		$result_array = array();
		$stmt = $this->x_prepare("SELECT 1 FROM post_tags WHERE post_id = ? AND tag_id = (SELECT id FROM tags WHERE tag = ?)");
		$this->x_check_bind_param($stmt->bind_param("is", $post_id, $tag));
		foreach ($post_ids as $_post_id)
		{
			$post_id = $_post_id;
			$result = $this->x_execute($stmt, true);
			$result_array[$post_id] = $result->num_rows > 0;
		}
		return $result_array;
	}
	public function booru_post_has_tag($post_id, $tag) { return $this->booru_posts_have_tag(array($post_id), $tag)[$post_id]; }

	public function booru_posts_are_favorites($post_ids, $user_id)
	{
		$post_id = 0;
		$result_array = array();
		$stmt = $this->x_prepare("SELECT 1 FROM favorites WHERE post_id = ? AND user_id = ?");
		$this->x_check_bind_param($stmt->bind_param("ii", $post_id, $user_id));
		foreach ($post_ids as $_post_id)
		{
			$post_id = $_post_id;
			$result = $this->x_execute($stmt, true);
			$result_array[$post_id] = $result->num_rows == 1;
		}
		return $result_array;
	}
	public function booru_post_is_favorite($post_id, $user_id) { return $this->booru_posts_are_favorites(array($post_id), $user_id)[$post_id]; }

	public function booru_post_with_source_exists($source)
	{
		$stmt = $this->x_prepare("SELECT id FROM posts WHERE source = ?");
		$this->x_check_bind_param($stmt->bind_param("s", $source));
		$result = $this->x_execute($stmt, true);
		return $result->num_rows > 0 ? (int)$this->x_scalar($result) : NULL;
	}

	public function booru_post_with_hash_exists($hash)
	{
		$stmt = $this->x_prepare("SELECT id FROM posts WHERE hash = ?");
		$this->x_check_bind_param($stmt->bind_param("s", $hash));
		$result = $this->x_execute($stmt, true);
		return $result->num_rows > 0 ? (int)$this->x_scalar($result) : NULL;
	}

	public function booru_user_toggle_favorite($user_id, $post_id, $set_favorite)
	{
		$stmt = $this->x_prepare("DELETE FROM favorites WHERE user_id = ? AND post_id = ?");
		$this->x_check_bind_param($stmt->bind_param("ii", $user_id, $post_id));
		$this->x_execute($stmt, false);
		if ($set_favorite)
		{
			$stmt = $this->x_prepare("INSERT INTO favorites (user_id, post_id) VALUES (?, ?)");
			$this->x_check_bind_param($stmt->bind_param("ii", $user_id, $post_id));
			$this->x_execute($stmt, false);
		}
	}

	public function booru_post_update_image_info($post_id, $width, $height, $mime, $hash)
	{
		$stmt = $this->x_prepare("UPDATE posts SET width = ?, height = ?, mime = ?, hash = ? WHERE id = ?");
		$this->x_check_bind_param($stmt->bind_param("iissi", $width, $height, $mime, $hash, $post_id));
		$this->x_execute($stmt, false);
	}
}

$db = new BooruDB();

?>
