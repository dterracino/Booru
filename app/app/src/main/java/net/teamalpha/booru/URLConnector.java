package net.teamalpha.booru;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.security.cert.X509Certificate;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

public class URLConnector {

    public static HttpURLConnection Connect(String URL, Boolean ignoreCertificate) throws NoSuchAlgorithmException, KeyManagementException, IOException {
        HttpURLConnection conn = (HttpURLConnection)(new URL(URL)).openConnection();
        if (conn instanceof HttpsURLConnection && ignoreCertificate) {
            TrustManager[] trustAllCerts = new TrustManager[]{getIgnoreCertTrustManager()};
            SSLContext sc = SSLContext.getInstance("TLS");
            sc.init(null, trustAllCerts, new SecureRandom());
            HttpsURLConnection sConn = (HttpsURLConnection) conn;
            sConn.setSSLSocketFactory(sc.getSocketFactory());
            sConn.setHostnameVerifier(getIgnoreCertHostnameVerifier());
        }
        return conn;
    }

    private static TrustManager getIgnoreCertTrustManager() {
        return new X509TrustManager() {
            public X509Certificate[] getAcceptedIssuers() {
                return new X509Certificate[] { };
            }
            public void checkClientTrusted(X509Certificate[] chain, String authType) { }
            public void checkServerTrusted(X509Certificate[] chain, String authType) { }
        };
    }

    private static HostnameVerifier getIgnoreCertHostnameVerifier() {
        return new HostnameVerifier() {
            public boolean verify(String hostname, SSLSession session) {
                return true;
            }
        };
    }
}
