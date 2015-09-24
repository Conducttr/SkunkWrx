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
using Nancy.Helpers;

public class ConducttrPOSTattributes : MonoBehaviour {
	
	private string audience_email = "";
	
	private string attribute1 = "1";
	private string attribute2 = "2";
	private string attribute3 = "2";
		

	void OnGUI() {
		
		audience_email = GUI.TextField ( new Rect (90, 0, 200, 30), audience_email,25 ); 
		
		attribute1 = GUI.TextField ( new Rect (90, 30, 100, 20), attribute1,25 ); 
		attribute1 = GUI.TextField ( new Rect (90, 50, 100, 20), attribute2,25 ); 
		attribute1 = GUI.TextField ( new Rect (90, 70, 100, 20), attribute3,25 ); 



		if ( GUI.Button ( new Rect (90, 130, 200, 100) , "Set Attributes" ) ){ //just a button
			setAttributes(audience_email,analSexSelected,askHIVSelected,completedSexActs,avatarHealth,communityHealth,condoms,attempts,avatarRisk,partnerRisk,avatarName,partnerName);
			
		}
		if (Event.current.keyCode == KeyCode.Return) {
			
			setAttributes(audience_email,analSexSelected,askHIVSelected,completedSexActs,avatarHealth,communityHealth,condoms,attempts,avatarRisk,partnerRisk,avatarName,partnerName);
		}
	}
	
	public WWW POST(string url, Dictionary<string,string> post){
		WWWForm form = new WWWForm();
		foreach(KeyValuePair<String,String> post_arg in post)
		{
			form.AddField(post_arg.Key, post_arg.Value);
		}
		WWW www = new WWW(url, form);
		
		StartCoroutine(WaitForRequest(www));
		return www; 
	}
	
	/* Response from Conducttr */
	private IEnumerator WaitForRequest(WWW www)
	{
		yield return www;
		
		// check for errors
		if (www.error == null)
		{
			Debug.Log("WWW Ok!: " + www.text);
		} else {
			Debug.Log("WWW Error: "+ www.error);
		}    
	}
	
	public void setAttributes(string audience_email, string analSexSelected, string askHIVSelected, string completedSexActs, string avatarHealth, string communityHealth, string condoms, string attempts, string avatarRisk, string partnerRisk, string avatarName, string partnerName){
		try{
			//ServicePointManager.ServerCertificateValidationCallback =ValidateServerCertficate;
			System.Net.ServicePointManager.ServerCertificateValidationCallback += (s,ce,ca,p) => true;
			
			/*------------------------------- Edit the information below ------------------------*/        

			string CONDUCTTR_PROJECT_ID = "";
			string CONDUCTTR_CONSUMER_KEY = "";
			string CONDUCTTR_CONSUMER_SECRET = "";
			string CONDUCTTR_CONSUMER_ACCESS_TOKEN = "";
			string CONDUCTTR_CONSUMER_ACCESS_TOKEN_SECRET = "";
			
			string CONDUCTTR_API_POST_METHOD = "";
			
			/*------------------------------- Edit the information above ------------------------*/    

		
			
			
			Uri URL = new Uri("https://api.conducttr.com/v1/project/" + CONDUCTTR_PROJECT_ID + "/" + CONDUCTTR_API_POST_METHOD);
			
			Dictionary<string,string> requestParameters=new Dictionary<string,string>();
			requestParameters["audience_email"]=audience_email;
			
			/* Add the attributes to the API Call */
			requestParameters["attribute1"]=attribute1;
			requestParameters["attribute2"]=attribute2;
			requestParameters["attribute3"]=attribute3;
		


			var config = new OAuthConfig() { ConsumerKey=CONDUCTTR_CONSUMER_KEY, ConsumerSecret=CONDUCTTR_CONSUMER_SECRET };
			OAuthAuthorizer auth = new OAuthAuthorizer(config);
			auth.AccessToken = CONDUCTTR_CONSUMER_ACCESS_TOKEN;
			auth.AccessTokenSecret = CONDUCTTR_CONSUMER_ACCESS_TOKEN_SECRET;
			
			//prepare request parameters
			string parameters="";
			foreach(string key in requestParameters.Keys) parameters+=key+"="+requestParameters[key]+"&";
			if (requestParameters.Keys.Count>0) parameters.Substring(0,parameters.Length-1);
			Dictionary<string, string> postParameters=OAuthAuthorizer.AuthorizeRequest3(config, CONDUCTTR_CONSUMER_ACCESS_TOKEN, CONDUCTTR_CONSUMER_ACCESS_TOKEN_SECRET, "POST",URL, parameters);
			
			foreach(string key in requestParameters.Keys) postParameters[key]=requestParameters[key];
			
			WWW www=POST(URL.ToString(),postParameters);

		}
		catch(Exception e){
			Debug.Log (e.Message + " : " + e.StackTrace);
			status.text = e.Message + " : " + e.StackTrace;
		}
		
	}
}
