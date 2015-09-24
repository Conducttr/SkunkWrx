using UnityEngine;
using System.Collections;
using System;
using System.Collections.Generic;
using OAuth3;
using SimpleJSON;
using System.Net;
using System.IO;
using System.Text;
using UnityEngine.UI;

public class ConducttrGETattributes : MonoBehaviour {
	
	private string audience_email = "";
	
	void OnGUI() {
		
		audience_email = GUI.TextField ( new Rect (10, 10, 100, 20), audience_email,50 ); 

		if ( GUI.Button ( new Rect (10, 60, 100, 20) , "Get Attributes" ) ){ //just a button
			getAttributes(audience_email);
			
		}
		if (Event.current.keyCode == KeyCode.Return) {
			
			getAttributes(audience_email);
		}
	}
	
	public WWW GET (string url){
		
		WWW www = new WWW (url);
		StartCoroutine (WaitForRequest (www));
		return www; 
	}
	
	/* Response from Conducttr */
	private IEnumerator WaitForRequest(WWW www){
		yield return www;
		
		// check for errors
		if (www.error == null){
			Debug.Log("WWW Ok!: " + www.text);
			
			/* Parse the JSON response and extract the attributes values */
			JSONNode results = JSON.Parse(www.text);

			var  = results["results"][0]["attribute1"].Value;
			var attribute2 = results["results"][1]["attribute2"].Value;
			var attribute3 = results["results"][2]["attribute3"].Value;
		
			Debug.Log (attribute1);
			Debug.Log (attribute2);	
			Debug.Log (attribute3);


		} 
		else {
			Debug.Log("WWW Error: "+ www.error);
		}    
	}
	
	
	public void getAttributes(string audience_email) {
		try{
			System.Net.ServicePointManager.ServerCertificateValidationCallback += (s,ce,ca,p) => true;

			/*------------------------------- Edit the information below ------------------------*/        

			string CONDUCTTR_PROJECT_ID = "";
			string CONDUCTTR_CONSUMER_KEY = "";
			string CONDUCTTR_CONSUMER_SECRET = "";
			string CONDUCTTR_CONSUMER_ACCESS_TOKEN = "";
			string CONDUCTTR_CONSUMER_ACCESS_TOKEN_SECRET = "";
			string CONDUCTTR_API_GET_METHOD = "";
			
			/*------------------------------- Edit the information above ------------------------*/    


			Uri URL = new Uri("https://api.conducttr.com/v1/project/" + CONDUCTTR_PROJECT_ID + "/" + CONDUCTTR_API_GET_METHOD + "?audience_email=" + audience_email);
			
			var config = new OAuthConfig() { ConsumerKey=CONDUCTTR_CONSUMER_KEY, ConsumerSecret=CONDUCTTR_CONSUMER_SECRET };
			OAuthAuthorizer auth = new OAuthAuthorizer(config);
			auth.AccessToken = CONDUCTTR_CONSUMER_ACCESS_TOKEN;
			auth.AccessTokenSecret = CONDUCTTR_CONSUMER_ACCESS_TOKEN_SECRET;
			
			//string json = "";
			WWW www=GET(URL.ToString()+"&"+OAuthAuthorizer.AuthorizeRequest2(config, CONDUCTTR_CONSUMER_ACCESS_TOKEN, CONDUCTTR_CONSUMER_ACCESS_TOKEN_SECRET, "GET", URL, null));
			
		}
		catch(Exception e){
			Debug.Log (e.Message + " : " + e.StackTrace);
			//status.text = e.Message;
		}
		
	}

	
}
