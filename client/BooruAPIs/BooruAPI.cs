using System;
using System.Net;
using System.Xml;
using System.Text.RegularExpressions;

namespace TA.Booru.BooruAPIs
{
    public abstract class BooruAPI
    {
        public abstract APIPost GetPost(uint ID, WebProxy Proxy = null);

        protected XmlDocument GetXmlDocument(string URI, WebProxy Proxy)
        {
            string xml_text = null;
            using (WebClient wc = new WebClient())
            {
                wc.Proxy = Proxy;
                xml_text = wc.DownloadString(URI);
            }
            XmlDocument document = new XmlDocument();
            document.LoadXml(xml_text);
            return document;
        }

        public static APIPost GetPost(string URL, WebProxy Proxy = null)
        {
            URL = URL.Trim().ToLower();
            if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)gelbooru.com\\/index.php\\?page=post&s=view.*&id=[0-9]*"))
            {
                string id = Regex.Match(URL, "&id=[0-9]*").Value.Substring(4);
                return (new GelbooruAPI()).GetPost(Convert.ToUInt32(id), Proxy);
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)konachan.(com|net)\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                string id = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
                string domain = Regex.Match(URL, "konachan.(com|net)").Value.Substring(9);
                return (new KonachanAPI(domain == "com")).GetPost(Convert.ToUInt32(id), Proxy);
            }
            else return null;
        }
    }
}
