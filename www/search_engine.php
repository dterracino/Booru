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
		$this->args = $args;
	}

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

function search_engine($search_string)
{
	global $db, $max_search_terms;

	$uparts = explode(" ", $search_string);
	$nparts = array();

	foreach ($uparts as $upart)
	{
		$xpart = trim($upart);
		if ($xpart != "")
			$nparts[] = strtolower($xpart);
	}
	$nparts = array_unique($nparts);

	if (count($nparts) > $max_search_terms)
		return "Too much search terms (" . count($nparts) . " > " . $max_search_terms . ")";

	$terms = array();
	foreach ($nparts as $npart)
		$terms[] = parse_tag_term($npart);

	//TODO Implement special terms
	//TODO Implement complex boolean conjunctions
	//TODO Implement offset and count

	if (session_has_perm("admin"))
	{
		$query = "SELECT id FROM posts WHERE 1";
		$all_arg_types = "";
		$all_args = array();
	}
	else
	{
		$query = "SELECT id FROM posts WHERE (posts.private = 0 OR posts.user_id = ?)";
		$all_arg_types = "i";
		$all_args = array(session_user_id());
	}

	foreach ($terms as $term)
	{
		$query .= " AND " . $term->sql;
		$all_arg_types .= $term->arg_types;
		foreach ($term->args as $arg)
			$all_args[] = $arg;
	}

	$query .= " ORDER BY created DESC";

//	$query .= " LIMIT ?, ?";
//	$all_arg_types .= "ii";
//	$all_args[] = $offset;
//	$all_args[] = $count;

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
