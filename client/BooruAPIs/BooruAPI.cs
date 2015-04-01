using System;
using System.Net;
using System.Xml;
using System.Text.RegularExpressions;

namespace TA.Booru.BooruAPIs
{
    public abstract class BooruAPI
    {
        public abstract APIPost GetPost(uint ID, WebProxy Proxy = null);

        protected XmlDocument GetXmlDocument(string URL, WebProxy Proxy)
        {
            XmlDocument document = new XmlDocument();
            using (WebClient wc = CreateWebClient(URL, Proxy))
                document.LoadXml(wc.DownloadString(URL));
            return document;
        }

        public static WebClient CreateWebClient(string URL, WebProxy Proxy = null)
        {
            WebClient client = new WebClient() { Proxy = Proxy };

            // BehoimiAPI will return 403 if no UserAgent is sent
            client.Headers.Add(HttpRequestHeader.UserAgent, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:400.0) Gecko/20100101 Firefox/400.0");

            // BehoimiAPI will return an image of sausages if referer is not valid
            client.Headers.Add(HttpRequestHeader.Referer, URL);

            return client;
        }

        public static APIPost GetPost(string URL, WebProxy Proxy = null)
        {
            URL = URL.Trim().ToLower();
            if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)gelbooru.com\\/index.php\\?page=post&s=view.*&id=[0-9]*"))
            {
                string id = Regex.Match(URL, "&id=[0-9]*").Value.Substring(4);
                return (new GelbooruAPI()).GetPost(Convert.ToUInt32(id), Proxy);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|https:\\/\\/|)(www.|)danbooru.donmai.us\\/posts\\/[0-9]*.*"))
            {
                string id = Regex.Match(URL, "posts\\/[0-9]*").Value.Substring(6);
                return (new DanbooruAPI()).GetPost(Convert.ToUInt32(id), Proxy);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|https:\\/\\/|)(www.|)konachan.(com|net)\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                string id = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
                string domain = Regex.Match(URL, "konachan.(com|net)").Value.Substring(9);
                return (new KonachanAPI(domain == "com")).GetPost(Convert.ToUInt32(id), Proxy);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|https:\\/\\/|)(www.|)behoimi.org\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                string id = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
                return (new BehoimiAPI()).GetPost(Convert.ToUInt32(id), Proxy);
            }
            else return null;
        }
    }
}
