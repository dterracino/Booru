using System;
using System.IO;
using System.Net;
using System.Xml;

namespace TA.Booru.Client
{
    public class Config
    {
        public readonly string URL;
        public readonly string Username;
        public readonly string Password;
        public readonly WebProxy Proxy;

        public Config(string URL, string Username, string Password, WebProxy Proxy)
        {
            this.URL = URL;
            this.Username = Username;
            this.Password = Password;
            this.Proxy = Proxy;
        }

        public static Config TryLoad()
        {
            string[] paths = new string[3];
            paths[0] = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.Personal), ".config", "booru.conf");
            paths[1] = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.Personal), "booru.conf");
            paths[2] = "/etc/booru.conf";

            for (int i = 0; i < paths.Length; i++)
                if (File.Exists(paths[i]))
                {
                    XmlDocument xml = new XmlDocument();
                    using (FileStream fs = new FileStream(paths[i], FileMode.Open, FileAccess.Read, FileShare.Read))
                        xml.Load(fs);

                    XmlNode rootNode = xml.SelectSingleNode("/BooruConfig");
                    string api_url = rootNode.SelectSingleNode("URL").InnerText;
                    XmlNode loginNode = rootNode["Login"];
                    string username = loginNode["Username"] .InnerText;
                    string password = loginNode["Password"].InnerText;
                    XmlNode proxyNode = rootNode["Proxy"];
                    WebProxy proxy = null;
                    if (proxyNode != null)
                    {
                        string proxy_ip = proxyNode["IP"].InnerText;
                        string proxy_username = proxyNode["Username"].InnerText;
                        string proxy_password = proxyNode["Password"].InnerText;
                        proxy = new WebProxy(proxy_ip, false, new string[0], new NetworkCredential(proxy_username, proxy_password));
                    }
                    return new Config(api_url, username, password, proxy);
                }

            return null;
        }
    }
}
