package com.conducttr.nfc;

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

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

public class MainActivity extends Activity {
	
	private EditText audience_phone ; 
	private Button login ;
	private SharedPreferences preferences ;
	private Constants myConstants = new Constants();
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
		audience_phone = (EditText)findViewById(R.id.audience_phone);
		login = (Button)findViewById(R.id.button1);
				
		preferences = PreferenceManager.getDefaultSharedPreferences(this);
		audience_phone.setText(preferences.getString("audience_phone",""));

		if (preferences.getString("logged", "").toString().equals("logged")) {
			Intent i = new Intent(MainActivity.this,NFCActivity.class);
			startActivity(i);
		}
		
		login.setOnClickListener(new View.OnClickListener() {
			
			public void onClick(View arg0) {
				// TODO Auto-generated method stub
				if(!audience_phone.getText().equals("")){
					
					SharedPreferences.Editor editor = preferences.edit();
					editor.putString("audience_phone", audience_phone.getText().toString());
					editor.putString("logged", "logged");
					editor.commit();

					Intent i = new Intent(MainActivity.this, NFCActivity.class);
					AsyncTaskRunner runner = new AsyncTaskRunner();
        			runner.execute();
					startActivity(i);						
				}
				else{
					Toast.makeText(MainActivity.this,"Please enter both the fields.", Toast.LENGTH_SHORT).show();
				}
		
			}
		});

	}
	@Override 
    protected void onResume() {
    	super.onResume();
    }
	
    private class AsyncTaskRunner extends AsyncTask<String, String, String>{
    	private CommonsHttpOAuthProvider provider;
    	private CommonsHttpOAuthConsumer consumer;
    	  
    	private String	resp;

		@Override
		protected String doInBackground(String... params){
			try{

		        this.consumer = new CommonsHttpOAuthConsumer(myConstants.CONDUCTTR_CONSUMER_KEY,
		        		myConstants.CONDUCTTR_CONSUMER_SECRET);

				this.provider = new CommonsHttpOAuthProvider(
		                myConstants.CONDUCTTR_REQUEST_URL,
		                myConstants.CONDUCTTR_ACCESS_URL,
		                myConstants.CONDUCTTR_AUTHORIZE_URL);
				
				
				String url = myConstants.CONDUCTTR_BASE_URL + myConstants.CONDUCTTR_PROJECT_ID + "/registration" ;

				HttpPost request = new HttpPost(url);
				List<NameValuePair> params1 = new ArrayList<NameValuePair>();
		  		params1.add(new BasicNameValuePair("audience_phone", audience_phone.getText().toString() ));

		  		request.setEntity(new UrlEncodedFormEntity(params1));
				
				consumer.sign(request);
		        HttpClient httpClient = new DefaultHttpClient();
		        HttpResponse response = httpClient.execute(request);
		        resp="okidoki";
		       
			}
			catch(Exception e){
				e.printStackTrace();
				resp = e.getMessage();
			}
			return resp;
		}
	}
}