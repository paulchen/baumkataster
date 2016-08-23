package at.rueckgr.android.baumkatasterwien;

import java.io.Serializable;

/**
 * Created by paulchen on 17.07.16.
 */
public class Tree implements Serializable {
    private double lat;
    private double lon;
    private String title;
    private String pflanzjahr;
    private String baumhoehe;
    private int baumhoeheInt;
    private String kronendurchmesser;

    public Tree(double lat, double lon, String title, String pflanzjahr, String baumhoehe, int baumhoeheInt, String kronendurchmesser) {
        this.lat = lat;
        this.lon = lon;
        this.title = title;
        this.pflanzjahr = pflanzjahr;
        this.baumhoehe = baumhoehe;
        this.baumhoeheInt = baumhoeheInt;
        this.kronendurchmesser = kronendurchmesser;
    }

    public double getLat() {
        return lat;
    }

    public void setLat(double lat) {
        this.lat = lat;
    }

    public double getLon() {
        return lon;
    }

    public void setLon(double lon) {
        this.lon = lon;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getPflanzjahr() {
        return pflanzjahr;
    }

    public void setPflanzjahr(String pflanzjahr) {
        this.pflanzjahr = pflanzjahr;
    }

    public String getBaumhoehe() {
        return baumhoehe;
    }

    public void setBaumhoehe(String baumhoehe) {
        this.baumhoehe = baumhoehe;
    }

    public int getBaumhoeheInt() {
        return baumhoeheInt;
    }

    public void setBaumhoeheInt(int baumhoeheInt) {
        this.baumhoeheInt = baumhoeheInt;
    }

    public String getKronendurchmesser() {
        return kronendurchmesser;
    }

    public void setKronendurchmesser(String kronendurchmesser) {
        this.kronendurchmesser = kronendurchmesser;
    }
}
