<?php

require_once("_config.php");
require_once("_session.php");

/*
	Step 1: Call html_begin, this method call will write header elements
	Step 2: Write the navigation elements with html_nav_element_begin and _end
	Step 3: Call html_body to close the navigation div and open the body div
	Step 4: Write the document body
	Step 4: Call html_end to close the document

	NoNav:  Call html_begin_no_nav and omit html_body for pages without nav
	Error:  Call html_error if you haven't called any html function before
*/

function html_error($title, $code, $msg)
{
        http_response_code($code);
        html_begin($title);
	html_nav_element_begin("Error");
	echo "Please try again or contant an user with p_admin";
	html_nav_element_end();
        html_body();
        echo $msg;
        html_end();
}

function html_begin($title) { html_begin_internal($title, true); }
function html_begin_no_nav($title) { html_begin_internal($title, false); }
function html_begin_internal($title, $nav)
{
	global $motd;

	if (!empty($_GET["tags"]))
		$tags = $_GET["tags"];
	else $tags = "";

	// HTML head
	echo "<!DOCTYPE html><html><head>";
	echo "<title>" . $title . "</title>";
	echo '<meta charset="UTF-8">';
	echo '<link rel="icon" type="image/icon" href="res/favicon.ico">';
	echo '<link rel="stylesheet" type="text/css" href="style_ll.css">';
	echo '<link rel="stylesheet" type="text/css" href="style.css">';
	echo '<script type="text/javascript" src="script.js"></script>';
	echo "</head><body>";

	// Header div
	echo '<div class="header">';
	echo '<img class="logo" alt="TODO" src="res/title.svg">';
	echo '<a href="index.php"><img class="link" alt="Index" src="res/house.svg"></a>';
	echo '<a href="posts.php"><img class="link" alt="Posts" src="res/tiles.svg"></a>';
	echo '<a href="specialpost.php?type=newest"><img class="link" alt="Newest" src="res/new.svg"></a>';
	echo '<a href="specialpost.php?type=random"><img class="link" alt="Random" src="res/dice.svg"></a>';
	if (isset($motd))
		echo '<div class="motd"><b>Notice:</b> ' . $motd . "</div>";
	echo '<div class="login">';
	session_printform();
	echo "</div></div>"; // close login and header div

	if ($nav)
	{
		// Navigation div
		echo '<div class="navigation">';
		html_nav_element_begin(NULL);
		echo '<form action="posts.php" method="GET">';
		echo '<input class="search tagbox" type="text" name="tags" value="';
		echo $tags . '"></form>';
		html_nav_element_end();
	}
	else echo '<div class="body">';
}

function html_nav_element_begin($title)
{
	echo '<div class="nav_element">';
	if (!empty($title))
		echo "<h2>" . $title . "</h2>";
}

function html_nav_element_end() { echo "</div>"; }

function html_body()
{
	// Close navigation div, open body div
	echo '</div><div class="body">';
}

function html_end()
{
	// Close body div, body and html
	echo "</div></body></html>";
}

?>
