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
        private WebProxy _Proxy;

        public Booru(string URL, string Username, string Password, WebProxy Proxy = null)
        {
            _URL = URL;
            _Username = Username;
            _Password = Password;
            _Proxy = Proxy;
        }

        public XmlElement Request(string Xml)
        {
            XmlDocument xml_doc = new XmlDocument();
            var request = (HttpWebRequest)WebRequest.Create(_URL);
            request.Proxy = _Proxy;
            request.Method = "POST";
            request.ContentType = "application/xml";
            byte[] data = Encoding.UTF8.GetBytes(Xml);
            request.ContentLength = data.Length;
            using (Stream requestStream = request.GetRequestStream())
                requestStream.Write(data, 0, data.Length);
            using (var response = (HttpWebResponse)request.GetResponse())
            using (Stream responseStream = response.GetResponseStream())
                xml_doc.Load(responseStream);
            XmlElement rootNode = xml_doc.DocumentElement;
            string error = rootNode["Error"].InnerText;
            if (!string.IsNullOrEmpty(error))
                throw new RemoteBooruException(error);
            else return rootNode;
        }

        public XMLFactory CreateXMLFactory(StringBuilder SB) { return new XMLFactory(SB, _Username, _Password); }

        public uint Upload(byte[] Image, bool Private, string Source, string Info, byte Rating, string[] Tags, bool Force)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteUpload(Image, Private, Source, Info, Rating, Tags, Force);
            XmlElement xml = Request(sb.ToString());
            return Convert.ToUInt32(xml["ID"].InnerText);
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
            XmlElement xml = Request(sb.ToString());
            return Convert.ToByte(xml["Bool"].InnerText) > 0;
        }

        public void SetImage(uint ID, byte[] Image)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteSetImage(ID, Image);
            Request(sb.ToString());
        }

        public void GetImage(uint ID, out byte[] ImageData, out string MimeType)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteGetImage(ID);
            XmlElement image = Request(sb.ToString())["Image"];
            ImageData = Convert.FromBase64String(image.InnerText);
            MimeType = image.Attributes["type"].Value;
        }

        public uint[] FindDuplicates(string Hash, byte[] Image = null)
        {
            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = CreateXMLFactory(sb))
                factory.WriteFindDuplicates(Hash, Image);
            XmlElement result = Request(sb.ToString())["IDs"];
            uint[] dupeIds = new uint[result.ChildNodes.Count];
            for (int i = 0; i < dupeIds.Length; i++)
                dupeIds[i] = Convert.ToUInt32(result.ChildNodes[i].InnerText);
            return dupeIds;
        }
    }
}
