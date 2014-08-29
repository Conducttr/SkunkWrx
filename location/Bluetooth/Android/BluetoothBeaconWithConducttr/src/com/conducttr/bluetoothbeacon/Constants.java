package com.conducttr.bluetoothbeacon;



public class Constants {

	
	/*------------------------------- Edit the information below ------------------------*/        

	/* Conducttr Configuration */
    public String CONDUCTTR_CONSUMER_KEY = "6f24dc98bba64107e0aba8c6c99eefa6053be6919";
    public String CONDUCTTR_CONSUMER_SECRET = "ed745694033a19a41fa11b01776ff56f";
    public String CONDUCTTR_PROJECT_ID = "1757";
  
    /*Number of samples */
    public int COUNT = 5 ;
 
    /*------------------------------- Edit the information above ------------------------*/       
    
    public String CONDUCTTR_BASE_URL = "https://api.conducttr.com/v1/project/";
	public String CONDUCTTR_REQUEST_URL = "https://my.conducttr.com/oauth/request-token";
    public String CONDUCTTR_AUTHORIZE_URL = "https://my.conducttr.com/oauth/authorize";
    public String CONDUCTTR_ACCESS_URL = "https://my.conducttr.com/oauth/access-token";

	public Constants (){
	}
}