package com.conducttr.bluetoothbeacon;

import java.util.ArrayList;
import java.util.List;

import oauth.signpost.commonshttp.CommonsHttpOAuthConsumer;
import oauth.signpost.commonshttp.CommonsHttpOAuthProvider;

import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.radiusnetworks.ibeacon.IBeaconManager;

public class MainActivity extends Activity {
	
	private EditText audience_phone; 
	private Button login;
	private SharedPreferences preferences ;
	private Constants myConstants = new Constants();
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
		audience_phone = (EditText)findViewById(R.id.editText2);
		login = (Button)findViewById(R.id.button1);
		
		verifyBluetooth();
		
		preferences = PreferenceManager.getDefaultSharedPreferences(this);
		audience_phone.setText(preferences.getString("audience_phone",""));

		if (preferences.getString("logged", "").toString().equals("logged")) {
			Intent i = new Intent(MainActivity.this,RangingActivity.class);
			startActivity(i);
		}
		
		login.setOnClickListener(new View.OnClickListener() {
			
			public void onClick(View arg0) {
				if (audience_phone.getText().toString().equals("")){
					Toast.makeText(MainActivity.this,"Please fill the field.", Toast.LENGTH_SHORT).show();
				}
				else{
					SharedPreferences.Editor editor = preferences.edit();
					editor.putString("audience_phone", audience_phone.getText().toString());
					editor.putString("logged", "logged");
					editor.commit();
					
					Intent i = new Intent(MainActivity.this,RangingActivity.class);
					i.putExtra("audience_phone", audience_phone.getText().toString());
					AsyncTaskRunner runner = new AsyncTaskRunner();
        			runner.execute();
					startActivity(i);	
				}
			}
		});
	}
	@Override 
    protected void onResume() {
    	super.onResume();
    	verifyBluetooth();
    }
    private class AsyncTaskRunner extends AsyncTask<String, String, String>{
    	private CommonsHttpOAuthConsumer consumer;
    	  
    	private String	resp;

		@Override
		protected String doInBackground(String... params){
			try{
		        this.consumer = new CommonsHttpOAuthConsumer(myConstants.CONDUCTTR_CONSUMER_KEY,
		        		myConstants.CONDUCTTR_CONSUMER_SECRET);
				this.consumer.setTokenWithSecret(myConstants.CONDUCTTR_ACCESS_TOKEN, myConstants.CONDUCTTR_ACCESS_TOKEN_SECRET);	

				String url = myConstants.CONDUCTTR_BASE_URL + myConstants.CONDUCTTR_PROJECT_ID + "/" ;
				HttpPost request = new HttpPost(url);
				List<NameValuePair> params1 = new ArrayList<NameValuePair>();
		  		params1.add(new BasicNameValuePair("audience_phone", audience_phone.getText().toString() ));
		  		
		  		request.setEntity(new UrlEncodedFormEntity(params1));
				
				consumer.sign(request);
		        HttpClient httpClient = new DefaultHttpClient();
		        //HttpResponse response = httpClient.execute(request);
		        httpClient.execute(request);
			}
			catch(Exception e){
				e.printStackTrace();
				resp = e.getMessage();
			}
			return resp;
		}
	}
	private void verifyBluetooth() {

		try {
			if (!IBeaconManager.getInstanceForApplication(this).checkAvailability()) {
				final AlertDialog.Builder builder = new AlertDialog.Builder(this);
				builder.setTitle("Bluetooth not enabled");			
				builder.setMessage("Please enable bluetooth in settings and restart this application.");
				builder.setPositiveButton(android.R.string.ok, null);
				
				builder.setOnDismissListener(new DialogInterface.OnDismissListener() {
					@Override
					public void onDismiss(DialogInterface dialog) {
						finish();
			            System.exit(0);					
					}					
				});
				
				builder.show();
			}			
		}
		catch (RuntimeException e) {
			final AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle("Bluetooth LE not available");			
			builder.setMessage("Sorry, this device does not support Bluetooth LE.");
			builder.setPositiveButton(android.R.string.ok, null);
			builder.setOnDismissListener(new DialogInterface.OnDismissListener() {
				@Override
				public void onDismiss(DialogInterface dialog) {
					finish();
		            System.exit(0);					
				}	
			});
			builder.show();
		}
	}	
}