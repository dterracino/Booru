using System.Net;

namespace TA.Booru.BooruAPIs
{
    public class APIPost
    {
        public APIPost(string APIName) { this.APIName = APIName; }

        public string APIName = string.Empty;

        public string Source = string.Empty;
        public string[] Tags = null;

        public string ThumbnailURL = string.Empty;
        public string SampleURL = string.Empty;
        public string ImageURL = string.Empty;

        public byte[] DownloadImage(WebProxy Proxy = null)
        {
            using (WebClient client = new WebClient())
            {
                client.Proxy = Proxy;
                return client.DownloadData(ImageURL);
            }
        }
    }
}
