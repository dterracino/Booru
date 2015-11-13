using System;
using System.IO;
using System.Net;
using System.Text;

namespace TA.Booru.BooruAPIs
{
    public class Downloader
    {
        private WebProxy _Proxy;
        private CookieContainer _Cookies;

        public Downloader(WebProxy Proxy)
        {
            this._Proxy = Proxy;
            this._Cookies = new CookieContainer();
        }

        public Downloader()
            : this(null) { }

        public HttpWebResponse DoRequest(string URI, string Method, string Content, string ContentType = null)
        {
            HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(URI);
            request.Proxy = _Proxy;
            request.CookieContainer = _Cookies;

            // BehoimiAPI will return 403 if no (valid) UserAgent is sent
            request.UserAgent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:400.0) Gecko/20202020 Firefox/400.0";

            // BehoimiAPI will return an image of sausages if referer URL is not valid
            // Setting the referer URL to request URL works
            request.Referer = URI;

            request.Method = Method;
            if (Content != null)
            {
                if (ContentType == null)
                    request.ContentType = "application/x-www-form-urlencoded";
                else request.ContentType = ContentType;
                using (Stream requestStream = request.GetRequestStream())
                {
                    byte[] requestData = Encoding.UTF8.GetBytes(Content);
                    requestStream.Write(requestData, 0, requestData.Length);
                }
            }

            return (HttpWebResponse)request.GetResponse();
        }

        private byte[] ReadCompleteStream(Stream Stream)
        {
            using (MemoryStream ms = new MemoryStream())
            {
                Stream.CopyTo(ms);
                return ms.ToArray();
            }
        }

        public byte[] DownloadData(string URI)
        {
            HttpWebResponse response = DoRequest(URI, "GET", null);
            using (Stream responseStream = response.GetResponseStream())
            {
                byte[] responseData = ReadCompleteStream(responseStream);
                response.Close();
                return responseData;
            }
        }

        public string DownloadString(string URI) { return Encoding.UTF8.GetString(DownloadData(URI)); }

        public void POSTLogin(string URI, string UsernameKey, string Username, string PasswordKey, string Password)
        {
            string content = string.Format("{0}={1}&{2}={3}", UsernameKey, Username, PasswordKey, Password);
            DoRequest(URI, "POST", content).Close();
        }
    }
}