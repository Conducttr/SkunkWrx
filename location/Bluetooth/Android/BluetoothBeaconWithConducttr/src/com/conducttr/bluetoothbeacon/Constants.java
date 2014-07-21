package com.conducttr.bluetoothbeacon;

import java.util.ArrayList;


public class Constants {

	
	/*------------------------------- Edit the information below ------------------------*/        

	/* Conducttr Configuration */
    public String CONDUCTTR_CONSUMER_KEY = "MY_CONDUCTTR_CONSUMER_KEY";
    public String CONDUCTTR_CONSUMER_SECRET = "MY_CONDUCTTR_CONSUMER_SECRET";
    public String CONDUCTTR_PROJECT_ID = "MY_CONDUCTTR_PROJECT_ID";
  
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
