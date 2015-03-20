using System;
using System.Net;
using System.Xml;
using System.Text.RegularExpressions;
using LitJson;

namespace TA.Booru.BooruAPIs
{
    public abstract class BooruAPI
    {
        public abstract APIPost GetPost(uint ID);

        protected XmlDocument GetXmlDocument(string URI)
        {
            string xml_text = null;
            using (WebClient wc = new WebClient())
                xml_text = wc.DownloadString(URI);
            XmlDocument document = new XmlDocument();
            document.LoadXml(xml_text);
            return document;
        }

        protected JsonData GetJsonData(string URI)
        {
            string json_text = null;
            using (WebClient wc = new WebClient())
                json_text = wc.DownloadString(URI);
            JsonReader reader = new JsonReader(json_text);
            JsonData data = JsonMapper.ToObject(reader);
            reader.Close();
            return data;
        }

        public static APIPost GetPost(string URL)
        {
            URL = URL.Trim().ToLower();
            if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)gelbooru.com\\/index.php\\?page=post&s=view.*&id=[0-9]*"))
            {
                string id = Regex.Match(URL, "&id=[0-9]*").Value.Substring(4);
                return (new GelbooruAPI()).GetPost(Convert.ToUInt32(id));
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)konachan.(com|net)\\/post\\/show\\/[0-9]*\\/?.*"))
            {
                string id = Regex.Match(URL, "show\\/[0-9]*").Value.Substring(5);
                string domain = Regex.Match(URL, "konachan.(com|net)").Value.Substring(9);
                return (new KonachanAPI(domain == "com")).GetPost(Convert.ToUInt32(id));
            }
            else return null;
        }
    }
}
