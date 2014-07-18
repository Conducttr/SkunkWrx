package com.conducttr.bluetoothbeacon;

import java.util.ArrayList;


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
    public ArrayList<IBeaconInfo> myList = new ArrayList<IBeaconInfo>();
	public IBeaconInfo IBeacon0 = new IBeaconInfo ("test",0,0);
	public String CONDUCTTR_REQUEST_URL = "https://my.conducttr.com/oauth/request-token";
    public String CONDUCTTR_AUTHORIZE_URL = "https://my.conducttr.com/oauth/authorize";
    public String CONDUCTTR_ACCESS_URL = "https://my.conducttr.com/oauth/access-token";
    public String PREFS_NAME = "com.conducttr.ibeaconwithconducttr";

	public Constants (){
		myList.add(IBeacon0);
	}
}