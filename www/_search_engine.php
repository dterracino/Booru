<?php

require_once("_db.php");
require_once("_config.php");
require_once("_session.php");

class SearchResult
{
	public function __construct()
	{
		$ids = array();
		$posts = array();
	}

	public $ids; //array of post IDs
	public $count; //count($ids)
	public $count_all; //total number of posts
	// assoc array where the key is the post ID
	// contains assoc arrays describing the posts
	// Keys: id, private, witdt, height, esoa, animated
	public $info;
}

class SearchTerm
{
	public function __construct($sql, $arg_types, $args)
	{
		$this->sql = $sql;
		$this->arg_types = $arg_types;
		if (is_array($args))
			$this->args = $args;
		else $this->args = array($args);
	}

	public $negate;
	public $sql;
	public $arg_types;
	public $args;
}

function parse_tag_term($term)
{
	global $db;
	$query = "id IN (SELECT post_id FROM post_tags WHERE tag_id = ?)";
	if ($term[0] == '_')
	{
		$query = "NOT " . $query;
		$arg = substr($term, 1);
	}
	else $arg = $term;
	try
	{
		$tag_id = $db->booru_get_tag_id($arg, false);
		return new SearchTerm($query, "i", array($tag_id));
	}
	catch (Exception $ex) { return $ex->getMessage(); }
}

function parse_special_term($term)
{
	$s_var = $term[0];
	$s_op = $term[1];
	$s_val = substr($term, 2);

	$column_names = array(
		// u is handled differently (user)
		"u" => "",
		// f is handled differently (favorite of user)
		"f" => "",
		// a is handled differently (aspect ration)
		"a" => "",
		"w" => "width",
		"h" => "height",
		"i" => "id",
		"r" => "rating",
		"p" => "private"
	);
	$operators = array("<", ">", "=");

	if (array_key_exists($s_var, $column_names))
	{
		if (in_array($s_op, $operators))
		{
			if ($s_var == "u" || $s_var == "f")
			{
				if ($s_op == "=")
				{
					if ($s_val == "me")
					{
						if (session_loggedin())
						{
							if ($s_var == "u")
								return new SearchTerm("user_id = ?", "i", session_user_id());
							else return new SearchTerm("id IN (SELECT post_id FROM favorites WHERE user_id = ?)", "i", session_user_id());
						}
						else return "You must be logged in to use the username placeholder";
					}
					else
					{
						if ($s_var == "u")
							return new SearchTerm("user_id = (SELECT id FROM users WHERE username = ?)", "s", $s_val);
						else return new SearchTerm("id IN (SELECT post_id FROM favorites WHERE user_id = (SELECT id FROM users WHERE username = ?))", "s", $s_val);
					}
				}
				else return "Operator not allowed for this search";
			}
			else if ($s_var == "a")
			{
				$query = "width / height " . $s_op . " ?";
				return new SearchTerm($query, "d", $s_val);
			}
			else
			{
				$query = $column_names[$s_var] . " " . $s_op . " ?";
				return new SearchTerm($query, "i", $s_val);
			}
		}
		else return "Operator unknown";
	}
	else return "Special search variable unknown";
}

function search_engine($search_string, $offset, $count)
{
	global $db, $max_search_terms;

	$uparts = explode(" ", $search_string);
	$nparts = array();

	foreach ($uparts as $upart)
	{
		$upart = trim($upart);
		if ($upart != "")
			$nparts[] = strtolower($upart);
	}
	$nparts = array_unique($nparts);

	if (count($nparts) > $max_search_terms)
		return "Too much search terms (" . count($nparts) . " > " . $max_search_terms . ")";

	$terms = array();
	foreach ($nparts as $npart)
	{
		if ($npart[0] == "!")
		{
			if (strlen($npart) < 2)
				return "Tried to invert null term";
			$negate = true;
			$npart = substr($npart, 1);
		}
		else $negate = false;

		if ($npart[0] == ":")
		{
			if (strlen($npart) < 4)
				return "Parse error on special term";
			$npart = substr($npart, 1);
			$term = parse_special_term($npart);
		}
		else $term = parse_tag_term($npart);

		if (is_string($term))
			return $term;

		$term->negate = $negate;
		$terms[] = $term;
	}

	$query = "SELECT SQL_CALC_FOUND_ROWS id, user_id, private, width, height FROM posts WHERE ";
	if (!session_loggedin())
		$query .= "posts.private = 0";
	else if (session_has_perm("admin"))
		$query .= "1";
	else $query .= "(posts.private = 0 OR posts.user_id = " . session_user_id() .  ")";

	$all_arg_types = "";
	$all_args = array();
	foreach ($terms as $term)
	{
		if ($term->negate)
			$query .= " AND NOT " . $term->sql;
		else $query .= " AND " . $term->sql;
		$all_arg_types .= $term->arg_types;
		foreach ($term->args as $arg)
			$all_args[] = $arg;
	}

	$query .= " ORDER BY created DESC";

	$query .= " LIMIT ? OFFSET ?";
	$all_arg_types .= "ii";
	$all_args[] = $count;
	$all_args[] = $offset;

	try
	{
		$stmt = $db->prepare($query);

		// Prepare the dynamic call to bind_param
		$cufa_args = array();
		$cufa_args[] = & $all_arg_types;
		//Do not use foreach, it will not work
		for ($i = 0; $i < count($all_args); $i++)
			$cufa_args[] = & $all_args[$i];
		if (call_user_func_array(array($stmt, "bind_param"), $cufa_args) === FALSE)
			throw new Exception("Couldn't bind params");

		$result = $db->x_execute($stmt, true);

		$search_result = new SearchResult();
		$search_result->count = $result->num_rows;
		while ($row = $result->fetch_assoc())
		{
			$id = $row["id"];
			$search_result->ids[] = $id;
			$search_result->info[$id] = $row;
			$search_result->info[$id]["favorite"] = 0;
		}
		$search_result->count_all = $db->x_found_rows();

		$esoa_result = $db->booru_posts_have_tag($search_result->ids, "esoa");
		$animated_result = $db->booru_posts_have_tag($search_result->ids, "animated");
		foreach ($search_result->ids as $post_id)
		{
			$search_result->info[$post_id]["esoa"] = $esoa_result[$post_id] ? 1 : 0;
			$search_result->info[$post_id]["animated"] = $animated_result[$post_id] ? 1 : 0;
		}

		if (session_loggedin())
		{
			$fav_result = $db->booru_posts_are_favorites($search_result->ids, session_user_id());
			foreach ($search_result->ids as $post_id)
				if ($fav_result[$post_id])
					$search_result->info[$post_id]["favorite"] = 1;
		}

		return $search_result;
	}
	catch (Exception $ex) { return $ex->getMessage(); }
}

?>
