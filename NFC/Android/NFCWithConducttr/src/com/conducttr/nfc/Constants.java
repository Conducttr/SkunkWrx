package com.conducttr.nfc;

public class Constants {

	
	/*------------------------------- Edit the information below ------------------------*/        

	/* Conducttr Configuration */
    public String CONDUCTTR_CONSUMER_KEY = "YOUR_CONDUCTTR_CONSUMER_KEY_GOES_HERE ";
    public String CONDUCTTR_CONSUMER_SECRET = "YOUR_CONDUCTTR_CONSUMER_SECRET_GOES_HERE ";
    public String CONDUCTTR_PROJECT_ID = "YOUR_CONDUCTTR_PROJECT_ID_GOES_HERE";
    
    /*------------------------------- Edit the information above ------------------------*/       
    
    public String CONDUCTTR_BASE_URL = "https://api.conducttr.com/v1/project/";

	public String CONDUCTTR_REQUEST_URL = "https://my.conducttr.com/oauth/request-token";
    public String CONDUCTTR_AUTHORIZE_URL = "https://my.conducttr.com/oauth/authorize";
    public String CONDUCTTR_ACCESS_URL = "https://my.conducttr.com/oauth/access-token";

	public Constants (){
	}
}