using System.Net;

namespace TA.Booru.BooruAPIs
{
    public class APIPost
    {
        public string Source = string.Empty;
        public string Info = string.Empty;
        public uint Width = 0;
        public uint Height = 0;
        public string[] Tags = null;

        public string ImageURL = string.Empty;
        public string SampleURL = string.Empty;
        public string ThumbnailURL = string.Empty;
        public string SourceURL = string.Empty;
        public string APIName = string.Empty;

        public byte[] Image = null;

        public void DownloadImage()
        {
            if (Image == null)
                try
                {
                    using (WebClient client = new WebClient())
                        Image = client.DownloadData(ImageURL);
                }
                catch { Image = null; }
        }
    }
}
