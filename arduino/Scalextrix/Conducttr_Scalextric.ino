/*

This sketch polls a Conducttr project and uses the returned data to determine how many laps a Scalextrix car should make around a track.

Uses a servo motor attached to a Scaletrix controller to drive the car.

*/

// libraries
#include <GSM.h>
#include <Servo.h>

// SERVO VARIABLES

Servo myServo;
int position = 0 ;
int start_position = 105; // this is the starting angle of the servo. Need to adjust for physical build of servo + controller
int max_position = 130; //don't want to drive the car too fast
int delay_val =25;  // Used for the "soft start" function so that car doesn't lurch from 0 to 60 :)
int lap_delay = 4100; // 1 tweet gets the car to go once around the circuit. This how long the servo is in the max_position

// LED controls
// note: Servo is on 9
int GSMNetworkLED = 8;

// Some of this is for future expansion to have a proper Json parser. Right now I'm faking it because I only need one variable
  int curly = 0; // number of curly brackets found
  boolean go4it = false;  
  boolean done = false; 
  String LatestJson = "" ;
  String jsonString = "";
  char jsonCharArray[] = "";

  int charCount = 0;
  int Tweets = 0;
  int this_num = 0;
  int that_num = 0;
  int loopy = 0;
  int cargo = 0;
  
  
// PIN Number
#define PINNUMBER ""

// APN data
#define GPRS_APN       "bluevia.movistar.es" // replace your GPRS APN
#define GPRS_LOGIN     ""    // replace with your GPRS login
#define GPRS_PASSWORD  "" // replace with your GPRS password

// initialize the library instance
GSMClient client;
GPRS gprs;
GSM gsmAccess; // include a 'true' parameter for debug enabled
GSM_SMS sms;

//
// REPLACE "xxx" and "yyyy" with your project ID number and consumer_key
//
char path[] = "/v1/project/xxx/unauth/tweet_queue?consumer_key=yyyy";
char server[] = "api.conducttr.com";
unsigned long lastConnectionTime = 0;          // last time you connected to the server, in milliseconds
boolean lastConnected = false;                 // state of the connection last time through the main loop
int getInterval = 10000;  // delay between updates, in milliseconds


void setup()
{
  // Setup the LEDs
  pinMode(GSMNetworkLED, OUTPUT);  
  
  // Turn on the "attempting network connection LED
  digitalWrite(GSMNetworkLED, HIGH); 
  
  // initialize serial communications
  Serial.begin(9600);
  Serial.println("Trying to get a GSM connection...");
  // connection state
  boolean notConnected = true;
  
  // Start GSM shield
  // If your SIM has PIN, pass it as a parameter of begin() in quotes
  while(notConnected)
  {
    if((gsmAccess.begin(PINNUMBER)==GSM_READY) &
        (gprs.attachGPRS(GPRS_APN, GPRS_LOGIN, GPRS_PASSWORD)==GPRS_READY))
      notConnected = false;
    else
    {
      Serial.println("Not connected");
      delay(1000);
    }
  }

  //  get the servo ready!
  myServo.attach(9);
  softmove(start_position);  // start in the stop position
  Serial.println("connecting... leaving setup...");
  
  // Turn OFF the "attempting network connection LED"
  digitalWrite(GSMNetworkLED, LOW); 
}


void loop() {
 

  if (client.available()) {
    char c = client.read();
    
    // Comment out the printing when debugging complete
    //Serial.print(c);
    
    // check for data
    if (c == '{') {    // ok, we're in business...
        ++curly;
    }
    if (curly == 1){  // start saving if this is the first { seen
      go4it = true;    
    }

    if (go4it) jsonString += (c);  // add this character to the data string

    if (c == '}') {
        --curly;
    }
    if (go4it && curly ==0){  // if curly is 0 then we've got to the end of the data
      
      Serial.println();
      Serial.print("DATA FOUND: ");
      Serial.println(jsonString);
      Serial.println();
      
      int start = jsonString.indexOf("tweets");
      if (start>0)
       {
          // get the Tweet number. This is a hack in replace of a proper Json parser.
          charCount = start+9;  // add length of the attribute text
          //Serial.println(charCount);
          String Tweets = "";
          do
          {
            // gather the characters until the numbers end
            c = jsonString.charAt(charCount);
            Tweets += c;
            charCount ++;
          } while (jsonString.charAt(charCount)!= '"');
          Serial.print("Tweets so far:");
          this_num = Tweets.toInt();
          Serial.println(this_num);
          
                   
          // do I need to drive the car?
          cargo = this_num - that_num;
          Serial.print("Laps:");
          Serial.println(cargo);
          if (cargo >0)
          {
           loopy = 1;  // reset the back off period back to 10000
           getInterval = 10000;         
           do {
               softmove(max_position);  // move the car!!!
               delay(lap_delay);
               Serial.print(".");       // write a dot for every lap just to show something happening :)
               cargo --;         
           } while (cargo >0);
           // stop the car
            softmove(start_position);
            that_num = this_num;  // swap
            Serial.println();
          }// end if
          else
          {
               //increase the back off period. This prevents Arduino constantly polling Conducttr if there's no activity
               if (loopy<4) //was 4
              {
                getInterval = 10000*loopy;
                Serial.print("get interval:");
                Serial.println(getInterval);
                Serial.print("backoff multiple:");
                Serial.println(loopy);
                loopy ++;
               }
          } // end else
      } // tweets found
       
      // reset all variables
      client.stop();
      done = true;
      go4it = false;
      jsonString = "";
       
    }
  }

 
  // if you're not connected, and time has passed since
  // your last connection, then connect again and send data:
  if(!client.connected() && (millis() - lastConnectionTime > getInterval)) 
  {
    // back off with each loop through the data
    // if data is found then loopy is reset to 1
    
    /*

    */
    
    httpRequest();  // GET the data
       
  }
  // store the state of the connection for next time through
  // the loop:
  lastConnected = client.connected();
}

// this method makes a HTTP connection to the server:
void httpRequest() {
  Serial.println("making GET request...");
  // if there's a successful connection:
  if (client.connect(server, 80)) {
    Serial.println("connecting..."); 
    // send the HTTP PUT request:
    client.print("GET ");
    client.print(path);
    client.println(" HTTP/1.1");
    client.println("Host: api.conducttr.com");
    client.println("Connection: close");
    client.println();

    // note the time that the connection was made:
    lastConnectionTime = millis(); 
    
  } 
  else {
    // if you couldn't make a connection:
    Serial.println("connection failed");
    Serial.println("disconnecting.");
    client.stop();
  }
 }

//
// SOFT START for Scalextrix controller
//
// gradually move the servo into position to avoid abrupt start and stop
void softmove(int new_position){

int position;
int i;

i= myServo.read();

if (new_position> i) 
{
	
	for(position = i ; position < new_position ; position++){
	myServo.write(position);
	delay(delay_val);
	}	
}
	else 
{
	for(position = i ; position > new_position ; position--){
	myServo.write(position);
	delay(delay_val);
	}
}
	
}
