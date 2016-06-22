using System;
using System.Net;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class BehoimiAPI : BooruAPI
    {
        public BehoimiAPI() 
            : base(null) { }
        public BehoimiAPI(WebProxy Proxy) 
            : base(Proxy) { }

        public override void Login(string Username, string Password)
        {
            _Downloader.POSTLogin("http://behoimi.org/user/authenticate", "user[name]", Username, "user[password]", Password);
        }

        public override APIPost GetPost(uint ID)
        {
            XmlDocument document = GetXmlDocument("http://behoimi.org/post/index.xml?tags=id%3A" + ID);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlAttributeCollection attribs = xmlposts[0].Attributes;
                return new APIPost("3dbooru")
                {
                    Source = "http://behoimi.org/post/show/" + Convert.ToString(attribs["id"].Value),
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
