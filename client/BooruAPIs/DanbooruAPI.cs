using System;
using System.Net;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class DanbooruAPI : BooruAPI
    {
        public override APIPost GetPost(uint ID, WebProxy Proxy)
        {
            XmlDocument document = GetXmlDocument("http://danbooru.donmai.us/posts.xml?tags=id%3A" + ID, Proxy);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlNode post = xmlposts[0];
                return new APIPost("Danbooru")
                {
                    Source = "http://danbooru.donmai.us/posts/" + Convert.ToString(post["id"].InnerText),
                    Tags = post["tag-string"].InnerText.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries),

                    ThumbnailURL = "http://danbooru.donmai.us" + post["preview-file-url"].InnerText,
                    SampleURL = "http://danbooru.donmai.us" + post["large-file-url"].InnerText, // WTF Danbooru?
                    ImageURL = "http://danbooru.donmai.us" + post["file-url"].InnerText
                };
            }
            else throw new Exception("Post not found");
        }
    }
}
