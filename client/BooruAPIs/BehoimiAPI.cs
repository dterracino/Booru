using System;
using System.Net;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class BehoimiAPI : BooruAPI
    {
        public override APIPost GetPost(uint ID, WebProxy Proxy)
        {
            XmlDocument document = GetXmlDocument("http://behoimi.org/post/index.xml?tags=id%3A" + ID, Proxy);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlAttributeCollection attribs = xmlposts[0].Attributes;
                return new APIPost("Behoimi")
                {
                    Source = "http://behoimi.org/post/show/" + Convert.ToString(attribs["id"].Value),
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
