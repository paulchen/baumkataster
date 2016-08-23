package at.rueckgr.android.baumkatasterwien;

import android.os.AsyncTask;
import android.util.Xml;

import org.xmlpull.v1.XmlPullParser;
import org.xmlpull.v1.XmlPullParserException;

import java.io.IOException;
import java.io.InputStream;
import java.io.Serializable;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import javax.net.ssl.HttpsURLConnection;

public class NetworkTask extends AsyncTask<String, Void, List<Tree>> implements Serializable {
    private MainActivity mainActivity;

    public NetworkTask(MainActivity mainActivity) {
        this.mainActivity = mainActivity;
    }

    @Override
    protected List<Tree> doInBackground(String... urlStrings) {
        URL url;
        try {
            url = new URL(urlStrings[0]);
        }
        catch (MalformedURLException e) {
            // TODO
            return Collections.emptyList();
        }
        HttpsURLConnection urlConnection = null;
        InputStream inputStream = null;
        try {
            urlConnection = (HttpsURLConnection) url.openConnection();
            inputStream = urlConnection.getInputStream();
            return processStream(inputStream);
        }
        catch (Exception e) {
            // TODO
            return Collections.emptyList();
        }
        finally {
            if(inputStream != null) {
                try {
                    inputStream.close();
                }
                catch (Exception e) {
                    // ignore silently
                }
            }
            if(urlConnection != null) {
                try {
                    urlConnection.disconnect();
                }
                catch (Exception e) {
                    // ignore silently
                }
            }
        }
    }

    private List<Tree> processStream(InputStream inputStream) throws IOException, XmlPullParserException {
        XmlPullParser parser = Xml.newPullParser();
        parser.setFeature(XmlPullParser.FEATURE_PROCESS_NAMESPACES, false);
        parser.setInput(inputStream, null);
        parser.nextTag();

        List<Tree> trees = new ArrayList<>();
        parser.require(XmlPullParser.START_TAG, null, "trees");
        while (parser.nextTag() != XmlPullParser.END_TAG) {
            if(parser.getEventType() != XmlPullParser.START_TAG) {
                continue;
            }
            String tag = parser.getName();
            if(tag.equals("tree")) {
                Tree tree = readTree(parser);
                if(tree != null) {
                    trees.add(tree);
                }
            }
            else {
                skip(parser);
            }
        }

        return trees;
    }

    private Tree readTree(XmlPullParser parser) throws IOException, XmlPullParserException {
        parser.require(XmlPullParser.START_TAG, null, "tree");
        String title = null;
        String pflanzjahr = null;
        String baumhoehe = null;
        String kronendurchmesser = null;
        Integer baumhoeheInt = null;
        Double lat = null;
        Double lon = null;

        while(parser.next() != XmlPullParser.END_TAG) {
            if(parser.getEventType() != XmlPullParser.START_TAG) {
                continue;
            }
            String tag = parser.getName();
            switch(tag) {
                case "GATTUNG_ART":
                    title = readStringTag(tag, parser);
                    break;

                case "PFLANZJAHR_TXT":
                    pflanzjahr = readStringTag(tag, parser);
                    break;

                case "BAUMHOEHE":
                    baumhoeheInt = readIntTag(tag, parser);
                    break;

                case "BAUMHOEHE_TXT":
                    baumhoehe = readStringTag(tag, parser);
                    break;

                case "KRONENDURCHMESSER_TXT":
                    kronendurchmesser = readStringTag(tag, parser);
                    break;

                case "lat":
                    lat = readDoubleTag(tag, parser);
                    break;

                case "lon":
                    lon = readDoubleTag(tag, parser);
                    break;

                default:
                    skip(parser);
            }
        }

        if(title != null && lat != null && lon != null && pflanzjahr != null && baumhoehe != null && baumhoeheInt != null && kronendurchmesser != null) {
            return new Tree(lat, lon, title, pflanzjahr, baumhoehe, baumhoeheInt, kronendurchmesser);
        }

        return null;
    }

    private String readStringTag(String tag, XmlPullParser parser) throws IOException, XmlPullParserException {
        parser.require(XmlPullParser.START_TAG, null, tag);
        String title = readText(parser);
        parser.require(XmlPullParser.END_TAG, null, tag);
        return title;
    }

    private Integer readIntTag(String tag, XmlPullParser parser) throws IOException, XmlPullParserException {
        parser.require(XmlPullParser.START_TAG, null, tag);
        String title = readText(parser);
        parser.require(XmlPullParser.END_TAG, null, tag);
        return Integer.valueOf(title);
    }

    private Double readDoubleTag(String tag, XmlPullParser parser) throws IOException, XmlPullParserException {
        parser.require(XmlPullParser.START_TAG, null, tag);
        String title = readText(parser);
        parser.require(XmlPullParser.END_TAG, null, tag);
        return Double.valueOf(title);
    }

    // For the tags title and summary, extracts their text values.
    private String readText(XmlPullParser parser) throws IOException, XmlPullParserException {
        String result = "";
        if (parser.next() == XmlPullParser.TEXT) {
            result = parser.getText();
            parser.nextTag();
        }
        return result;
    }

    private void skip(XmlPullParser parser) throws XmlPullParserException,
            IOException {

        if (parser.getEventType() != XmlPullParser.START_TAG) {
            throw new IllegalStateException();
        }
        int depth = 1;
        while (depth != 0) {
            switch (parser.next()) {
                case XmlPullParser.END_TAG:
                    depth--;
                    break;
                case XmlPullParser.START_TAG:
                    depth++;
                    break;
            }
        }
    }

    @Override
    protected void onPostExecute(List<Tree> trees) {
        mainActivity.onSuccess(trees);
    }
}
