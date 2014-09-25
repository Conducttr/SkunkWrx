<?php
header("Content-Type: text/event-stream\n\n");


/* OAUTH Library  - Edit this if you change the path of the OAuth folder */ 
include_once "Oauth/OAuthStore.php";
include_once "Oauth/OAuthRequester.php";


/*------------------------------- Edit the information below ------------------------*/        

/* MySQL DATABASE CONFIGURATION */

define("MYSQL_DBHOST", "MY_MYSQL_HOST");
define("MYSQL_DBNAME", "MY_MYSQL_DATABASE_NAME");
define("MYSQL_USER", "MY_MYSQL_DATABASE_USER");
define("MYSQL_PASS", "MY_MYSQL_DATABASE_PASS");

/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE FROM THE API - CONSULT IF YOUR HOSTING ALLOW THAT */
define("MYSQL_ROOT_DBHOST", "MY_MYSQL_ROOT_HOST");
define("MYSQL_ROOT_USER", "MY_MYSQL_ROOT_USER");
define("MYSQL_ROOT_PASS", "MY_MYSQL_ROOT_PASS");
	
/* CONDUCTTR OAUTH CONFIGURATION */

define("CONDUCTTR_CONSUMER_KEY", "MY_CONDUCTTR_CONSUMER_KEY");
define("CONDUCTTR_CONSUMER_SECRET", "MY_CONDUCTTR_CONSUMER_SECRET");
define("CONDUCTTR_PROJECT_ID", "MY_CONDUCTTR_PROJECT_ID");


/*------------------------------- Edit the information above ------------------------*/        

/* DON'T MODIFY THIS */
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
	
	function get_NextPage($phone){
		
		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('SELECT nextpage FROM audience WHERE audience_phone = :audience_phone');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
	
	function set_NextPage($page,$phone){
		$params = array(':nextpage' => $page ,':audience_phone' => $phone);
		$st = $this->db->prepare('UPDATE audience SET nextpage = :nextpage, newvalue = 1 WHERE audience_phone = :audience_phone');
		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}

	function reset_NextPage($phone){

		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('UPDATE audience SET nextpage = 0 WHERE audience_phone = :audience_phone');
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
		$st = $this->db->prepare('INSERT INTO audience (audience_phone,nextpage,book_journey,sync) VALUES (:audience_phone,0,0,0)  ON DUPLICATE KEY UPDATE nextpage=0,book_journey=0,sync=0');
		
		$st->execute($params);
		$result = array("response" => "OK"); 
		return $result;
	}
	function delete_user($phone){

		$params = array(':audience_phone' => $phone);
		$st = $this->db->prepare('DELETE FROM audience WHERE audience_phone = :audience_phone');
		if ($st->execute($params))	$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 
		
		return $result;
	}
	
	function set_book_journey($phone){
		
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE );
		$book_journey = $input['book_journey'];
		$book_journey_string = serialize($book_journey);
		$params = array(':audience_phone' => $phone, ':book_journey' => $book_journey_string);
		$st = $this->db->prepare('UPDATE audience SET book_journey = :book_journey, newvalue = 1 WHERE audience_phone = :audience_phone');
		if ($st->execute($params))	$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 
		
		return $result;
	}

		
	function create_issuu_table(){
		
		/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE TABLE FROM THE API */
		/* MOST OF THE HOSTING SERVICE ALLOW GRAPHICALLY TO CREATE YOUR TABLES */

		$q="CREATE TABLE IF NOT EXISTS issuu (
				id int(1) not null DEFAULT '1',
				tell_conducttr_im_here  text NOT NULL,
				book_journey  text NOT NULL,
				issuu_book  text NOT NULL,
				reader_width  int(1) NOT NULL,
				reader_height  int(1) NOT NULL,
				PRIMARY KEY (id)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";
			
		try {
			$st = $this->db->prepare($q);
			if ($st->execute()) $result = array("response" => "OK"); 
			else $result = array("response" => "ERROR"); 
		} catch (PDOException $e) {
			$result = $e;
		}
		return $result;	
	}
	function create_audience_table(){
		
		/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE TABLE FROM THE API */
		/* MOST OF THE HOSTING SERVICE ALLOW GRAPHICALLY TO CREATE YOUR TABLES */

		$q="CREATE TABLE IF NOT EXISTS `audience` (
			audience_phone varchar(12) NOT NULL,
			audience_id varchar(10) NOT NULL,
			audience_email varchar(30) NOT NULL,
			audience_twitter varchar(20) NOT NULL,
			nextpage int(2) NOT NULL DEFAULT '0',
			sync tinyint(1) DEFAULT '0',
			book_journey` text NOT NULL,
			newvalue int(11) NOT NULL,
			PRIMARY KEY (audience_phone),
			UNIQUE KEY (audience_phone)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";

		
		try {
			$st = $this->db->prepare($q);
			if ($st->execute()) $result = array("response" => "OK"); 
			else $result = array("response" => "ERROR"); 
		} catch (PDOException $e) {
			$result = $e;
		}
		return $result;	
	}
	function create_db(){
		
		/* THIS IS ONLY NECESSARY TO CREATE THE DATABASE FROM THE API - CONSULT IF YOUR HOSTING ALLOW THAT */
		/* MOST OF THE HOSTING SERVICE ALLOW GRAPHICALLY TO CREATE YOUR DATABASE */

		try {
			$dbh = new PDO("mysql:host=".MYSQL_ROOT_DBHOST, MYSQL_ROOT_USER, MYSQL_ROOT_PASS);
			$dbh->exec("CREATE DATABASE ".MYSQL_DBNAME.";
					CREATE USER ".MYSQL_ROOT_USER."@'localhost' IDENTIFIED BY ".MYSQL_ROOT_PASS.";
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
		
		$issuu_book = $input['issuu_book'];
		$tell_conducttr_im_here = $input['tell_conducttr_im_here'];
		$reader_width = $input['reader_width'];
		$reader_height = $input['reader_height'];
		
		$tell_conducttr_im_here_string = serialize($tell_conducttr_im_here);

		$params = array(':issuu_book' => $issuu_book, ':tell_conducttr_im_here' => $tell_conducttr_im_here_string, 'reader_width' => $reader_width, 'reader_height' => $reader_height );

		$st = $this->db->prepare("UPDATE issuu SET issuu_book = :issuu_book, tell_conducttr_im_here = :tell_conducttr_im_here, reader_width = :reader_width, reader_height = :reader_height  WHERE id = '1' ");

		if ($st->execute($params))
			$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 		
		
		return $result;
		
	}
	function get_setup(){
		$st = $this->db->prepare('SELECT issuu_book, tell_conducttr_im_here, book_journey, reader_width, reader_height FROM issuu');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		$result = array("issuu_book" => $data[0]['issuu_book'], "tell_conducttr_im_here" => unserialize($data[0]['tell_conducttr_im_here']),"book_journey"=> unserialize($data[0]['book_journey']), "reader_width" =>$data[0]['reader_width'],"reader_height" =>$data[0]['reader_height']); 

		return $result;
	}

	function set_issuu_book(){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$issuu_book = $input['issuu_book'];

		$params = array(':issuu_book' => $issuu_book);
		
		$st = $this->db->prepare("UPDATE issuu SET issuu_book = :issuu_book WHERE id = '1' ");

		if ($st->execute($params))
			$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 

		return $result;
	}
	

	function set_triggers (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$tell_conducttr_im_here = $input['tell_conducttr_im_here'];

		$tell_conducttr_im_here_string = serialize($tell_conducttr_im_here);
		$params = array(':tell_conducttr_im_here' => $tell_conducttr_im_here_string);
		
		$st = $this->db->prepare("UPDATE issuu SET tell_conducttr_im_here = :tell_conducttr_im_here WHERE id = '1' ");

		if ($st->execute($params))
			$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 
		
		return $result;
	}
	function set_default_book_journey(){
		
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$book_journey = $input['book_journey'];

		$book_journey_string = serialize($book_journey);
		$params = array(':book_journey' => $book_journey_string);
		
		$st = $this->db->prepare("UPDATE issuu SET book_journey = :book_journey WHERE id = '1' ");
		
		if ($st->execute($params))
			$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 
		
		return $result;
	}
	function set_reader_size (){
		$inputJSON = file_get_contents('php://input');
		$input = json_decode( $inputJSON, TRUE ); //convert JSON into array
		$reader_width = $input['reader_width'];
		$reader_height = $input['reader_height'];
		
		$params = array(':reader_width' => $reader_width, ':reader_height' => $reader_height);
		
		$st = $this->db->prepare("UPDATE issuu SET reader_width = :reader_width, reader_height = :reader_height WHERE id = '1' ");

		if ($st->execute($params))
			$result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 
		
		return $result;
	}
	function get_issuu_book(){
		$st = $this->db->prepare('SELECT issuu_book FROM issuu');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data[0]['issuu_book'];
	}
	function get_triggers(){
		$st = $this->db->prepare('SELECT tell_conducttr_im_here FROM issuu');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return unserialize($data[0]['tell_conducttr_im_here']);
	}
	function get_default_book_journey(){
		$st = $this->db->prepare('SELECT book_journey FROM issuu');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return unserialize($data[0]['book_journey']);
	}
	function get_reader_size(){
		$st = $this->db->prepare('SELECT reader_width, reader_height FROM issuu');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return ($data[0]);
	}
	function initialize_issuu(){

		$st = $this->db->prepare("INSERT INTO issuu (id) VALUES ('1')");
		if ($st->execute()) $result = array("response" => "OK"); 
		else $result = array("response" => "ERROR"); 
		return $result;
	}	
	
	function synchronize(){
		header("Content-Type: text/event-stream\n\n");
		$ALREADY_SEARCHED_PHONES=array();
		$i=0;
		$st =  $this->db->prepare('SELECT audience_phone FROM audience WHERE sync = 0');
		$st->execute($params);
		while($audience_phone = $st->fetch( PDO::FETCH_ASSOC )){ 
			$phone=$audience_phone['audience_phone']; 
			if (!array_search($phone,$ALREADY_SEARCHED_PHONES)){
				array_push($ALREADY_SEARCHED_PHONES, $phone);
				$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".CONDUCTTR_PROJECT_ID."/code?audience_phone=".$phone;	
				$options = array('consumer_key' => CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => CONDUCTTR_CONSUMER_SECRET);
				OAuthStore::instance("2Leg", $options);
				try{
					$request = new OAuthRequester(CONDUCTTR_REQUEST_TOKEN_URL, $method);
					$result = $request->doRequest(0);
					parse_str($result['body'], $params);
					$request = new OAuthRequester($CONDUCTTR_REQUEST_URL, "GET" , $params);
					$result = $request->doRequest();
					$response = json_decode($result['body']);
					if($response->results[0]->code == $_REQUEST['code']){
						$params = array(':audience_phone' => $phone);
						$st =  $this->db->prepare('UPDATE audience SET sync = 1 WHERE audience_phone = :audience_phone');
						if ($st->execute($params)){
							header("Content-Type: text/event-stream\n\n");
							header("Cache-Control: no-cache"); // recommended to prevent caching of event data.
							echo "id: $serverTime" . PHP_EOL;
							echo "data: $phone".PHP_EOL;
							echo "retry: 10000".PHP_EOL;
							echo PHP_EOL;
							ob_flush();
							flush();			
						}	
					}
				}
				catch(OAuthException2 $e){
					echo "Exception" . $e->getMessage();
				}
			}		
		}
		sleep(1);
	}
	function reading (){
		$params = array(':audience_phone' => $_REQUEST['audience_phone']);
		$st = $this->db->prepare('SELECT nextpage,book_journey FROM audience WHERE newvalue = 1 AND audience_phone=:audience_phone');
		$st->execute($params);
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		$book_journey=json_encode(unserialize($data[0]['book_journey'])); 
		$nextpage=$data[0]['nextpage']; 
		$result = array('nextpage' => $nextpage, 'book_journey' => $book_journey);
		if(sizeof($data)>0){
			$st = $this->db->prepare('UPDATE audience SET nextpage = 0, book_journey=null,newvalue = 0 WHERE audience_phone = :audience_phone');
			if ($st->execute($params)){
				$card = json_encode($result);
				header("Content-Type: text/event-stream\n\n");
				header("Cache-Control: no-cache"); // recommended to prevent caching of event data.
				echo "id: $serverTime" . PHP_EOL;
				echo "data: $card".PHP_EOL;
				echo "retry: 10000".PHP_EOL;

				echo PHP_EOL;
				ob_flush();
				flush();
				
				}
		}
		sleep(1);
	}
}

$api = new Conducttr_API;
$possible_method = array("GET","POST", "PUT", "DELETE");
$value = "Not a method selected";

if (isset($_REQUEST["action"])){
	
	$action=strtolower($_REQUEST["action"]);
	switch ($action){
		case "get_user_info":
			$value = $api->get_user_info();
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
			
		case "create_issuu_table":
			$value = $api->create_issuu_table();
			break;
		case "create_audience_table":
			$value = $api->create_audience_table();
			break;	
		case "initialize_issuu":
			$value = $api->initialize_issuu();
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
			
		case "set_issuu_book":
			$value = $api->set_issuu_book();
			break;			
		case "set_default_book_journey":
			$value = $api->set_default_book_journey();
			break;
		case "set_triggers":
			$value = $api->set_triggers();
			break;	
		case "set_reader_size":
			$value = $api->set_reader_size();
			break;	
			
		case "get_issuu_book":
			$value = $api->get_issuu_book();
		case "get_default_book_journey":
			$value = $api->get_default_book_journey();
			break;	break;
		case "get_triggers":
			$value = $api->get_triggers();
			break;
		case "get_reader_size":
			$value = $api->get_reader_size();
			break;	

		case "set_nextpage":
		if (isset($_REQUEST["nextpage"]) && isset($_REQUEST["audience_phone"]))
			$value = $api->set_NextPage($_REQUEST["nextpage"],$_REQUEST["audience_phone"]);
		else
			$value = "Missing argument";
		break;	
		case "set_book_journey":
			if(isset($_REQUEST["audience_phone"]))
				$value = $api->set_book_journey($_REQUEST["audience_phone"]);
			else $value="Error";	
			break;	
							
		case "synchronize":
			$value = $api->synchronize();
			break;
		case "reading":
			$value = $api->reading();
			break;	
		case "initialize":
			$api->create_issuu_table();
			$api->create_audience_table();
			$api->initialize_issuu();
			$value = array("response" => "OK"); 
			break;	
			
    }
}
if (isset($_REQUEST["action"]) && $_REQUEST["action"]!="synchronize" && $_REQUEST["action"]!="reading"){
	exit(json_encode($value));

}
?>
