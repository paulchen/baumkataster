package at.rueckgr.android.baumkatasterwien;

import android.content.Context;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.graphics.Typeface;
import android.support.annotation.NonNull;
import android.support.v4.app.ActivityCompat;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.Gravity;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptor;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.CameraPosition;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.LatLngBounds;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;

import org.apache.commons.lang3.ArrayUtils;

import java.text.MessageFormat;
import java.util.List;

public class MainActivity extends AppCompatActivity implements OnMapReadyCallback, GoogleMap.OnCameraChangeListener, GoogleMap.OnMarkerClickListener,
        GoogleMap.OnMyLocationButtonClickListener, AsyncResponse {

    private GoogleMap mMap;
    private boolean markerClicked;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        // Obtain the SupportMapFragment and get notified when the map is ready to be used.
        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);
    }


    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;

        LatLng vienna = new LatLng(48.20833, 16.373064);
        mMap.moveCamera(CameraUpdateFactory.newLatLng(vienna));
        mMap.moveCamera(CameraUpdateFactory.zoomTo(10));
        mMap.setOnCameraChangeListener(this);
        if (ActivityCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED ||
                ActivityCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_COARSE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
            mMap.setMyLocationEnabled(true);
            mMap.setOnMyLocationButtonClickListener(this);
        }
        else {
            ActivityCompat.requestPermissions(this, new String[] { android.Manifest.permission.ACCESS_COARSE_LOCATION, android.Manifest.permission.ACCESS_FINE_LOCATION }, 1);
        }

        mMap.setInfoWindowAdapter(new GoogleMap.InfoWindowAdapter() {

            @Override
            public View getInfoWindow(Marker arg0) {
                return null;
            }

            @Override
            public View getInfoContents(Marker marker) {

                Context context = getApplicationContext(); //or getActivity(), YourActivity.this, etc.

                LinearLayout info = new LinearLayout(context);
                info.setOrientation(LinearLayout.VERTICAL);

                TextView title = new TextView(context);
                title.setTextColor(Color.BLACK);
                title.setGravity(Gravity.CENTER);
                title.setTypeface(null, Typeface.BOLD);
                title.setText(marker.getTitle());

                TextView snippet = new TextView(context);
                snippet.setTextColor(Color.GRAY);
                snippet.setText(marker.getSnippet());

                info.addView(title);
                info.addView(snippet);

                return info;
            }
        });
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        if(requestCode == 1) {
            if(ArrayUtils.contains(grantResults, PackageManager.PERMISSION_GRANTED)) {
                //noinspection MissingPermission
                mMap.setMyLocationEnabled(true);
                mMap.setOnMyLocationButtonClickListener(this);
            }
        }
    }

    @Override
    public void onCameraChange(CameraPosition cameraPosition) {
        if(markerClicked) {
            markerClicked = false;
            return;
        }

        mMap.clear();

        if(cameraPosition.zoom >= 16) {
            LatLngBounds latLngBounds = mMap.getProjection().getVisibleRegion().latLngBounds;
            LatLng northeast = latLngBounds.northeast;
            LatLng southwest = latLngBounds.southwest;

            String urlString = MessageFormat.format("https://rueckgr.at/~paulchen/baeume.php?bbox={0},{1},{2},{3}",
                    southwest.latitude, southwest.longitude, northeast.latitude, northeast.longitude);

            new NetworkTask(this).execute(urlString);
        }
    }

    @Override
    public boolean onMarkerClick(Marker marker) {
        markerClicked = true;
        mMap.moveCamera(CameraUpdateFactory.newLatLng(marker.getPosition()));
        marker.showInfoWindow();
        return true;
    }

    @Override
    public boolean onMyLocationButtonClick() {
        mMap.moveCamera(CameraUpdateFactory.zoomTo(17));
        return false;
    }

    @Override
    public void onSuccess(List<Tree> trees) {
        BitmapDescriptor bis5m = BitmapDescriptorFactory.fromResource(R.drawable.baumbestand_bis5m);
        BitmapDescriptor bis15m = BitmapDescriptorFactory.fromResource(R.drawable.baumbestand_bis15m);
        BitmapDescriptor groesser15m = BitmapDescriptorFactory.fromResource(R.drawable.baumbestand_groesser15m);

        for (Tree tree : trees) {
            LatLng latLng = new LatLng(tree.getLat(), tree.getLon());

            BitmapDescriptor descriptor;
            switch(tree.getBaumhoeheInt()) {

                case 2:
                case 3:
                    descriptor = bis15m;
                    break;

                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                    descriptor = groesser15m;
                    break;

                case 0:
                case 1:
                default:
                    descriptor = bis5m;
            }

            String snippet = MessageFormat.format("Pflanzjahr: {0}\nBaumhöhe: {1}\nKronendurchmesser: {2}", tree.getPflanzjahr(), tree.getBaumhoehe(), tree.getKronendurchmesser());
            mMap.addMarker(new MarkerOptions().position(latLng).title(tree.getTitle()).icon(descriptor).snippet(snippet));
            mMap.setOnMarkerClickListener(this);
        }
    }
}
