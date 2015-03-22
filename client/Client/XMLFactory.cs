using System;
using System.Text;

namespace TA.Booru.Client
{
    public class XMLFactory : IDisposable
    {
        private StringBuilder _SB;

        public XMLFactory(StringBuilder SB, string Username, string Password)
        {
            _SB = SB;
            _SB.AppendLine("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>");
            _SB.AppendLine("<Request>");
            _SB.AppendLine("\t<Login>");
            _SB.AppendLine("\t\t<Username>" + Username + "</Username>");
            _SB.AppendLine("\t\t<Password>" + Password + "</Password>");
            _SB.AppendLine("\t</Login>");
        }

        public void Dispose() { _SB.Append("</Request>"); }

        private string Escape(string str) { return System.Security.SecurityElement.Escape(str); }

        public void WriteDelete(uint ID)
        {
            _SB.AppendLine("\t<Type>Delete</Type>");
            _SB.AppendLine("\t<ID>" + ID + "</ID>");
        }

        public void WriteUpload(byte[] Image, bool Private, string Source, string Info, byte Rating, string[] Tags)
        {
            _SB.AppendLine("\t<Type>Upload</Type>");
            _SB.AppendLine("\t<Image>" + Convert.ToBase64String(Image) + "</Image>");
            _SB.AppendLine("\t<Post>");
            _SB.AppendLine("\t\t<Private>" + (Private ? "1" : "0") + "</Private>");
            _SB.AppendLine("\t\t<Source>" + Escape(Source ?? string.Empty) + "</Source>");
            _SB.AppendLine("\t\t<Info>" + Escape(Info ?? string.Empty) + "</Info>");
            _SB.AppendLine("\t\t<Rating>" + Rating + "</Rating>");
            _SB.AppendLine("\t\t<Tags>");
            foreach (string tag in Tags)
                _SB.AppendLine("\t\t\t<Tag>" + Escape(tag) + "</Tag>");
            _SB.AppendLine("\t\t</Tags>");
            _SB.AppendLine("\t</Post>");
        }

        public void WriteTagExists(string Tag)
        {
            _SB.AppendLine("\t<Type>TagExists</Type>");
            _SB.AppendLine("\t<Tag>" + Tag + "</Tag>");
        }

        public void WriteEditHeader(uint ID)
        {
            _SB.AppendLine("\t<Type>Edit</Type>");
            _SB.AppendLine("\t<ID>" + ID + "</ID>");
            _SB.AppendLine("\t<Post>");
        }

        public void WriteEditSource(string Source) { _SB.AppendLine("\t\t<Source>" + Escape(Source) + "</Source>"); }
        public void WriteEditInfo(string Info) { _SB.AppendLine("\t\t<Info>" + Escape(Info) + "</Info>"); }
        public void WriteEditRating(byte Rating) { _SB.AppendLine("\t\t<Rating>" + Rating + "</Rating>"); }
        public void WriteEditPrivate(bool Private) { _SB.AppendLine("\t\t<Private>" + (Private ? 1 : 0) + "</Private>"); }

        public void WriteEditTags(bool? AddOrRemove, string[] Tags)
        {
            string nodeName = "Tags";
            if (AddOrRemove.HasValue)
                nodeName += AddOrRemove.Value ? "Add" : "Remove";
            _SB.AppendLine("\t\t<" + nodeName + ">");
            foreach (string tag in Tags)
                _SB.AppendLine("\t\t\t<Tag>" + Escape(tag) + "</Tag>");
            _SB.AppendLine("\t\t</" + nodeName + ">");
        }

        public void WriteEditFooter() { _SB.AppendLine("\t</Post>"); }
    }
}
