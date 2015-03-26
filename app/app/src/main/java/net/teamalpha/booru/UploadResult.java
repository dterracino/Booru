package net.teamalpha.booru;

import org.xmlpull.v1.XmlPullParser;
import org.xmlpull.v1.XmlPullParserFactory;

import java.io.InputStream;

public class UploadResult {
    public final String Error;
    public final int ID;

    public UploadResult(String Error) {
        this.Error = Error;
        this.ID = 0;
    }

    public UploadResult(int ID) {
        this.Error = null;
        this.ID = ID;
    }

    public static UploadResult parseFromXml(InputStream in) {
        try {
            XmlPullParserFactory factory = XmlPullParserFactory.newInstance();
            XmlPullParser xpp = factory.newPullParser();
            xpp.setInput(in, "UTF-8");

            int eventType = xpp.getEventType();
            while (eventType != XmlPullParser.END_DOCUMENT) {
                if (eventType == XmlPullParser.START_TAG) {
                    String tag = xpp.getName();
                    if (tag.equals("Error")) {
                        if (xpp.next() == XmlPullParser.TEXT) {
                            return new UploadResult(xpp.getText());
                        }
                    } else if (tag.equals("ID")) {
                        String id = xpp.nextText();
                        return new UploadResult(Integer.parseInt(id));
                    }
                }
                eventType = xpp.next();
            }
        } catch (Exception ex) { }
        return new UploadResult("Couldn't parse XML");
    }
}
