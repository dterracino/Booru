using System;
namespace TA.Booru.Client
{
    public class BooruException : Exception
    {
        public BooruException(string message)
            : base(message) { }

        public BooruException(string message, Exception innerException)
            : base(message, innerException) { }
    }

    public class RemoteBooruException : BooruException
    {
        public RemoteBooruException(string message)
            : base(message) { }

        public RemoteBooruException(string message, Exception innerException)
            : base(message, innerException) { }
    }
}
