using System;
using System.Net;
using System.Xml;
using System.Text.RegularExpressions;

namespace TA.Booru.BooruAPIs
{
    public abstract class BooruAPI
    {
        protected Downloader _Downloader;

        protected BooruAPI(WebProxy Proxy = null) { this._Downloader = new Downloader(Proxy); }

        public abstract void Login(string Username, string Password);

        public abstract APIPost GetPost(uint ID);

        protected XmlDocument GetXmlDocument(string URI)
        {
            XmlDocument document = new XmlDocument();
            document.LoadXml(_Downloader.DownloadString(URI));
            return document;
        }

        public static APIPost GetPost(string URL, WebProxy Proxy = null, string APIUsername = null, string APIPassword = null)
        {
            URL = URL.Trim().ToLower();

            BooruAPI api = null;
            string strId = null;

            if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)gelbooru.com\\/index.php\\?page=post&s=view.*&id=[0-9]*"))
            {
                api = new GelbooruAPI(Proxy);
                strId = Regex.Match(URL, "&id=[0-9]*").Value.Substring(4);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|https:\\/\\/|)(www.|)danbooru.donmai.us\\/posts\\/[0-9]*.*"))
            {
                api = new DanbooruAPI(Proxy);
                strId = Regex.Match(URL, "posts\\/[0-9]*").Value.Substring(6);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|https:\\/\\/|)(www.|)konachan.(com|net)\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                string domain = Regex.Match(URL, "konachan.(com|net)").Value.Substring(9);
                api = new KonachanAPI(Proxy, domain == "com");
                strId = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|https:\\/\\/|)(www.|)yande.re\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                api = new YandereAPI(Proxy);
                strId = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)behoimi.org\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                api = new BehoimiAPI(Proxy);
                strId = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
            }

            if (api != null)
            {
                if (APIUsername != null && APIPassword != null)
                    api.Login(APIUsername, APIPassword);
                return api.GetPost(Convert.ToUInt32(strId));
            }

            return null;
        }
    }
}
