using System;
using System.IO;
using System.Text;
using System.Linq;
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
                /*
                typeof(GetOptions),
                typeof(EditOptions),
                typeof(EditImgOptions),
                typeof(GetImgOptions),
                typeof(SetImgOptions)
                */
            });
            if (!pResult.Errors.Any())
            {
                try
                {
                    var commonOptions = (Options)pResult.Value;
                    Config config = Config.TryLoad();
                    if (config != null)
                    {
                        if (commonOptions.URL == null)
                            commonOptions.URL = config.URL;
                        if (commonOptions.Username == null)
                            commonOptions.Username = config.Username;
                        if (commonOptions.Password == null)
                            commonOptions.Password = config.Password;
                    }
                    Booru booru = new Booru(commonOptions.URL, commonOptions.Username, commonOptions.Password);

                    Type oType = commonOptions.GetType();
                    if (oType == typeof(AddOptions))
                    {
                        var options = (AddOptions)commonOptions;
                        Console.Write("Loading image... ");
                        byte[] image = File.ReadAllBytes(options.ImagePath); //TODO Read with FileStream
                        Console.WriteLine("OK");
                        var tags = options.Tags.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                        byte rating = (byte)options.Rating;
                        bool is_private = options.Private ?? false;
                        Console.Write("Adding post... ");
                        uint id = booru.Upload(image, is_private, options.Source, options.Info, rating, tags);
                        Console.WriteLine(id);
                    }
                    else if (oType == typeof(AddUrlOptions))
                    {
                        var options = (AddUrlOptions)commonOptions;
                        var apiPosts = BooruAPI.SearchPostsPerURL(options.URL);
                        if (apiPosts.Count > 1)
                            Console.WriteLine("Multiple posts found, importing only the first one");
                        else if (apiPosts.Count < 1)
                            throw new Exception("No post to import detected");
                        var apiPost = apiPosts[0];
                        if (options.CustomImagePath == null)
                        {
                            Console.Write("Downloading image... ");
                            apiPost.DownloadImage();
                        }
                        else
                        {
                            Console.Write("Loading image... ");
                            apiPost.Image = File.ReadAllBytes(options.CustomImagePath); //TODO Read with FileStream
                        }
                        Console.WriteLine("OK");
                        if (!options.AllTags)
                        {
                            for (int a = apiPost.Tags.Count - 1; !(a < 0); a--)
                                if (!booru.TagExists(apiPost.Tags[a]))
                                    apiPost.Tags.RemoveAt(a);
                        }
                        if (options.Tags != null)
                        {
                            options.Tags = options.Tags.ToLower();
                            if (options.TagsNoDelta)
                            {
                                string[] parts = options.Tags.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                                apiPost.Tags = parts.ToList();
                            }
                            else TagDelta(ref apiPost.Tags, options.Tags);
                        }
                        if (options.Info != null)
                            apiPost.Info = options.Info;
                        byte rating = (byte)options.Rating;
                        bool is_private = options.Private ?? false;
                        Console.Write("Importing post... ");
                        ulong id = booru.Upload(apiPost.Image, is_private, apiPost.Source, apiPost.Info, rating, null);
                        Console.WriteLine(id);
                    }
                    else if (oType == typeof(DelOptions))
                    {
                        var options = (DelOptions)commonOptions;
                        booru.Delete(options.ID);
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
                    else if (oType == typeof(EditOptions))
                    {
                        var options = (EditOptions)commonOptions;
                        using (var post = GetPost(ns, options.ID))
                        {
                            post.EditCount += 1;
                            if (options.Description != null)
                                post.Description = options.Description;
                            if (options.Private.HasValue)
                                post.Private = options.Private.Value;
                            if (!(options.Rating < 0) && options.Rating < byte.MaxValue)
                                post.Rating = (byte)options.Rating;
                            if (options.Source != null)
                                post.Source = options.Source;
                            if (options.Tags != null)
                            {
                                options.Tags = options.Tags.ToLower();
                                if (options.TagsNoDelta)
                                {
                                    string[] parts = options.Tags.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                                    post.Tags.Clear();
                                    foreach (string part in parts)
                                        post.Tags.Add(new BooruTag(part));
                                }
                                else TagDelta(ref post.Tags, options.Tags);
                            }
                            Request(ns, RequestCode.Edit_Post, (rw) =>
                                {
                                    post.ToWriter(rw);
                                    post.Tags.ToWriter(rw);
                                }, (rw) => { });
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
                    else if (oType == typeof(GetImgOptions))
                    {
                        GetImgOptions options = (GetImgOptions)commonOptions;
                        BooruImage img = null;
                        try
                        {
                            Request(ns, RequestCode.Get_Image, (rw) => rw.Write(options.ID), (rw) => { img = BooruImage.FromReader(rw); });
                            string path = options.Path;
                            img.Save(ref path, true);
                        }
                        finally { img.Dispose(); }
                    }
                    else if (oType == typeof(SetImgOptions))
                    {
                        SetImgOptions options = (SetImgOptions)commonOptions;
                        using (BooruImage img = BooruImage.FromFile(options.Path))
                            Request(ns, RequestCode.Edit_Image, (rw) =>
                            {
                                rw.Write(options.ID);
                                img.ToWriter(rw);
                            }, (rw) => { });
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
                if (part.StartsWith("_") && part.Length > 1)
                    removeTags.Add(part.Substring(1).ToLower());
                else addTags.Add(part.ToLower());
            foreach (string rTag in removeTags)
                Tags.RemoveAll((val) => rTag == val);
            Tags.AddRange(addTags);
        }
    }
}
