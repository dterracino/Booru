package net.teamalpha.booru;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.ContentResolver;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.util.Base64;
import android.util.Base64OutputStream;

import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.cert.X509Certificate;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

public class UploadTask extends AsyncTask<UploadJob, Integer, UploadResult> {

    private static final int CHUNK_SIZE = 128;

    private ProgressDialog pd;
    private Activity activity;

    public UploadTask(Activity activity) {
        this.activity = activity;
    }

    @Override
    protected void onPreExecute() {
        super.onPreExecute();
        pd = new ProgressDialog(activity);
        pd.setProgressStyle(ProgressDialog.STYLE_HORIZONTAL);
        pd.setTitle(activity.getString(R.string.progress_dialog_title));
        pd.setMessage(activity.getString(R.string.progress_dialog_message));
        pd.setMax(0);
        pd.setProgress(0);
        pd.setCancelable(false);
        pd.show();
    }



    @Override
    protected UploadResult doInBackground(UploadJob[] jobs) {
        UploadJob job = jobs[0];
        try {
            HttpURLConnection conn = URLConnector.Connect(job.API_URL, job.IgnoreCertificate);
            conn.setDoInput(true);
            conn.setDoOutput(true);
            conn.setUseCaches(false);
            conn.setChunkedStreamingMode(CHUNK_SIZE);
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Content-Type", "application/xml");
            OutputStream out = conn.getOutputStream();

            String xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
            xml += "<Request>\n";
            xml += "\t<Login>\n";
            xml += "\t\t<Username>" + job.Username + "</Username>\n";
            xml += "\t\t<Password>" + job.Password + "</Password>\n";
            xml += "\t</Login>\n";
            xml += "\t<Type>Upload</Type>\n";
            xml += "\t<Image>";
            byte[] bytes_before_image = xml.getBytes("UTF-8");
            out.write(bytes_before_image);

            Base64OutputStream b64stream = new Base64OutputStream(out, Base64.NO_CLOSE | Base64.NO_WRAP);
            ContentResolver contentResolver = activity.getContentResolver();
            int imageByteCount = (int)contentResolver.openFileDescriptor(job.ImageURI, "r").getStatSize();
            InputStream imgStream = contentResolver.openInputStream(job.ImageURI);
            byte[] buffer = new byte[CHUNK_SIZE];
            Boolean contRead = true;
            int totalBytesWritten = 0;
            while (contRead) {
                int read = imgStream.read(buffer, 0, CHUNK_SIZE);
                if (read > 0) {
                    b64stream.write(buffer, 0, read);
                    totalBytesWritten += read;
                    int progress = (int)((float)totalBytesWritten / imageByteCount * 100f + 0.5f);
                    publishProgress(progress);
                } else {
                    contRead = false;
                }
            }
            b64stream.close();

            xml = "</Image>\n";
            xml += "\t<Post>\n";
            xml += "\t\t<Private>" + (job.Private ? "1" : "0") + "</Private>\n";
            xml += "\t\t<Source>" + escapeXml(job.Source) + "</Source>\n";
            xml += "\t\t<Info>" + escapeXml(job.Info) + "</Info>\n";
            xml += "\t\t<Rating>" + job.Rating + "</Rating>\n";
            xml += "\t\t<Tags>\n";
            String[] tags = job.Tags.split("[ \\t\\r\\n]");
            for (String tag : tags)
                if (tag.length() > 0) {
                    xml += "\t\t\t<Tag>";
                    xml += escapeXml(tag.toLowerCase()) + "</Tag>\n";
                }
            xml += "\t\t</Tags>\n";
            xml += "\t</Post>\n";
            xml += "</Request>";
            byte[] bytes_after_image = xml.getBytes("UTF-8");
            out.write(bytes_after_image);
            out.close();

            InputStream in = conn.getInputStream();
            UploadResult result = UploadResult.parseFromXml(in);
            in.close();
            return result;
        } catch (Exception ex) {
            return new UploadResult(ex.getMessage());
        }
    }

    public static String escapeXml (String str)
    {
        StringBuilder sb;
        if (str == null)
            return null;
        sb = new StringBuilder ();
        for (int i = 0; i < str.length(); i++) {
            char c = str.charAt(i);
            switch (c) {
                case '<': sb.append ("&lt;"); break;
                case '>': sb.append ("&gt;"); break;
                case '"': sb.append ("&quot;"); break;
                case '\'': sb.append ("&apos;"); break;
                case '&': sb.append ("&amp;"); break;
                default: sb.append (c); break;
            }
        }
        return sb.toString();
    }

    @Override
    protected void onProgressUpdate(Integer[] progresses) {
        pd.setMax(100);
        pd.setProgress(progresses[0]);
    }

    @Override
    protected void onPostExecute(UploadResult result) {
        pd.dismiss();
        String dialogMessage = null;
        if (result.Error != null) {
            dialogMessage = "An error occurred\n" + result.Error;
        } else {
            dialogMessage = "Post successfully uploaded\nID " + result.ID;
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(activity);
        builder.setMessage(dialogMessage);
        builder.setCancelable(false);
        builder.setPositiveButton("OK", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                activity.finish();
            }
        });
        builder.create().show();
    }
}
