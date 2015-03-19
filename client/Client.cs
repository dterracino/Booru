using System;
using System.IO;
using System.Text;

namespace TA.Booru.Client
{
    public static class Client
    {
        public static void Main()
        {
            Console.Write("API URL: ");
            string api_url = Console.ReadLine();

            Console.Write("Username: ");
            string username = Console.ReadLine();

            Console.Write("Password: ");
            string password = Console.ReadLine();

            Console.Write("Image file: ");
            byte[] image = File.ReadAllBytes(Console.ReadLine());

            Console.Write("Source: ");
            string source = Console.ReadLine();

            Console.Write("Info: ");
            string info = Console.ReadLine();

            Console.Write("Rating: ");
            byte rating = Convert.ToByte(Console.ReadLine());

            Console.Write("Tags: ");
            string[] tags = Console.ReadLine().Split(' ');

            StringBuilder sb = new StringBuilder();
            using (XMLFactory factory = new XMLFactory(sb, username, password))
                factory.WriteUpload(image, false, source, info, rating, tags);
            string request = sb.ToString();

            API api = new API(api_url);
            string response = api.RequestString(request);

            Console.WriteLine();
            Console.WriteLine(response);
        }
    }
}