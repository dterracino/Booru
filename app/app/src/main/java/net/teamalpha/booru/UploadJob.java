package net.teamalpha.booru;

import android.net.Uri;

public class UploadJob {
    public final String API_URL;
    public final Boolean IgnoreCertificate;
    public final String Username;
    public final String Password;
    public final String Source;
    public final String Info;
    public final int Rating;
    public final Boolean Private;
    public final String Tags;
    public final Uri ImageURI;

    public UploadJob(
            String API_URL,
            Boolean IgnoreCertificate,
            String Username,
            String Password,
            String Source,
            String Info,
            int Rating,
            Boolean Private,
            String Tags,
            Uri ImageURI
            )
    {
        this.API_URL = API_URL;
        this.IgnoreCertificate = IgnoreCertificate;
        this.Username = Username;
        this.Password = Password;
        this.Source = Source;
        this.Info = Info;
        this.Rating = Rating;
        this.Private = Private;
        this.Tags = Tags;
        this.ImageURI = ImageURI;
    }
}
