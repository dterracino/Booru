using System;
using System.Reflection;
using CommandLine;

namespace TA.Booru.Client
{
    internal class Options
    {
        [Option("api-url", Required = false, HelpText = "The Booru API URL (overrides config)")]
        public string API_URL { get; set; }

        [Option("username", Required = false, HelpText = "Your username (overrides config)")]
        public string Username { get; set; }

        [Option("password", Required = false, HelpText = "Your password (overrides config)")]
        public string Password { get; set; }

        [Option("proxy-en", Required = false, DefaultValue = false, HelpText = "Enable the configured proxy")]
        public bool ProxyEnable { get; set; }

        private string GetAssemblyAttribute<T>(Func<T, string> value) where T : Attribute
        {
            T attribute = (T)Attribute.GetCustomAttribute(Assembly.GetExecutingAssembly(), typeof(T));
            return value.Invoke(attribute);
        }
    }

    [Verb("add", HelpText = "Adds a post")]
    internal class AddOptions : Options
    {
        [Option('i', "image", Required = true, HelpText = "The posts image or URL")]
        public string ImagePathOrURL { get; set; }

        [Option('t', "tags", Required = true, HelpText = "The posts tags")]
        public string Tags { get; set; }

        [Option('s', "source", Required = false, HelpText = "The image source")]
        public string Source { get; set; }

        [Option("info", Required = false, HelpText = "Additional information")]
        public string Info { get; set; }

        [Option('r', "rating", DefaultValue = 7, Required = false, HelpText = "The content rating")]
        public int Rating { get; set; }

        [Option("private", Required = false, HelpText = "Private setting")]
        public bool Private { get; set; }

        [Option("force", Required = false, HelpText = "Force upload")]
        public bool Force { get; set; }
    }

    [Verb("addurl", HelpText = "Imports a post via BooruAPI")]
    internal class AddUrlOptions : Options
    {
        [Option("api-username", Required = false, HelpText = "BooruAPI username")]
        public string BooruAPI_Username { get; set; }

        [Option("api-password", Required = false, HelpText = "BooruAPI password")]
        public string BooruAPI_Password { get; set; }

        [Option('u', "url", Required = true, HelpText = "The URL to import")]
        public string URL { get; set; }

        [Option("custom-image", Required = false, HelpText = "The custom image path or URL")]
        public string CustomImagePathOrURL { get; set; }

        [Option("all-tags", DefaultValue = false, Required = false, HelpText = "Add all tags, not only already known")]
        public bool AllTags { get; set; }

        [Option("tags-no-delta", DefaultValue = false, Required = false, HelpText = "--tags defines all tags, not only delta")]
        public bool TagsNoDelta { get; set; }

        [Option('t', "tags", Required = false, HelpText = "Additional tags (delta)")]
        public string Tags { get; set; }

        [Option("info", Required = false, HelpText = "Additional information")]
        public string Info { get; set; }

        [Option('r', "rating", DefaultValue = 7, Required = false, HelpText = "The content rating")]
        public int Rating { get; set; }

        [Option("private", Required = false, HelpText = "Private setting")]
        public bool Private { get; set; }

        [Option("force", Required = false, HelpText = "Force upload")]
        public bool Force { get; set; }
    }

    [Verb("del", HelpText = "Deletes a post")]
    internal class DelOptions : Options
    {
        [Option('i', "id", Required = true, HelpText = "The post ID")]
        public uint ID { get; set; }
    }

    [Verb("edit", HelpText = "Edits a post")]
    internal class EditOptions : Options
    {
        [Option('i', "id", Required = true, HelpText = "The post ID")]
        public uint ID { get; set; }

        [Option("tags-no-delta", DefaultValue = false, Required = false, HelpText = "--tags defines all tags, not only delta")]
        public bool TagsNoDelta { get; set; }

        [Option('t', "tags", Required = false, HelpText = "The posts tags delta")]
        public string Tags { get; set; }

        [Option("source", Required = false, HelpText = "The new image source")]
        public string Source { get; set; }

        [Option("info", Required = false, HelpText = "The new description")]
        public string Info { get; set; }

        [Option('r', "rating", DefaultValue = -1, Required = false, HelpText = "The new content rating")]
        public int Rating { get; set; }

        [Option("private", Required = false, HelpText = "New private setting")]
        public bool? Private { get; set; }
    }

    [Verb("getimg", HelpText = "Gets a posts image")]
    internal class GetImgOptions : Options
    {
        [Option('i', "id", Required = true, HelpText = "The post/image ID")]
        public uint ID { get; set; }

        [Option("path", Required = true, HelpText = "The image path")]
        public string Path { get; set; }
    }

    [Verb("setimg", HelpText = "Sets a posts image")]
    internal class SetImgOptions : Options
    {
        [Option('i', "id", Required = true, HelpText = "The post/image ID")]
        public uint ID { get; set; }

        [Option("image", Required = true, HelpText = "The image path or URL")]
        public string ImagePathOrURL { get; set; }
    }

    [Verb("finddupes", HelpText = "Finds possible duplicates of an image")]
    internal class FindDupeOptions : Options
    {
        [Option("image", Required = true, HelpText = "The image path or URL")]
        public string ImagePathOrURL { get; set; }
    }

    /*
    [Verb("get", HelpText = "Gets a post")]
    internal class GetOptions : Options
    {
        [Option('i', "id", Required = true, HelpText = "The post ID")]
        public uint ID { get; set; }
    }

    [Verb("editimg", HelpText = "Edits a posts image")]
    internal class EditImgOptions : Options
    {
        [Option('i', "id", Required = true, HelpText = "The post/image ID")]
        public uint ID { get; set; }

        [Option("path", Required = true, HelpText = "The temporary image path (w/o extension)")]
        public string Path { get; set; }

        [Option("tool", Required = false, HelpText = "The image editor program")]
        public string Tool { get; set; }
    }
    */
}
