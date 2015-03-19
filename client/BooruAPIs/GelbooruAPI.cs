using System;
using System.Xml;
using System.Collections.Generic;

namespace TA.Booru.BooruAPIs
{
    public class GelbooruAPI : BooruAPI
    {
        private const int MAX_TAGS = 10;

        protected override string GetBaseURI() { return "http://gelbooru.com/index.php?page=dapi&s=post&q=index"; }

        public override List<APIPost> SearchPosts(string[] Tags)
        {
            List<APIPost> posts = new List<APIPost>();
            string tagstring = string.Empty;
            if (Tags.Length > 0)
                if (Tags.Length > MAX_TAGS)
                {
                    string[] tags_for_search = new string[MAX_TAGS];
                    Array.Copy(Tags, tags_for_search, MAX_TAGS);
                    tagstring = string.Join("+", tags_for_search);
                }
                else tagstring = string.Join("+", Tags);
            for (int i = 0; true; i++)
            {
                XmlDocument document = GetXmlDocument(CreateURI("limit", "100", "pid", i.ToString(), "tags", tagstring));
                XmlNodeList xmlposts = document["posts"].GetElementsByTagName("post");
                if (xmlposts.Count > 0)
                    foreach (XmlNode xmlpost in xmlposts)
                    {
                        XmlAttributeCollection attribs = xmlpost.Attributes;
                        APIPost post = CreateAPIPost("Gelbooru");
                        post.SourceURL = "http://gelbooru.com/index.php?page=post&s=view&id=" + Convert.ToString(attribs["id"].Value);
                        post.Source = post.SourceURL;
                        post.Width = Convert.ToUInt32(Convert.ToString(attribs["width"].Value));
                        post.Height = Convert.ToUInt32(Convert.ToString(attribs["height"].Value));
                        post.ImageURL = Convert.ToString(attribs["file_url"].Value);
                        post.SampleURL = Convert.ToString(attribs["sample_url"].Value);
                        post.ThumbnailURL = Convert.ToString(attribs["preview_url"].Value);
                        post.Tags = GetTagList(Convert.ToString(attribs["tags"].Value));
                        posts.Add(post);
                    }
                else break;
            }
            if (Tags.Length > MAX_TAGS)
            {
                string[] rest_tags = new string[Tags.Length - MAX_TAGS];
                Array.Copy(Tags, MAX_TAGS, rest_tags, 0, rest_tags.Length);
                CheckForTags(ref posts, rest_tags);
            }
            return posts;
        }
    }
}