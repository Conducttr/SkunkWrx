package com.conducttr.bluetoothbeacon;

public class IBeaconInfo  {
    private String UUID;
    private int Major;
    private int Minor;
  
    public double iBeacon_accuracy;
    public double iBeacon_proximity;
    public double iBeacon_proximity_sum;

    public int iBeacon_last_proximity;
    public int iBeacon_count;
    public int parsediBeacon_proximity;

    public IBeaconInfo(String uuid, int major, int minor ) {
    	UUID = uuid;
    	Major= major;
    	Minor= minor;
    	iBeacon_proximity=0;
    	iBeacon_proximity_sum=0;
    	iBeacon_last_proximity=0;
    	iBeacon_count=1;
    	parsediBeacon_proximity=0;
    }
    public String getUUID(){
    	return UUID;
    }
    public int getMajor(){
    	return Major;
    }
    public int getMinor(){
    	return Minor;
    }
    public int getCount(){
    	return iBeacon_count;
    }
    public double getProximity(){
    	return iBeacon_proximity;
    }
}
