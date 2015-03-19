using System;
using LitJson;

namespace TA.Booru.BooruAPIs
{
    public class KonachanAPI : BooruAPI
    {
        private bool _R18 = false;

        public KonachanAPI() { }
        public KonachanAPI(bool R18)
            : this() { _R18 = R18; }

        public override APIPost GetPost(uint ID)
        {
            JsonData json = GetJsonData("http://konachan" + (_R18 ? ".com" : ".net") + "/post.json?tags=id:" + ID);
            if (json.Count > 0)
            {
                JsonData jpost = json[0];
                APIPost post = CreateAPIPost("Konachan");
                post.SourceURL = "http://konachan.com/post/show/" + Convert.ToString(jpost["id"]);
                post.Source = post.SourceURL;
                post.Width = Convert.ToUInt32(Convert.ToString(jpost["width"]));
                post.Height = Convert.ToUInt32(Convert.ToString(jpost["height"]));
                post.ImageURL = Convert.ToString(jpost["file_url"]);
                post.SampleURL = Convert.ToString(jpost["sample_url"]);
                post.ThumbnailURL = Convert.ToString(jpost["preview_url"]);
                post.Tags = Convert.ToString(jpost["tags"]).Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                return post;
            }
            else throw new Exception("Post not found");
        }
    }
}
