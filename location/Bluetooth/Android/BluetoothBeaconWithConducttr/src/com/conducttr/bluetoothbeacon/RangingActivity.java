package com.conducttr.bluetoothbeacon;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.Collection;

import oauth.signpost.commonshttp.CommonsHttpOAuthConsumer;
import oauth.signpost.commonshttp.CommonsHttpOAuthProvider;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;

import android.app.Activity;
import android.content.Context;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.RemoteException;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ListView;
import android.widget.TextView;

import com.radiusnetworks.ibeacon.IBeacon;
import com.radiusnetworks.ibeacon.IBeaconConsumer;
import com.radiusnetworks.ibeacon.IBeaconManager;
import com.radiusnetworks.ibeacon.RangeNotifier;
import com.radiusnetworks.ibeacon.Region;



public class RangingActivity extends Activity implements IBeaconConsumer {

	protected static final String TAG = "RangingActivity";
	private ListView list = null;
	private ArrayList<IBeaconInfo> myList = new ArrayList<IBeaconInfo>();
	private MyBaseAdapter adapter;
    private IBeaconManager iBeaconManager = IBeaconManager.getInstanceForApplication(this);
    private SharedPreferences preferences ;
	private Constants myConstants = new Constants();

    private String audience_phone;
    
	@Override
    protected void onCreate(Bundle savedInstanceState) {
		Log.d(TAG, "onCreate");
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_ranging);	
		
		myList = myConstants.myList;
		list = (ListView) findViewById(R.id.list); 
		
		adapter = new MyBaseAdapter(this, myList);
		list.setAdapter(adapter);
	   
		iBeaconManager.bind(this);	
	    verifyBluetooth();
		preferences = getSharedPreferences(myConstants.PREFS_NAME, Context.MODE_PRIVATE);

        audience_phone = getIntent().getExtras().getString("audience_phone");
		audience_phone = audience_phone.trim();
		myList.clear();
	}

    @Override 
    protected void onDestroy() {
        super.onDestroy();
        iBeaconManager.unBind(this);
    }
    @Override 
    protected void onPause() {
    	super.onPause();
    	if (iBeaconManager.isBound(this)) iBeaconManager.setBackgroundMode(this, true);    		
    }
    @Override 
    protected void onResume() {
    	super.onResume();
    	if (iBeaconManager.isBound(this)) iBeaconManager.setBackgroundMode(this, false);    		
    }

    @Override
    public void onIBeaconServiceConnect() {
        iBeaconManager.setRangeNotifier(new RangeNotifier() {
	        @Override 
	        public void didRangeBeaconsInRegion(Collection<IBeacon> iBeacons, Region region) {
	   
	        	if (iBeacons.size() > 0) {
		        	for (IBeacon iBeacon: iBeacons) {
		        		int index = getIndexByUUID(iBeacon.getProximityUuid());
		        		if (index ==  -1){
		        			IBeaconInfo tempIbeacon = new IBeaconInfo (iBeacon.getProximityUuid(),iBeacon.getMajor(),iBeacon.getMinor());
		        			myList.add(tempIbeacon);
			        		index = getIndexByUUID(iBeacon.getProximityUuid());
		        		}
		        		IBeaconInfo myIBeaconInfo = myList.get(index);

		        		/* Discard to far away results
		        		if  (myIBeaconInfo.iBeacon_last_accuracy > iBeacon.getAccuracy()+2 ) || (myIBeaconInfo.iBeacon_last_accuracy < iBeacon.getAccuracy()-2 ){
		        			iBeacon1_last_accuracy = myIBeacon.getAccuracy();
		        		}
		        		*/	   			
		        		myIBeaconInfo.iBeacon_proximity+= iBeacon.getProximity();  	
		        		myIBeaconInfo.iBeacon_accuracy = iBeacon.getAccuracy();
		        		
	        			if (myIBeaconInfo.iBeacon_count>=myConstants.COUNT){
		        			myIBeaconInfo.iBeacon_proximity=myIBeaconInfo.iBeacon_proximity/myConstants.COUNT;
		        			myIBeaconInfo.parsediBeacon_proximity = (int)myIBeaconInfo.iBeacon_proximity;
	            			
		        			if (myIBeaconInfo.parsediBeacon_proximity != myIBeaconInfo.iBeacon_last_proximity && myIBeaconInfo.iBeacon_last_proximity!= 0){
		        				
		        				String last = "";
		        				if (myIBeaconInfo.iBeacon_last_proximity == 1) last = "INMEDIATE";
		        				else if (myIBeaconInfo.iBeacon_last_proximity == 2) last = "NEAR";
		        				else if (myIBeaconInfo.iBeacon_last_proximity == 3) last = "FAR";

		        				String actual = "";
		        				if (myIBeaconInfo.parsediBeacon_proximity == 1) actual = "INMEDIATE";
		        				else if (myIBeaconInfo.parsediBeacon_proximity == 2) actual = "NEAR";
		        				else if (myIBeaconInfo.parsediBeacon_proximity == 3) actual = "FAR";
		        				
		        				String matchphrase2 = myIBeaconInfo.getUUID() + "-" + myIBeaconInfo.getMajor() + "-"  + myIBeaconInfo.getMinor() +"-from-"  + last + "-to-" + actual;
	                	    	
		        				//String matchphrase2 = index +"from"  + Integer.toString(myIBeaconInfo.iBeacon_last_proximity) + "to" + Integer.toString(myIBeaconInfo.parsediBeacon_proximity);
		        				AsyncTaskRunner runner = new AsyncTaskRunner();
	                			
		        				logToiBeacon("Calling ...");
	                	    	runner.execute(matchphrase2);
	                			myIBeaconInfo.iBeacon_last_proximity = myIBeaconInfo.parsediBeacon_proximity ;
		        			}
		        			else if( myIBeaconInfo.iBeacon_last_proximity == 0){
	                	    	myIBeaconInfo.iBeacon_last_proximity = myIBeaconInfo.parsediBeacon_proximity ;
		        			}
		        			myIBeaconInfo.iBeacon_proximity = 0;
		        			myIBeaconInfo.iBeacon_count = 0;
	            		}	
		        		myIBeaconInfo.iBeacon_count++;
		        		myList.set(index, myIBeaconInfo);
			        	runOnUiThread(new Runnable() {
			                public void run() {
			    				adapter.notifyDataSetChanged();
			                }
			            });
		        	}
		        }	
	        }
        });
        try {
            iBeaconManager.startRangingBeaconsInRegion(new Region("myRangingUniqueId", null, null, null));
        } catch (RemoteException e) {   }
    }			
   
    private void logToiBeacon (final String line) {
    	runOnUiThread(new Runnable() {
    	    public void run() {
    	    	TextView TextView3 = (TextView)RangingActivity.this
    					.findViewById(R.id.response);
    	    	TextView3.setText(line);            	
    	    }
    	});
    }
    private class AsyncTaskRunner extends AsyncTask<String, String, String>
	{
    	private CommonsHttpOAuthProvider provider;
    	private CommonsHttpOAuthConsumer consumer;
    	private String	resp;

		@Override
		protected String doInBackground(String... params)
		{
			try
			{
		        this.consumer = new CommonsHttpOAuthConsumer(myConstants.CONDUCTTR_CONSUMER_KEY,
		        		myConstants.CONDUCTTR_CONSUMER_SECRET);
				this.provider = new CommonsHttpOAuthProvider(
						myConstants.CONDUCTTR_REQUEST_URL,
						myConstants.CONDUCTTR_ACCESS_URL,
						myConstants.CONDUCTTR_AUTHORIZE_URL);

				HttpGet request = new HttpGet(myConstants.CONDUCTTR_BASE_URL + myConstants.CONDUCTTR_PROJECT_ID + "/" + params[0] + "?audience_phone=" + audience_phone);
				consumer.sign(request);
		        HttpClient httpClient = new DefaultHttpClient();
		        HttpResponse response = httpClient.execute(request);
		       
		        /*Response Parse */
		        InputStream data = response.getEntity().getContent();
		        BufferedReader bufferedReader = new BufferedReader(new InputStreamReader(data));
                String responeLine;
                StringBuilder responseBuilder = new StringBuilder();
                while ((responeLine = bufferedReader.readLine()) != null) {
                    responseBuilder.append(responeLine);
                }
                logToiBeacon("Response: " + responseBuilder.toString());
			}
			catch(Exception e)
			{
				e.printStackTrace();
				resp = e.getMessage();
                logToiBeacon(resp);
			}
			return resp;
		}
	}
    
    public class MyBaseAdapter extends BaseAdapter {  
    	ArrayList<IBeaconInfo> myList = new ArrayList<IBeaconInfo>(); 
    	LayoutInflater inflater; Context context;    
    	public MyBaseAdapter(Context context, ArrayList<IBeaconInfo> myList) {
    		this.myList = myList; 
    		this.context = context; 
    		inflater = LayoutInflater.from(this.context); // only context can also be used } - See more at: http://www.pcsalt.com/android/listview-using-baseadapter-android/#sthash.6ldx2dMq.dpuf
    	}
    	@Override public int getCount() { 
    		return myList.size(); 
    	}   
    	
    	@Override public Object getItem(int position) {
    		return myList.get(position); 
    	}   
    	@Override public long getItemId(int position) {
    		return 0; 
    	}
    	
        public View getView(int position, View convertView, ViewGroup parent) {
        	
        	if(convertView == null) convertView = inflater.inflate(R.layout.tupple_monitoring, null);

            TextView beacon_uuid = (TextView) convertView.findViewById(R.id.BEACON_uuid);
            TextView beacon_major = (TextView) convertView.findViewById(R.id.BEACON_major);
			TextView beacon_minor = (TextView) convertView.findViewById(R.id.BEACON_minor);
			TextView beacon_accuracy = (TextView) convertView.findViewById(R.id.BEACON_accuracy);
			TextView beacon_count = (TextView) convertView.findViewById(R.id.BEACON_count);
			TextView beacon_actual_proximity = (TextView) convertView.findViewById(R.id.BEACON_actual_proximity);
			TextView beacon_average_proximity = (TextView) convertView.findViewById(R.id.BEACON_average_proximity);
			TextView beacon_last_proximity = (TextView) convertView.findViewById(R.id.BEACON_last_proximity);

			beacon_uuid.setText("UUID: " + myList.get(position).getUUID());
			beacon_major.setText("Major: " + myList.get(position).getMajor());
			beacon_minor.setText(", Minor: " + myList.get(position).getMinor());
			beacon_accuracy.setText("Distance" + myList.get(position).iBeacon_accuracy);
			beacon_count.setText("Count: " + myList.get(position).iBeacon_count);
			beacon_actual_proximity.setText("Actual Proximity: " + myList.get(position).iBeacon_proximity);
			beacon_average_proximity.setText("Average Proximity: " + myList.get(position).iBeacon_proximity/(myList.get(position).iBeacon_count - 1));
			beacon_last_proximity.setText("Last Proximity: " + myList.get(position).iBeacon_last_proximity);
            return convertView;
        }
    }

    private int getIndexByUUID(String uuid) {
        for (int i = 0; i < myList.size(); i++) {
            if (myList.get(i)!=null && myList.get(i).getUUID().equals(uuid)) {
                return i;
            }
        }
        return -1;// not there is list
    }
	private void verifyBluetooth() {
		try {
			if (!IBeaconManager.getInstanceForApplication(this).checkAvailability()) {
				finish();
			}			
		}
		catch (RuntimeException e) {
			finish();
		}
	}	
 }