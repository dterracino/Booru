namespace TA.Booru.KN
{
	public class Config
	{
		public const string BooruName = "Booru";
		public const string MOTD = null;

		public const string MySQL_Host = "127.0.0.1";
		public const string MySQL_Username = "booru";
		public const string MySQL_Password = "booru";
		public const string MySQL_Database = "booru";

		public const string Directory_Thumbnails = "/srv/kn_booru/thumbs";
		public const string Directory_Images = "/srv/kn_booru/images";

		// sendfile method

		// 0 = with Stream
		// 1 = X-Sendfile (lighttpd) (untested)
		public const byte Sendfile_Method = 0;

		public const ushort ThumbsPerPage = 60;
		public const ushort ThumbSize = 120;
		public const ushort ThumbGenSize = 256;

		public const byte MaxSearchTerm = 8;

		public static Dictionary<string, string> MimeTypes = new Dictionary<string, string>()
		{
			{ "image/jpeg", ".jpg" },
			{ "image/png", ".png" },
			{ "image/gif", ".gif" },
			{ "video/webm", ".webm" },
			{ "application/x-shockwave-flash", ".swf" }
		};

		// If your server doesn't provide the SERVER_NAME variable,
		// please set it manually (instead of booru.example.com)
		if (isset($_SERVER["HTTPS"]))
			$server_base_url = "https://";
		else $server_base_url = "http://";
		if (isset($_SERVER["SERVER_NAME"]))
			$server_base_url .= $_SERVER["SERVER_NAME"];
		else $server_base_url .= "booru.example.com";
	}
}
