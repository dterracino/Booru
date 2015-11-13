using System;
using TA.KuroNeko.LibKuro;

namespace TA.Booru.Web
{
    public class UserPage:Page
    {
        public override void  CreatePage()
{
            try{
            if (!GET.ContainsKey("id"))
                throw new Exception("ID not set");
            int id = Convert.ToInt32(GET["id"]);

                BooruUser 


            }
}
    }
}

	$user = $db->booru_get_user_by_id($id);

	html_begin($user["username"]);

	html_nav_element_begin("Profile");
	echo "If you want more permissions or a different profile pic, contact an user with p_admin";
	html_nav_element_end();

	html_body();

	try
	{
		echo '<img alt="" src="avatar.php?id=' . $id . '"><br><br>';
		echo '<span style="font-size: 40px">' . $user["username"] . "</span>";
		if ($user["job"] != "")
			echo "<br>" . $user["job"];

		echo "<br><br><b>Permissions</b>";
		foreach ($user as $key => $value)
			if (substr($key, 0, 2) == "p_" && $value == 1)
				echo "<br>" . $key;

		echo "<br><br><b>Search</b>";
		echo '<a href="posts.php?tags=%3Au%3D' . $user["username"] . '">';
		echo "<br>Posts uploaded by " . $user["username"] . "</a>";
		echo '<a href="posts.php?tags=%3Af%3D' . $user["username"] . '">';
		echo "<br>Favorites of " . $user["username"] . "</a>";
	}
	catch (Exception $ex) { echo $ex->getMessage(); }

	html_end();
}
catch (Exception $ex) { html_error("User", 500, $ex->getMessage()); }

?>
