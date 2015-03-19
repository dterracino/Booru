using System;
using System.IO;
using System.Xml;

namespace TA.Booru.Client
{
    public class Config
    {
        public readonly string URL;
        public readonly string Username;
        public readonly string Password;

        public Config(string URL, string Username, string Password)
        {
            this.URL = URL;
            this.Username = Username;
            this.Password = Password;
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
                    string socket = rootNode.SelectSingleNode("URL").InnerText;
                    string username = rootNode.SelectSingleNode("Username").InnerText;
                    string password = rootNode.SelectSingleNode("Password").InnerText;
                    return new Config(socket, username, password);
                }

            return null;
        }
    }
}
