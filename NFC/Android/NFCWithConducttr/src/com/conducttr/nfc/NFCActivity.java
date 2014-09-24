package com.conducttr.nfc;
 
import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.util.Arrays;

import oauth.signpost.commonshttp.CommonsHttpOAuthConsumer;
import oauth.signpost.commonshttp.CommonsHttpOAuthProvider;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.IntentFilter.MalformedMimeTypeException;
import android.content.SharedPreferences;
import android.nfc.NdefMessage;
import android.nfc.NdefRecord;
import android.nfc.NfcAdapter;
import android.nfc.Tag;
import android.nfc.tech.Ndef;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Vibrator;
import android.preference.PreferenceManager;
import android.util.Log;
import android.widget.TextView;
import android.widget.Toast;
 
/**
 * Based on the Activity for reading data from an NDEF Tag by @author Ralf Wondratschek
 *
 */
public class NFCActivity extends Activity {
 
    public static final String MIME_TEXT_PLAIN = "text/plain";
    public static final String TAG = "NFCActivity";
 
    private TextView mTextView;
    private NfcAdapter mNfcAdapter;
    
	private Constants myConstants = new Constants();
	private SharedPreferences preferences ;
    private String audience_phone;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_nfc);
 
        mTextView = (TextView) findViewById(R.id.textView_explanation);
        
        mNfcAdapter = NfcAdapter.getDefaultAdapter(this);

        if (mNfcAdapter == null) {
            // Stop here, we definitely need NFC
            Toast.makeText(this, "This device doesn't support NFC.", Toast.LENGTH_LONG).show();
            finish();
            return;
 
        }
     
        if (!mNfcAdapter.isEnabled()) {
            mTextView.setText("NFC is disabled.");
        } else {
            mTextView.setText(R.string.explanation);
        }

        handleIntent(getIntent());
    }
     
    @Override
    protected void onResume() {
        super.onResume();
         
        /**
         * It's important, that the activity is in the foreground (resumed). Otherwise
         * an IllegalStateException is thrown. 
         */
        setupForegroundDispatch(this, mNfcAdapter);
    }
     
    @Override
    protected void onPause() {
        /**
         * Call this before onPause, otherwise an IllegalArgumentException is thrown as well.
         */
        stopForegroundDispatch(this, mNfcAdapter);
         
        super.onPause();
    }
     
    @Override
    protected void onNewIntent(Intent intent) { 
        /**
         * This method gets called, when a new Intent gets associated with the current activity instance.
         * Instead of creating a new activity, onNewIntent will be called. For more information have a look
         * at the documentation.
         * 
         * In our case this method gets called, when the user attaches a Tag to the device.
         */
		preferences = PreferenceManager.getDefaultSharedPreferences(this);
		audience_phone = preferences.getString("audience_phone","");
		audience_phone = audience_phone.trim();

		handleIntent(intent);
    }
     
    private void handleIntent(Intent intent) {
        String action = intent.getAction();
        
        if (NfcAdapter.ACTION_NDEF_DISCOVERED.equals(action)) {
             
            String type = intent.getType();
            if (MIME_TEXT_PLAIN.equals(type)) {
     
                Tag tag = intent.getParcelableExtra(NfcAdapter.EXTRA_TAG);
                new NdefReaderTask().execute(tag);
                 
            } else {
                Log.d(TAG, "Wrong mime type: " + type);
            }
        } else if (NfcAdapter.ACTION_TECH_DISCOVERED.equals(action)) {
             
            // In case we would still use the Tech Discovered Intent
            Tag tag = intent.getParcelableExtra(NfcAdapter.EXTRA_TAG);
            String[] techList = tag.getTechList();
            String searchedTech = Ndef.class.getName();
             
            for (String tech : techList) {
                if (searchedTech.equals(tech)) {
                    new NdefReaderTask().execute(tag);
                    break;
                }
            }
        }
    }
     
    /**
     * @param activity The corresponding {@link Activity} requesting the foreground dispatch.
     * @param adapter The {@link NfcAdapter} used for the foreground dispatch.
     */
    public static void setupForegroundDispatch(final Activity activity, NfcAdapter adapter) {
        final Intent intent = new Intent(activity.getApplicationContext(), activity.getClass());
        intent.setFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP);
 
        final PendingIntent pendingIntent = PendingIntent.getActivity(activity.getApplicationContext(), 0, intent, 0);
 
        IntentFilter[] filters = new IntentFilter[1];
        String[][] techList = new String[][]{};
 
        // Notice that this is the same filter as in our manifest.
        filters[0] = new IntentFilter();
        filters[0].addAction(NfcAdapter.ACTION_NDEF_DISCOVERED);
        filters[0].addCategory(Intent.CATEGORY_DEFAULT);
        try {
            filters[0].addDataType(MIME_TEXT_PLAIN);
        } catch (MalformedMimeTypeException e) {
            throw new RuntimeException("Check your mime type.");
        }
         
        adapter.enableForegroundDispatch(activity, pendingIntent, filters, techList);
    }
 
    /**
     * @param activity The corresponding {@link BaseActivity} requesting to stop the foreground dispatch.
     * @param adapter The {@link NfcAdapter} used for the foreground dispatch.
     */
    public static void stopForegroundDispatch(final Activity activity, NfcAdapter adapter) {
        adapter.disableForegroundDispatch(activity);
    }
    
    private class NdefReaderTask extends AsyncTask<Tag, Void, String> {
    	private CommonsHttpOAuthProvider provider;
    	private CommonsHttpOAuthConsumer consumer;
    	private String	resp; 
    	
        @Override
        protected String doInBackground(Tag... params) {
            Tag tag = params[0];
             
            Ndef ndef = Ndef.get(tag);
            if (ndef == null) {
                // NDEF is not supported by this Tag. 
                return null;
            }
     
            NdefMessage ndefMessage = ndef.getCachedNdefMessage();
     
            NdefRecord[] records = ndefMessage.getRecords();
            for (NdefRecord ndefRecord : records) {
                if (ndefRecord.getTnf() == NdefRecord.TNF_WELL_KNOWN && Arrays.equals(ndefRecord.getType(), NdefRecord.RTD_TEXT)) {
                    this.consumer = new CommonsHttpOAuthConsumer(myConstants.CONDUCTTR_CONSUMER_KEY,
    		        		myConstants.CONDUCTTR_CONSUMER_SECRET);
    				this.provider = new CommonsHttpOAuthProvider(
    						myConstants.CONDUCTTR_REQUEST_URL,
    						myConstants.CONDUCTTR_ACCESS_URL,
    						myConstants.CONDUCTTR_AUTHORIZE_URL);
                	try {
                		String tag_text = readText(ndefRecord);
                		preferences = PreferenceManager.getDefaultSharedPreferences(NFCActivity.this);
                        audience_phone = preferences.getString("audience_phone","0");
                		audience_phone = audience_phone.trim();
                		
                		
                		HttpGet request = new HttpGet(myConstants.CONDUCTTR_BASE_URL + myConstants.CONDUCTTR_PROJECT_ID + "/" + tag_text + "?audience_phone=" + audience_phone);
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
                        Vibrator v = (Vibrator) NFCActivity.this.getSystemService(Context.VIBRATOR_SERVICE);
        		        v.vibrate(500);	
        		        
                        return "TAG Content: " + readText(ndefRecord) +"\n\n Conducttr Response: \n"+ responseBuilder.toString();
                            
                    } catch (Exception e) {
                        Log.e(TAG, "Unsupported Encoding", e);
                        return e.toString();
                    }
                }
            }

            return null;
        }
         
        private String readText(NdefRecord record) throws UnsupportedEncodingException {
            /*
             * See NFC forum specification for "Text Record Type Definition" at 3.2.1 
             * 
             * http://www.nfc-forum.org/specs/
             * 
             * bit_7 defines encoding
             * bit_6 reserved for future use, must be 0
             * bit_5..0 length of IANA language code
             */
     
            byte[] payload = record.getPayload();
     
            // Get the Text Encoding
            String textEncoding = ((payload[0] & 128) == 0) ? "UTF-8" : "UTF-16";
     
            // Get the Language Code
            int languageCodeLength = payload[0] & 0063;
             
            // String languageCode = new String(payload, 1, languageCodeLength, "US-ASCII");
            // e.g. "en"
             
            // Get the Text
            return new String(payload, languageCodeLength + 1, payload.length - languageCodeLength - 1, textEncoding);
        }
         
        @Override
        protected void onPostExecute(String result) {
            if (result != null) {
                mTextView.setText("TAG read!  \n\n" + result);
            }
        }
    }
}