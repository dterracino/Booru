using System;
using System.Net;
using LitJson;

namespace TA.Booru.BooruAPIs
{
    public class KonachanAPI : BooruAPI
    {
        private bool _R18 = false;

        public KonachanAPI() { }
        public KonachanAPI(bool R18)
            : this() { _R18 = R18; }

        public override APIPost GetPost(uint ID, WebProxy Proxy)
        {
            JsonData json = GetJsonData("http://konachan" + (_R18 ? ".com" : ".net") + "/post.json?tags=id:" + ID, Proxy);
            if (json.Count > 0)
            {
                JsonData jpost = json[0];
                return new APIPost("Konachan")
                {
                    Source = "http://konachan.com/post/show/" + Convert.ToString(jpost["id"]),
                    Tags = Convert.ToString(jpost["tags"]).Split(new char[1] { ' ' }, StringSplitOptions.RemoveEmptyEntries),

                    ThumbnailURL = Convert.ToString(jpost["preview_url"]),
                    SampleURL = Convert.ToString(jpost["sample_url"]),
                    ImageURL = Convert.ToString(jpost["file_url"])
                };
            }
            else throw new Exception("Post not found");
        }
    }
}
