using System;
using System.Net;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class GelbooruAPI : BooruAPI
    {
        public GelbooruAPI()
            : base(null) { }
        public GelbooruAPI(WebProxy Proxy)
            : base(Proxy) { }

        public override void Login(string Username, string Password)
        {
            _Downloader.POSTLogin("http://gelbooru.com/index.php?page=account&s=login&code=00", "user", Username, "pass", Password);
        }

        public override APIPost GetPost(uint ID)
        {
            XmlDocument document = GetXmlDocument("http://gelbooru.com/index.php?page=dapi&s=post&q=index&id=" + ID);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlAttributeCollection attribs = xmlposts[0].Attributes;
                return new APIPost("Gelbooru")
                {
                    Source = "http://gelbooru.com/index.php?page=post&s=view&id=" + Convert.ToString(attribs["id"].Value),
                    Tags = attribs["tags"].Value.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries),

                    ThumbnailURL = attribs["preview_url"].Value,
                    SampleURL = attribs["sample_url"].Value,
                    ImageURL = attribs["file_url"].Value
                };
            }
            else throw new Exception("Post not found");
        }
    }
}
