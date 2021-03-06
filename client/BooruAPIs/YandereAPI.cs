using System;
using System.Net;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class YandereAPI : BooruAPI
    {
        public YandereAPI() 
            : base(null) { }
        public YandereAPI(WebProxy Proxy) 
            : base(Proxy) { }

        public override void Login(string Username, string Password) { }

        public override APIPost GetPost(uint ID)
        {
            XmlDocument document = GetXmlDocument("http://yande.re/post.xml?tags=id%3A" + ID);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlAttributeCollection attribs = xmlposts[0].Attributes;
                return new APIPost("yande.re")
                {
                    Source = "http://yande.re/post/show/" + Convert.ToString(attribs["id"].Value),
                    Tags = attribs["tags"].Value.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries),

                    ThumbnailURL = attribs["preview_url"].Value,
                    SampleURL = attribs["sample_url"].Value,
                    ImageURL = attribs["file_url"].Value
                };
            }
            else throw new ArgumentException("Post not found");
        }
    }
}
