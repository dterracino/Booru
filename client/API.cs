using System;
using System.IO;
using System.Net;
using System.Xml;
using System.Text;

namespace TA.Booru.Client
{
	public class API
	{
		private string _URL;

		public API(string URL) { _URL = URL; }

		private Stream RawRequest(string RequestBody)
		{
			var request = (HttpWebRequest)WebRequest.Create(_URL);
			request.Method = "POST";
			request.ContentType = "application/xml";
			byte[] data = Encoding.UTF8.GetBytes(RequestBody);
			request.ContentLength = data.Length;
			using (Stream requestStream = request.GetRequestStream())
				requestStream.Write(data, 0, data.Length);
			var response = (HttpWebResponse)request.GetResponse();
			return response.GetResponseStream();
		}

		public string RequestString(string Xml)
		{
			using (Stream responseStream = RawRequest(Xml))
			using (StreamReader reader = new StreamReader(responseStream))
				return reader.ReadToEnd();
		}

		public XmlNode RequestXml(string Xml)
		{
			XmlDocument xml_doc = new XmlDocument();
			using (Stream responseStream = RawRequest(Xml))
				xml_doc.Load(responseStream);
			XmlNode rootNode = xml_doc.DocumentElement;
			string error = rootNode["Error"].InnerText;
			if (!string.IsNullOrEmpty(error))
				throw new Exception(error);
			else return rootNode;
		}
	}
}
