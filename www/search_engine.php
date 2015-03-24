<?php

require_once("db.php");
require_once("config.php");
require_once("session.php");

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
	$sql = "id IN (SELECT post_id FROM post_tags WHERE tag_id = (SELECT id FROM tags WHERE tag = ?))";
	if ($term[0] == '_')
	{
		$sql = "NOT " . $sql;
		$arg = substr($term, 1);
	}
	else $arg = $term;
	return new SearchTerm($sql, "s", array($arg));
}

function parse_special_term($term)
{
	$s_var = $term[0];
	$s_op = $term[1];
	$s_val = substr($term, 2);

	$column_names = array(
		"u" => "", // u is handled differently
		"w" => "width",
		"h" => "height"
	);
	$operators = array("<", ">", "=");

	if (array_key_exists($s_var, $column_names))
	{
		if (in_array($s_op, $operators))
		{
			if ($s_var == "u")
			{
				if ($s_op == "=")
					return new SearchTerm("user_id = (SELECT id FROM users WHERE username = ?)", "s", $s_val);
				else return "Operator not allowed for usernames";
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

function search_engine($search_string)
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

	$query = "SELECT id FROM posts WHERE ";
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

	//TODO Implement offset and count
	// $query .= " LIMIT ?, ?";
	// $all_arg_types .= "ii";
	// $all_args[] = $offset;
	// $all_args[] = $count;

	$stmt = $db->prepare($query);

	$cufa_args = array();
	$cufa_args[] = & $all_arg_types;
	//Do not use foreach, it will not work
	for ($i = 0; $i < count($all_args); $i++)
		$cufa_args[] = & $all_args[$i];

	call_user_func_array(array($stmt, "bind_param"), $cufa_args);

	$stmt->execute();
	$result = $stmt->get_result();

	$ids = array();
	while ($row = $result->fetch_row())
		$ids[] = $row[0];

	return $ids;
}

?>
