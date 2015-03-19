using System;
using System.Collections.Generic;
using LitJson;

namespace TA.Booru.BooruAPIs
{
    public class KonachanAPI : BooruAPI
    {
        private const int MAX_TAGS = 6;
        private bool _R18 = false;

        public KonachanAPI() { }
        public KonachanAPI(bool R18)
            : this() { _R18 = R18; }

        protected override string GetBaseURI() { return string.Format("http://konachan.{0}/post.json", _R18 ? "com" : "net"); }

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
            for (int i = 1; true; i++)
            {
                JsonData json = GetJSONData(CreateURI("limit", "100", "page", i.ToString(), "tags", tagstring));
                if (json.Count > 0)
                    for (int p = 0; p < json.Count; p++)
                    {
                        JsonData jpost = json[p];
                        APIPost post = CreateAPIPost("Konachan");
                        post.SourceURL = "http://konachan.com/post/show/" + Convert.ToString(jpost["id"]);
                        post.Source = post.SourceURL;
                        post.Width = Convert.ToUInt32(Convert.ToString(jpost["width"]));
                        post.Height = Convert.ToUInt32(Convert.ToString(jpost["height"]));
                        post.ImageURL = Convert.ToString(jpost["file_url"]);
                        post.SampleURL = Convert.ToString(jpost["sample_url"]);
                        post.ThumbnailURL = Convert.ToString(jpost["preview_url"]);
                        post.Tags = GetTagList(Convert.ToString(jpost["tags"]));
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