using System;
using System.IO;
using System.Net;
using System.Linq;
using System.Text;
using System.Collections.Generic;
using CommandLine;
using TA.Booru.BooruAPIs;

namespace TA.Booru.Client
{
    public static class Client
    {
        public static int Main(string[] args)
        {
            var pResult = Parser.Default.ParseArguments(args, new Type[]
            {
                typeof(AddOptions),
                typeof(AddUrlOptions),
                typeof(DelOptions),
                typeof(EditOptions),
                typeof(GetImgOptions),
                typeof(SetImgOptions)
                /*
                typeof(GetOptions),
                typeof(EditImgOptions),
                */
            });
            if (!pResult.Errors.Any())
            {
                try
                {
                    var commonOptions = (Options)pResult.Value;
                    Config config = Config.TryLoad();
                    WebProxy proxy = null;
                    if (config != null)
                    {
                        if (commonOptions.API_URL == null)
                            commonOptions.API_URL = config.URL;
                        if (commonOptions.Username == null)
                            commonOptions.Username = config.Username;
                        if (commonOptions.Password == null)
                            commonOptions.Password = config.Password;
                        if (commonOptions.ProxyEnable && config.Proxy != null)
                            proxy = config.Proxy;
                    }
                    else if (commonOptions.API_URL == null || commonOptions.Username == null || commonOptions.Password == null)
                        Console.WriteLine("Config file not found, you must provide --api-url, --username and --password");
                    else Console.WriteLine("Tip: You can create a config file for faster client usage");
                    if (commonOptions.ProxyEnable && proxy == null)
                        Console.WriteLine("Proxy is enabled but not configured");
                    Booru booru = new Booru(commonOptions.API_URL, commonOptions.Username, commonOptions.Password, proxy);

                    Type oType = commonOptions.GetType();
                    if (oType == typeof(AddOptions))
                    {
                        var options = (AddOptions)commonOptions;
                        byte[] image = DownLoadImage(options.ImagePathOrURL, proxy);
                        var tags = options.Tags.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                        byte rating = (byte)options.Rating;
                        bool is_private = options.Private ?? false;
                        Console.Write("Uploading post... ");
                        uint id = booru.Upload(image, is_private, options.Source, options.Info, rating, tags);
                        Console.WriteLine(id);
                        return 0;
                    }
                    else if (oType == typeof(AddUrlOptions))
                    {
                        var options = (AddUrlOptions)commonOptions;
                        var apiPost = BooruAPI.GetPost(options.URL, proxy);
                        byte[] image = DownLoadImage(options.CustomImagePathOrURL ?? apiPost.ImageURL, proxy);
                        bool is_private = options.Private ?? false;
                        string info = null;
                        if (options.Info != null)
                            info = options.Info;
                        byte rating = (byte)options.Rating;
                        var tags = new List<string>();
                        if (!options.AllTags)
                        {
                            Console.Write("Checking tag existence... ");
                            foreach (string tag in apiPost.Tags)
                                if (booru.TagExists(tag))
                                    tags.Add(tag);
                            Console.WriteLine("OK");
                        }
                        else tags.AddRange(apiPost.Tags);
                        if (options.Tags != null)
                        {
                            options.Tags = options.Tags.ToLower();
                            if (options.TagsNoDelta)
                            {
                                string[] parts = options.Tags.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                                tags = parts.ToList();
                            }
                            else TagDelta(ref tags, options.Tags);
                        }
                        Console.Write("Uploading post... ");
                        ulong id = booru.Upload(image, is_private, apiPost.Source, info, rating, tags.ToArray());
                        Console.WriteLine(id);
                        return 0;
                    }
                    else if (oType == typeof(DelOptions))
                    {
                        var options = (DelOptions)commonOptions;
                        booru.Delete(options.ID);
                        return 0;
                    }
                    else if (oType == typeof(EditOptions))
                    {
                        var options = (EditOptions)commonOptions;
                        StringBuilder sb = new StringBuilder();
                        using (XMLFactory factory = booru.CreateXMLFactory(sb))
                        {
                            factory.WriteEditHeader(options.ID);
                            if (options.Info != null)
                                factory.WriteEditInfo(options.Info);
                            if (options.Private.HasValue)
                                factory.WriteEditPrivate(options.Private.Value);
                            if (!(options.Rating < 0))
                            {
                                if (options.Rating < byte.MaxValue)
                                    factory.WriteEditRating((byte)options.Rating);
                                else throw new ArgumentException("Rating value too big");
                            }
                            if (options.Source != null)
                                factory.WriteEditSource(options.Source);
                            if (options.Tags != null)
                            {
                                options.Tags = options.Tags.ToLower();
                                string[] parts = options.Tags.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                                if (!options.TagsNoDelta)
                                {
                                    List<string> addTags = new List<string>();
                                    List<string> removeTags = new List<string>();
                                    foreach (string part in parts)
                                        if (part.StartsWith("!") || part.StartsWith("_"))
                                            removeTags.Add(part.Substring(1));
                                        else addTags.Add(part);
                                    factory.WriteEditTags(true, addTags.ToArray());
                                    factory.WriteEditTags(false, removeTags.ToArray());
                                }
                                else factory.WriteEditTags(null, parts);
                            }
                            factory.WriteEditFooter();
                        }
                        booru.Request(sb.ToString());
                    }
                    else if (oType == typeof(GetImgOptions))
                    {
                        GetImgOptions options = (GetImgOptions)commonOptions;
                        Console.Write("Downloading image... ");
                        byte[] imageData;
                        string mimeType;
                        booru.GetImage(options.ID, out imageData, out mimeType);
                        using (FileStream fs = new FileStream(options.Path, FileMode.Create, FileAccess.Write, FileShare.Read))
                            fs.Write(imageData, 0, imageData.Length);
                        Console.WriteLine("OK");
                    }
                    else if (oType == typeof(SetImgOptions))
                    {
                        SetImgOptions options = (SetImgOptions)commonOptions;
                        byte[] image = DownLoadImage(options.ImagePathOrURL, proxy);
                        Console.Write("Uploading image... ");
                        booru.SetImage(options.ID, image);
                        Console.WriteLine("OK");
                    }
                    #region Other methods
                    /*
                    else if (oType == typeof(GetOptions))
                    {
                        var options = (GetOptions)commonOptions;
                        using (var post = GetPost(ns, options.ID))
                        {
                            Console.WriteLine("User        " + post.User);
                            Console.WriteLine("Private     " + (post.Private ? "yes" : "no"));
                            Console.WriteLine("Source      " + post.Source ?? string.Empty);
                            Console.WriteLine("Description " + post.Description ?? string.Empty);
                            Console.WriteLine("Rating      " + post.Rating);
                            Console.WriteLine("Size        {0}x{1}", post.Width, post.Height);
                            Console.WriteLine("Date        {0}", post.CreationDate);
                            Console.WriteLine("ViewCount   " + post.ViewCount);
                            Console.WriteLine("EditCount   " + post.EditCount);
                            Console.WriteLine("Score       " + post.Score);
                            Console.WriteLine();
                            Console.WriteLine(BooruTagListToString(post.Tags));
                        }
                    }
                    else if (oType == typeof(EditImgOptions))
                    {
                        EditImgOptions options = (EditImgOptions)commonOptions;
                        BooruImage img = null;
                        string path = options.Path;
                        try
                        {
                            Request(ns, RequestCode.Get_Image, (rw) => rw.Write(options.ID), (rw) => { img = BooruImage.FromReader(rw); });
                            img.Save(ref path, true);
                        }
                        finally { img.Dispose(); }
                        if (options.Tool != null)
                        {
                            var psi = new ProcessStartInfo(options.Tool, path);
                            Process tool = new Process() { StartInfo = psi };
                            tool.Start();
                            Console.Write("Waiting for image editor to exit...");
                            tool.WaitForExit();
                        }
                        else
                        {
                            Console.Write("Edit the image and press any key to save it... ");
                            Console.ReadKey(true);
                            Console.WriteLine();
                        }
                        using (BooruImage eImg = BooruImage.FromFile(path))
                            Request(ns, RequestCode.Edit_Image, (rw) =>
                                {
                                    rw.Write(options.ID);
                                    eImg.ToWriter(rw);
                                }, (rw) => { });
                        File.Delete(options.Path);
                    }
                    */
                    #endregion
                }
                catch (Exception ex)
                {
                    Console.WriteLine(ex.GetType().FullName + ": " + ex.Message);
                    Console.WriteLine(ex.StackTrace);
                }
            }
            return 1;
        }

        //TODO Improve
        private static void TagDelta(ref List<string> Tags, string deltaString)
        {
            var removeTags = new List<string>();
            var addTags = new List<string>();
            string[] deltaParts = deltaString.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
            foreach (string part in deltaParts)
                if ((part.StartsWith("!") || part.StartsWith("_")) && part.Length > 1)
                    removeTags.Add(part.Substring(1).ToLower());
                else addTags.Add(part.ToLower());
            foreach (string rTag in removeTags)
                Tags.RemoveAll((val) => rTag == val);
            Tags.AddRange(addTags);
        }

        private static byte[] DownLoadImage(string PathOrURL, WebProxy Proxy = null)
        {
            if (File.Exists(PathOrURL))
            {
                Console.Write("Loading image... ");
                using (FileStream fs = new FileStream(PathOrURL, FileMode.Open, FileAccess.Read, FileShare.Read))
                {
                    byte[] buff = new byte[fs.Length];
                    fs.Read(buff, 0, buff.Length);
                    Console.WriteLine("OK");
                    return buff;
                }
            }
            else if (IsURL(PathOrURL))
            {
                Console.Write("Downloading image... ");
                // Use BooruAPI compatible WebClient
                using (WebClient wc = BooruAPI.CreateWebClient(PathOrURL, Proxy))
                {
                    byte[] buff = wc.DownloadData(PathOrURL);
                    Console.WriteLine("OK");
                    return buff;
                }
            }
            else throw new Exception("File not found or invalid URL");
        }

        private static bool IsURL(string URL)
        {
            Uri uriResult;
            return Uri.TryCreate(URL, UriKind.Absolute, out uriResult)
                && (uriResult.Scheme == Uri.UriSchemeHttp || uriResult.Scheme == Uri.UriSchemeHttps);
        }

        private static string GetExtensionByType(string mime)
        {
            if (mime != null)
                switch (mime)
                {
                    case "image/jpeg": return "jpg";
                    case "image/png": return "png";
                    case "image/gif": return "gif";
                    case "video/webm": return "webm";
                    case "application/x-shockwave-flash": return "swf";
                }
            return "bin";
        }
    }
}