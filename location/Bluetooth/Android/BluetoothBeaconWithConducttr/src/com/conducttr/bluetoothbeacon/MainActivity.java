package com.conducttr.bluetoothbeacon;

import java.util.ArrayList;
import java.util.List;

import oauth.signpost.commonshttp.CommonsHttpOAuthConsumer;
import oauth.signpost.commonshttp.CommonsHttpOAuthProvider;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;


import com.radiusnetworks.ibeacon.IBeaconManager;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.telephony.TelephonyManager;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.Toast;

public class MainActivity extends Activity {
	
	EditText audience_first_name , audience_phone ; 
	Button login,reset ;
	CheckBox remember;
	SharedPreferences preferences ;
	Constants myConstants = new Constants();
	String PREFS_NAME = "com.conducttr.bluetoothbeacon";
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
	
		audience_first_name = (EditText)findViewById(R.id.editText1);
		audience_phone = (EditText)findViewById(R.id.editText2);
		login = (Button)findViewById(R.id.button1);
		remember = (CheckBox)findViewById(R.id.checkBox1);
		
		verifyBluetooth();
		TelephonyManager tMgr =(TelephonyManager)this.getSystemService(Context.TELEPHONY_SERVICE);
		audience_phone.setText(tMgr.getLine1Number());
		preferences = getSharedPreferences(myConstants.PREFS_NAME, Context.MODE_PRIVATE);

		if (preferences.getString("logged", "").toString().equals("logged")) 
		{
			Intent i = new Intent(MainActivity.this,RangingActivity.class);
			i.putExtra("audience_first_name",preferences.getString("audience_first_name", "").toString());
			i.putExtra("audience_phone",preferences.getString("audience_phone", "").toString());
			i.putExtra("CHECK", true);
			startActivity(i);
			
		}
		
		login.setOnClickListener(new View.OnClickListener() {
			
			public void onClick(View arg0) {
				// TODO Auto-generated method stub
				if(!audience_first_name.getText().toString().equals("") &&  (!audience_phone.getText().equals("")))
				{
					if(remember.isChecked())
					{
						SharedPreferences.Editor editor = preferences.edit();
						editor.putString("audience_first_name", audience_first_name.getText().toString());
						editor.putString("audience_phone", audience_phone.getText().toString());
						editor.putString("logged", "logged");
						editor.commit();
					}
					Intent i = new Intent(MainActivity.this,RangingActivity.class);
					i.putExtra("audience_first_name", audience_first_name.getText().toString());
					i.putExtra("audience_phone", audience_phone.getText().toString());
					i.putExtra("CHECK", remember.isChecked());
					AsyncTaskRunner runner = new AsyncTaskRunner();
        			runner.execute();
					startActivity(i);						
				}
				else
				{
					Toast.makeText(MainActivity.this,"Please enter both the fields.", Toast.LENGTH_SHORT).show();
				}
		
			}
		});

	}
	@Override 
    protected void onResume() {
    	super.onResume();
    	verifyBluetooth();
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
				
				
				String url = myConstants.CONDUCTTR_BASE_URL + myConstants.CONDUCTTR_PROJECT_ID + "/" ;

				HttpPost request = new HttpPost(url);
				List<NameValuePair> params1 = new ArrayList<NameValuePair>();
		  		params1.add(new BasicNameValuePair("audience_phone", audience_phone.getText().toString() ));
		  		params1.add(new BasicNameValuePair("audience_first_name", audience_first_name.getText().toString() ));

		  		request.setEntity(new UrlEncodedFormEntity(params1));
				
				consumer.sign(request);
		        HttpClient httpClient = new DefaultHttpClient();
		        HttpResponse response = httpClient.execute(request);
		       
			}
			catch(Exception e)
			{
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