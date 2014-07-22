<?php
/* OAUTH Library  - Edit this if you change the path of the OAuth folder */ 
include_once "Oauth/OAuthStore.php";
include_once "Oauth/OAuthRequester.php";


/*------------------------------- Edit the information below ------------------------*/        

/* MySQL DATABASE CONFIGURATION */

define("MYSQL_DBHOST", "MY_MYSQL_HOST");
define("MYSQL_DBNAME", "MY_MYSQL_DATABASE_NAME");
define("MYSQL_USER", "MY_MYSQL_USER");
define("MYSQL_PASS", "MY_MYSQL_PASSWORD");;

/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE FROM THE API - CONSULT IF YOUR HOSTING ALLOW THAT */
define("MYSQL_ROOT_DBHOST", "MY_MYSQL_ROOT_HOST");
define("MYSQL_ROOT_USER", "MY_MYSQL_ROOT_USER");
define("MYSQL_ROOT_PASS", "MY_MYSQL_ROOT_PASS");
	
/* CONDUCTTR OAUTH CONFIGURATION */

define("CONDUCTTR_CONSUMER_KEY", "MY_CONDUCTTR_CONSUMER_KEY");
define("CONDUCTTR_CONSUMER_SECRET", "MY_CONDUCTTR_CONSUMER_SECRET");
define("CONDUCTTR_PROJECT_ID", "MY_CONDUCTTR_PROJECT_ID");



/*------------------------------- Edit the information above ------------------------*/        


define("CONDUCTTR_OAUTH_HOST","https://my.conducttr.com/oauth/");
define("CONDUCTTR_REQUEST_TOKEN_URL", CONDUCTTR_OAUTH_HOST . "request-token");
define("CONDUCTTR_AUTHORIZE_URL", CONDUCTTR_OAUTH_HOST . "authorize");
define("CONDUCTTR_ACCESS_TOKEN_URL", CONDUCTTR_OAUTH_HOST . "access-token");

class Conducttr_API {
    private $db;
    
    // Constructor - create DB connection
	function __construct() {
        $this->db = new PDO('mysql:host='.MYSQL_DBHOST.';dbname='.MYSQL_DBNAME,MYSQL_USER,MYSQL_PASS);
    }
    // Destructor - close DB connection
    function __destruct() {
        $this->db= null;
    }
	
	function get_NextVideo($phone){
		
		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('SELECT nextvideo FROM audience WHERE audience_phone = :audience_phone');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
	function set_NextVideo($video,$phone){
		$params = array(':nextvideo' => $video ,':audience_phone' => $phone);
		$st = $this->db->prepare('UPDATE audience SET nextvideo = :nextvideo WHERE audience_phone = :audience_phone');
		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}

	function reset_NextVideo($phone){

		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('UPDATE audience SET nextvideo = 0 WHERE audience_phone = :audience_phone');
		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	
	function get_user_info(){
   
		$st = $this->db->prepare('SELECT * FROM audience');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

	
	function create_user($phone){

		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('INSERT INTO audience (audience_phone) VALUES (:audience_phone)');
		
		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	
	function delete_user($phone){

		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('DELETE FROM audience WHERE audience_phone = :audience_phone');
		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	
	function create_table(){
		
		/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE TABLE FROM THE API */
		/* MOST OF THE HOSTING SERVICE ALLOW GRAPHICALLY TO CREATE YOUR TABLES */

		$q="CREATE TABLE IF NOT EXISTS audience (
				audience_phone varchar(12) NOT NULL,
				audience_id  varchar(10) NOT NULL,
				audience_email varchar(30) NOT NULL,
				audience_twitter  varchar(20) NOT NULL,
				nextvideo  int(2) NOT NULL DEFAULT '0',
				PRIMARY KEY (audience_phone),
				UNIQUE KEY id (audience_phone)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
		
		try {
			$this->db->exec($q);
		} catch (PDOException $e) {
			$result = $e;
		}
		$result = array("response" => "OK"); 
		return $result;	
	}
	
	function create_videos_table(){
		
		/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE TABLE FROM THE API */
		/* MOST OF THE HOSTING SERVICE ALLOW GRAPHICALLY TO CREATE YOUR TABLES */

		$q="CREATE TABLE IF NOT EXISTS videos (
				id int(1) not null DEFAULT '1',
				videos text NOT NULL,
				video_journey text NOT NULL,
				tell_conducttr_im_here  text NOT NULL,
				video_width  int(1) NOT NULL,
				video_height  int(1) NOT NULL,
				PRIMARY KEY (id)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
		
		
		try {
			$this->db->exec($q);
		} catch (PDOException $e) {
			$result = $e;
		}
		$result = array("response" => "OK"); 
		return $result;	
	}
	
	function create_db(){
		
		/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE FROM THE API - CONSULT IF YOUR HOSTING ALLOW THAT */
		/* MOST OF THE HOSTING SERVICE ALLOW GRAPHICALLY TO CREATE YOUR DATABASE */

		try {
			$dbh = new PDO("mysql:host=".MYSQL_ROOT_DBHOST, MYSQL_ROOT_USER, MYSQL_ROOT_PASS);
			$dbh->exec("CREATE DATABASE ".MYSQL_DBNAME.";
					CREATE USER ".MYSQL_USER."@'localhost' IDENTIFIED BY ".MYSQL_PASS.";
					GRANT ALL ON ".MYSQL_DBNAME.".* TO ".MYSQL_USER."@'localhost';
					FLUSH PRIVILEGES;") 
        or die(print_r($dbh->errorInfo(), true));

		} catch (PDOException $e) {
			die("DB ERROR: ". $e->getMessage());
		}	
	}
	
	function oauth_call($method,$matchphrase,$phone){
			
		if ($phone != ""){
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".CONDUCTTR_PROJECT_ID."/".$matchphrase."?audience_phone=".$phone;	
		}
		else{
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".CONDUCTTR_PROJECT_ID."/".$matchphrase;	
		}
		
		$options = array('consumer_key' => CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => CONDUCTTR_CONSUMER_SECRET);
		OAuthStore::instance("2Leg", $options);
		try
		{
			$request = new OAuthRequester(CONDUCTTR_REQUEST_TOKEN_URL, $method);
			$result = $request->doRequest(0);
			parse_str($result['body'], $params);
			$request = new OAuthRequester($CONDUCTTR_REQUEST_URL, $method, $params);
			$result = $request->doRequest();
			return json_decode($result['body']);

		}
		catch(OAuthException2 $e){
			echo "Exception" . $e->getMessage();
		}
	}
	function setup (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$videos = $input['videos'];
		$video_journey = $input['video_journey'];
		$tell_conducttr_im_here = $input['tell_conducttr_im_here'];
		$video_width = $input['video_width'];
		$video_height = $input['video_height'];
		
		
		$videos_string = serialize($videos);
		$video_journey_string = serialize($video_journey);
		$tell_conducttr_im_here_string = serialize($tell_conducttr_im_here);

		$params = array(':videos' => $videos_string, ':video_journey' => $video_journey_string, ':tell_conducttr_im_here' => $tell_conducttr_im_here_string, 'video_width' => $video_width, 'video_height' => $video_height );

		$st = $this->db->prepare("UPDATE videos SET videos = :videos, video_journey = :video_journey, tell_conducttr_im_here = :tell_conducttr_im_here, video_width = :video_width, video_height = :video_height  WHERE id = '1' ");

		$st->execute($params);
		
		
		$result = array("response" => "OK"); 
		return $result;
		
	}
	function get_setup(){
		$st = $this->db->prepare('SELECT videos, video_journey, tell_conducttr_im_here, video_width, video_height FROM videos');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		$result = array("videos" => unserialize($data[0]['videos']), "video_journey" => unserialize($data[0]['video_journey']), "tell_conducttr_im_here" => unserialize($data[0]['tell_conducttr_im_here']), "video_width" =>$data[0]['video_width'],"video_height" =>$data[0]['video_height']); 

		return $result;
	}


	function set_videos (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$videos = $input['videos'];

		$videos_string = serialize($videos);
		$params = array(':videos' => $videos_string);
		
		$st = $this->db->prepare("UPDATE videos SET videos = :videos WHERE id = '1' ");

		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;

	}
	
	function set_video_journey (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$video_journey = $input['video_journey'];

		$video_journey_string = serialize($video_journey);
		$params = array(':video_journey' => $video_journey_string);
		
		$st = $this->db->prepare("UPDATE videos SET video_journey = :video_journey WHERE id = '1' ");

		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	function set_triggers (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$tell_conducttr_im_here = $input['tell_conducttr_im_here'];

		$tell_conducttr_im_here_string = serialize($tell_conducttr_im_here);
		$params = array(':tell_conducttr_im_here' => $tell_conducttr_im_here_string);
		
		$st = $this->db->prepare("UPDATE videos SET tell_conducttr_im_here = :tell_conducttr_im_here WHERE id = '1' ");

		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	function set_video_size (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$video_width = $input['video_width'];
		$video_height = $input['video_height'];

		//$tell_conducttr_im_here_string = serialize($tell_conducttr_im_here);
		
		$params = array(':video_width' => $video_width, ':video_height' => $video_height);
		
		$st = $this->db->prepare("UPDATE videos SET video_width = :video_width, video_height = :video_height WHERE id = '1' ");

		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	function get_videos(){
		$st = $this->db->prepare('SELECT videos FROM videos');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return unserialize($data[0]['videos']);

	}
	function get_video_journey(){
		$st = $this->db->prepare('SELECT video_journey FROM videos');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return unserialize($data[0]['video_journey']);
	}
	function get_triggers(){
		$st = $this->db->prepare('SELECT tell_conducttr_im_here FROM videos');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return unserialize($data[0]['tell_conducttr_im_here']);
	}
	function initialize_videos(){

		$st = $this->db->prepare("INSERT INTO videos (id) VALUES ('1')");
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		$result = array("response" => "OK"); 
		return $result;
	}	
	function get_video_size(){
		$st = $this->db->prepare('SELECT video_width, video_height FROM videos');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return ($data[0]);

	}
}

$api = new Conducttr_API;
$possible_method = array("GET","POST", "PUT", "DELETE");
$value = "An error has occurred";

if (isset($_REQUEST["action"])){
	
	$action=strtolower($_REQUEST["action"]);
	switch ($action){
		case "get_user_info":
			$value = $api->get_user_info();
			break;
		case "get_nextvideo":
			if ( isset($_REQUEST["audience_phone"] ))
				$value = $api->get_NextVideo($_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";
			break;	
		case "reset_nextvideo":
			if (isset($_REQUEST["audience_phone"]))
				$value = $api->reset_NextVideo($_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";
			break;		
		case "set_nextvideo":
			if (isset($_REQUEST["nextvideo"]) && isset($_REQUEST["audience_phone"]))
				$value = $api->set_NextVideo($_REQUEST["nextvideo"],$_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";
			break;	
		case "create_user":
			if (isset($_REQUEST["audience_phone"]))
				$value = $api->create_user($_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";
			break;	
		case "delete_user":
			if (isset($_REQUEST["audience_phone"]))
				$value = $api->delete_user($_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";
			break;	
        case "create_table":
			$value = $api->create_table();
			break; 
		case "create_videos_table":
			$value = $api->create_videos_table();
			break;
		case "initialize_videos":
			$value = $api->initialize_videos();
			break;
		case "create_db":
			$value = $api->create_db();
			break;
		case "oauth_call":
			if ( isset($_REQUEST["method"])  && isset($_REQUEST["matchphrase"]) && in_array($_REQUEST["method"], $possible_method))
				$value = $api->oauth_call($_REQUEST["method"],$_REQUEST["matchphrase"],$_REQUEST["audience_phone"]);
			break;	
		case "setup":
			$value = $api->setup();
			break;
		case "get_setup":
			$value = $api->get_setup();
			break;
		case "set_videos":
			$value = $api->set_videos();
			break;	
		case "set_video_journey":
			$value = $api->set_video_journey();
			break;
		case "set_triggers":
			$value = $api->set_triggers();
			break;	
		case "set_video_size":
			$value = $api->set_video_size();
			break;	
		case "get_videos":
			$value = $api->get_videos();
			break;
		case "get_video_journey":
			$value = $api->get_video_journey();
			break;
		case "get_triggers":
			$value = $api->get_triggers();
			break;
		case "get_video_size":
			$value = $api->get_video_size();
			break;	
			
		case "initialize":
			$api->create_table();
			$api->create_videos_table();
			$api->initialize_videos();
			$value = array("response" => "OK"); 
			break;	
    }
}
exit(json_encode($value));
?>
?>
