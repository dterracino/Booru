using System;
using System.Xml;

namespace TA.Booru.BooruAPIs
{
    public class GelbooruAPI : BooruAPI
    {
        public override APIPost GetPost(uint ID)
        {
            XmlDocument document = GetXmlDocument("http://gelbooru.com/index.php?page=dapi&s=post&q=index&id=" + ID);
            XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
            if (xmlposts.Count > 0)
            {
                XmlAttributeCollection attribs = xmlposts[0].Attributes;
                APIPost post = CreateAPIPost("Gelbooru");
                post.SourceURL = "http://gelbooru.com/index.php?page=post&s=view&id=" + Convert.ToString(attribs["id"].Value);
                post.Source = post.SourceURL;
                post.Width = Convert.ToUInt32(Convert.ToString(attribs["width"].Value));
                post.Height = Convert.ToUInt32(Convert.ToString(attribs["height"].Value));
                post.ImageURL = attribs["file_url"].Value;
                post.SampleURL = attribs["sample_url"].Value;
                post.ThumbnailURL = attribs["preview_url"].Value;
                post.Tags = attribs["tags"].Value.Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                return post;
            }
            else throw new Exception("Post not found");
        }
    }
}
