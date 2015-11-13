using System;
using System.Net;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class KonachanAPI : BooruAPI
    {
        private bool _R18 = false;

        public KonachanAPI()
            : base(null) { }
        public KonachanAPI(WebProxy Proxy)
            : base(Proxy) { }
        public KonachanAPI(bool R18)
            : base(null) { _R18 = R18; }
        public KonachanAPI(WebProxy Proxy, bool R18)
            : base(Proxy) { _R18 = R18; }

        public override void Login(string Username, string Password) { }

        public override APIPost GetPost(uint ID)
        {
            XmlDocument document = GetXmlDocument("http://konachan." + (_R18 ? "com" : "net") + "/post.xml?tags=id%3A" + ID);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlAttributeCollection attribs = xmlposts[0].Attributes;
                return new APIPost("Konachan")
                {
                    Source = "http://konachan.com/post/show/" + Convert.ToString(attribs["id"].Value),
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
