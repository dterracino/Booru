using System;
using System.IO;
using System.Net;
using System.Xml;
using System.Text;

namespace TA.Booru.Client
{
    public class Booru
    {
        private string _URL;
        private string _Username;
        private string _Password;

        public Booru(string URL, string Username, string Password)
        {
            _URL = URL;
            _Username = Username;
            _Password = Password;
        }

        public XmlNode Request(string Xml)
        {
            XmlDocument xml_doc = new XmlDocument();
            var request = (HttpWebRequest)WebRequest.Create(_URL);
            request.Method = "POST";
            request.ContentType = "application/xml";
            byte[] data = Encoding.UTF8.GetBytes(Xml);
            request.ContentLength = data.Length;
            using (Stream requestStream = request.GetRequestStream())
                requestStream.Write(data, 0, data.Length);
            using (var response = (HttpWebResponse)request.GetResponse())
            using (Stream responseStream = response.GetResponseStream())
                xml_doc.Load(responseStream);
            XmlNode rootNode = xml_doc.DocumentElement;
            string error = rootNode["Error"].InnerText;
            if (!string.IsNullOrEmpty(error))
                throw new Exception(error);
            else return rootNode;
        }

        public XMLFactory CreateXMLFactory(StringBuilder SB) { return new XMLFactory(SB, _Username, _Password); }

        public uint Upload(byte[] Image, bool Private, string Source, string Info, byte Rating, string[] Tags)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteUpload(Image, Private, Source, Info, Rating, Tags);
            XmlNode node = Request(sb.ToString());
            return Convert.ToUInt32(node["ID"].InnerText);
        }

        public void Delete(uint ID)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteDelete(ID);
            Request(sb.ToString());
        }

        public bool TagExists(string Tag)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteTagExists(Tag);
            XmlNode node = Request(sb.ToString());
            return Convert.ToByte(node["Bool"].InnerText) > 0;
        }
    }
}
