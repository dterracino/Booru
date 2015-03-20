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

        protected APIPost CreateAPIPost(string APIName)
        {
            return new APIPost()
            {
                APIName = APIName,
                Info = "Imported from " + APIName
            };
        }

        private static string ExtractParameterFromURLQuery(string URL, string Param)
        {
            int ss_begin = URL.IndexOf(Param + "=");
            if (!(ss_begin < 0))
                ss_begin += Param.Length + 1;
            else return string.Empty;
            int ss_end = URL.IndexOf('&', ss_begin);
            if (ss_end < 0)
                ss_end = URL.Length;
            return URL.Substring(ss_begin, ss_end - ss_begin);
        }

        public static APIPost GetPost(string URL)
        {
            //TODO Add pool detection
            URL = URL.Trim().ToLower();
            if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)gelbooru.com\\/index.php\\?page=post&s=view&id=[0-9]*"))
            {
                string id = URL.Substring(URL.LastIndexOf("=") + 1);
                return (new GelbooruAPI()).GetPost(Convert.ToUInt32(id));
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)gelbooru.com/i\\ndex.php\\?page=post&s=view&id=[0-9]*&pool_id=[0-9]*"))
            {
                int idIndex = URL.LastIndexOf("&id=") + 4;
                string id = URL.Substring(idIndex, URL.LastIndexOf("=") - idIndex);
                return (new GelbooruAPI()).GetPost(Convert.ToUInt32(id));
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)konachan.com\\/post/show/[0-9]*/?.*"))
            {
                string id = Regex.Match(URL, "show/[0-9]{1,}").Value.Substring(5);
                return (new KonachanAPI(true)).GetPost(Convert.ToUInt32(id));
            }
            else if (Regex.IsMatch(URL, "(http:\\/\\/|)(www.|)konachan.net\\/post\\/show\\/[0-9]*/?.*"))
            {
                string id = Regex.Match(URL, "show/[0-9]{1,}").Value.Substring(5);
                return (new KonachanAPI(false)).GetPost(Convert.ToUInt32(id));
            }
            return null;
        }
    }
}
