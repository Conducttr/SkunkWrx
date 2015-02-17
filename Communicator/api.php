<?php
session_start();
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

/* OAUTH Library  - Edit this if you change the path of the OAuth folder */ 
include_once "Oauth/OAuthRequestSigner.php";
require 'phpmailer/class.phpmailer.php';
require 'phpmailer/class.smtp.php';

include_once "config.php";


class Conducttr_API {
    private $db;
	private $audience_email;
	private $audience_phone;
	private $audience_first_name;
	private $audience_last_name;
	private $roles;
	private $profile_image;
	
    private $CONDUCTTR_PROJECT_ID;
    private $CONDUCTTR_CONSUMER_KEY;
    private $CONDUCTTR_CONSUMER_SECRET;
    private $CONDUCTTR_ACCESS_TOKEN;
    private $CONDUCTTR_ACCESS_TOKEN_SECRET; 
    private $BADGES_GROUP_ID;
    private $ROLES_GROUP_ID;
	private $DELAY;
	private $INVENTORY;

    // Constructor - create DB connection
	function __construct($audience_id) {
        $this->db = new PDO('mysql:host='.MYSQL_DBHOST.';dbname='.MYSQL_DBNAME,MYSQL_USER,MYSQL_PASS);
        $this->db -> exec("set names utf8");
        
        if($audience_id!=-1){
			$st = $this->db->prepare('SELECT * FROM audience LEFT JOIN projects ON (audience.project_id=projects.PROJECT_ID) WHERE audience.id=:audience_id ');
			$st->bindValue(':audience_id', $audience_id, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($data)){
				$this->CONDUCTTR_PROJECT_ID = $data[0]['PROJECT_ID'];
				$this->CONDUCTTR_CONSUMER_KEY = $data[0]['CONSUMER_KEY'];
				$this->CONDUCTTR_CONSUMER_SECRET = $data[0]['CONSUMER_SECRET'];
				$this->CONDUCTTR_ACCESS_TOKEN = $data[0]['ACCESS_TOKEN'];
				$this->CONDUCTTR_ACCESS_TOKEN_SECRET = $data[0]['ACCESS_TOKEN_SECRET'];
				$this->REGISTRATION_REQUIRED = $data[0]['REGISTRATION_REQUIRED'];
				$this->BADGES_GROUP_ID = $data[0]['BADGES_GROUP_ID'];
				$this->ROLES_GROUP_ID = $data[0]['ROLES_GROUP_ID'];
				$this->DELAY = $data[0]['DELAY'];
				$this->INVENTORY = $data[0]['INVENTORY'];

				$this->audience_id = $audience_id;
				$this->audience_email = $data[0]['audience_email'];
				$this->audience_phone = $data[0]['audience_phone'];
				$this->audience_first_name = $data[0]['audience_first_name'];
				$this->audience_last_name = $data[0]['audience_last_name'];
				$this->roles = $data[0]['roles'];
				$this->profile_image = $data[0]['profile_image'];
			}
		}
		
    }
    
    // Destructor - close DB connection
    function __destruct() {
        $this->db= null;
    }
    
    function check_project($PROJECT_ID,$PROJECT_NAME) {
		/* Check if project exists */
		if( $PROJECT_ID != -1){
			$st = $this->db->prepare('SELECT * FROM projects WHERE PROJECT_ID=:project_id ');
			$st->bindValue(':project_id', $PROJECT_ID, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($data)){
				$_SESSION['PROJECT_ID'] = $data[0]['PROJECT_ID'];
				$_SESSION['CONSUMER_KEY'] = $data[0]['CONSUMER_KEY'];
				$_SESSION['CONSUMER_SECRET'] = $data[0]['CONSUMER_SECRET'];
				$_SESSION['ACCESS_TOKEN'] = $data[0]['ACCESS_TOKEN'];
				$_SESSION['ACCESS_TOKEN_SECRET'] = $data[0]['ACCESS_TOKEN_SECRET'];
				$_SESSION['BADGES_GROUP_ID'] = $data[0]['BADGES_GROUP_ID'];
				$_SESSION['ROLES_GROUP_ID'] = $data[0]['ROLES_GROUP_ID'];

				return $data[0]['PROJECT_ID'];
			}
			else return false;
		}
		else{
			$st = $this->db->prepare('SELECT * FROM projects WHERE PROJECT_NAME=:project_name ');
			$st->bindValue(':project_name', $PROJECT_NAME, PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($data)){
				$_SESSION['PROJECT_ID'] = $data[0]['PROJECT_ID'];
				$_SESSION['CONSUMER_KEY'] = $data[0]['CONSUMER_KEY'];
				$_SESSION['CONSUMER_SECRET'] = $data[0]['CONSUMER_SECRET'];
				$_SESSION['ACCESS_TOKEN'] = $data[0]['ACCESS_TOKEN'];
				$_SESSION['ACCESS_TOKEN_SECRET'] = $data[0]['ACCESS_TOKEN_SECRET'];
				$_SESSION['BADGES_GROUP_ID'] = $data[0]['BADGES_GROUP_ID'];
				$_SESSION['ROLES_GROUP_ID'] = $data[0]['ROLES_GROUP_ID'];
				return $data[0]['PROJECT_ID'];
			}
			else return false;
		}
    }
    
	function get_audience_details(){
		$st = $this->db->prepare('SELECT * FROM audience WHERE id=:audience_id ');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
	
	function register($audience_email,$audience_email_confirm,$password,$password_confirm,$project_id){
		if ($audience_email==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty email"));
			return $result;
		}
		if ($audience_email!=$audience_email_confirm){
			$result = array ("Response" => array("status"=>401,"message"=>"The emails aren't the same"));
			return $result;
		}
		if (!filter_var($audience_email, FILTER_VALIDATE_EMAIL)) {
			$result = array ("response" => array("status"=>401,"message"=>"Invalid email"));
			return $result;
		}		
		if ($password==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty password"));
			return $result;
		}
		if ($password!=$password_confirm){
			$result = array ("Response" => array("status"=>401,"message"=>"Passwords don't match"));
			return $result;
		}
		if ($project_id==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Not valid project"));
			return $result;
		}
		
		/* Check if project exists */
		if( $project_id != -1){
			$this->CONDUCTTR_PROJECT_ID = $project_id;
			$st = $this->db->prepare('SELECT * FROM projects WHERE PROJECT_ID=:project_id ');
			$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($data)){
				$this->CONDUCTTR_CONSUMER_KEY = $data[0]['CONSUMER_KEY'];
				$this->CONDUCTTR_CONSUMER_SECRET = $data[0]['CONSUMER_SECRET'];
				$this->CONDUCTTR_ACCESS_TOKEN = $data[0]['ACCESS_TOKEN'];
				$this->CONDUCTTR_ACCESS_TOKEN_SECRET = $data[0]['ACCESS_TOKEN_SECRET'];
				$this->BADGES_GROUP_ID = $data[0]['BADGES_GROUP_ID'];
				$this->ROLES_GROUP_ID = $data[0]['ROLES_GROUP_ID'];
			}
			else return array("Response" => array("status"=>401,"message"=>"Not valid project", "error"=>$st->errorInfo()));
		}

		/*Check audience already exists */
		$st = $this->db->prepare('SELECT * FROM audience WHERE audience_email=:audience_email AND project_id=:project_id');
		$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
		$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		
		if (!empty($data))return array ("Response" => array("status"=>403,"message"=>"Email already registerd"));

		/* Check if it's already registered inside Conducttr */
		$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/already_registered";		
		$params = array(
			"audience_email"=>$audience_email
		);
		$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
		OAuthStore::instance("2Leg", $options);		
		
		$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
		$secrets = array(
			'signature_methods' => array('HMAC-SHA1'),
			'token' => $this->CONDUCTTR_ACCESS_TOKEN,
			'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
			'nonce' => $this->makeNonce(),			
			'timestamp' => time(),
			'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
			'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
		);
		$req->sign(0, $secrets);
		$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_URL => $signed_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);  
		if (!$response) {  
			$response = curl_error($curl);  
		}  
		curl_close($curl);		
		$value = json_decode($response);
		$alredy_registered = $value->results[0]->registered;
		if ($alredy_registered==1) {
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/child_groups";		
			$params = array(
				"root_group_id" => $this->ROLES_GROUP_ID,
				"audience_email"=>$audience_email
			);
		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),			
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$options = array(
				CURLOPT_HEADER => false,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);		
			$groups = json_decode($response);
			$roles = array();
			for ($i=0; $i<sizeof($groups->results);  $i++){
				$roles[]=$groups->results[$i]->name;
			}
			$st = $this->db->prepare('INSERT INTO audience (audience_email,project_id,password,roles) VALUES (:audience_email,:project_id,:password,:roles)');
			$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
			$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
			$st->bindValue(':password', md5($password), PDO::PARAM_STR);
			$st->bindValue(':roles', serialize($roles), PDO::PARAM_STR);
			if($st->execute()){	
				$audience_id = $this->db->lastInsertId();
				$_SESSION['audience_id'] = $audience_id;
				$result = array ("Response" => array("status"=>200,"message"=>"Signup successful"));
			}
			else{
				$result = array ("Response" => array("status"=>401,"message"=>$st->errorInfo()));
			}					

		} 
		else $result= array("Response"=>array("status"=>401,"message"=>"You must register first"));
	}
	function login($audience_email,$password,$project_id){
		if ($audience_email==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty email"));
			return $result;
		}
		
		preg_match("/^(?!\.)((?!.*\.{2})[a-zA-Z0-9\x{0080}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}\x{0250}-\x{02AF}\x{0300}-\x{036F}\x{0370}-\x{03FF}\x{0400}-\x{04FF}\x{0500}-\x{052F}\x{0530}-\x{058F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0700}-\x{074F}\x{0750}-\x{077F}\x{0780}-\x{07BF}\x{07C0}-\x{07FF}\x{0900}-\x{097F}\x{0980}-\x{09FF}\x{0A00}-\x{0A7F}\x{0A80}-\x{0AFF}\x{0B00}-\x{0B7F}\x{0B80}-\x{0BFF}\x{0C00}-\x{0C7F}\x{0C80}-\x{0CFF}\x{0D00}-\x{0D7F}\x{0D80}-\x{0DFF}\x{0E00}-\x{0E7F}\x{0E80}-\x{0EFF}\x{0F00}-\x{0FFF}\x{1000}-\x{109F}\x{10A0}-\x{10FF}\x{1100}-\x{11FF}\x{1200}-\x{137F}\x{1380}-\x{139F}\x{13A0}-\x{13FF}\x{1400}-\x{167F}\x{1680}-\x{169F}\x{16A0}-\x{16FF}\x{1700}-\x{171F}\x{1720}-\x{173F}\x{1740}-\x{175F}\x{1760}-\x{177F}\x{1780}-\x{17FF}\x{1800}-\x{18AF}\x{1900}-\x{194F}\x{1950}-\x{197F}\x{1980}-\x{19DF}\x{19E0}-\x{19FF}\x{1A00}-\x{1A1F}\x{1B00}-\x{1B7F}\x{1D00}-\x{1D7F}\x{1D80}-\x{1DBF}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{1F00}-\x{1FFF}\x{20D0}-\x{20FF}\x{2100}-\x{214F}\x{2C00}-\x{2C5F}\x{2C60}-\x{2C7F}\x{2C80}-\x{2CFF}\x{2D00}-\x{2D2F}\x{2D30}-\x{2D7F}\x{2D80}-\x{2DDF}\x{2F00}-\x{2FDF}\x{2FF0}-\x{2FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3100}-\x{312F}\x{3130}-\x{318F}\x{3190}-\x{319F}\x{31C0}-\x{31EF}\x{31F0}-\x{31FF}\x{3200}-\x{32FF}\x{3300}-\x{33FF}\x{3400}-\x{4DBF}\x{4DC0}-\x{4DFF}\x{4E00}-\x{9FFF}\x{A000}-\x{A48F}\x{A490}-\x{A4CF}\x{A700}-\x{A71F}\x{A800}-\x{A82F}\x{A840}-\x{A87F}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}\.!#$%&'*+-\/=?^_`{|}~\-\d]+)@(?!\.)([a-zA-Z0-9\x{0080}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}\x{0250}-\x{02AF}\x{0300}-\x{036F}\x{0370}-\x{03FF}\x{0400}-\x{04FF}\x{0500}-\x{052F}\x{0530}-\x{058F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0700}-\x{074F}\x{0750}-\x{077F}\x{0780}-\x{07BF}\x{07C0}-\x{07FF}\x{0900}-\x{097F}\x{0980}-\x{09FF}\x{0A00}-\x{0A7F}\x{0A80}-\x{0AFF}\x{0B00}-\x{0B7F}\x{0B80}-\x{0BFF}\x{0C00}-\x{0C7F}\x{0C80}-\x{0CFF}\x{0D00}-\x{0D7F}\x{0D80}-\x{0DFF}\x{0E00}-\x{0E7F}\x{0E80}-\x{0EFF}\x{0F00}-\x{0FFF}\x{1000}-\x{109F}\x{10A0}-\x{10FF}\x{1100}-\x{11FF}\x{1200}-\x{137F}\x{1380}-\x{139F}\x{13A0}-\x{13FF}\x{1400}-\x{167F}\x{1680}-\x{169F}\x{16A0}-\x{16FF}\x{1700}-\x{171F}\x{1720}-\x{173F}\x{1740}-\x{175F}\x{1760}-\x{177F}\x{1780}-\x{17FF}\x{1800}-\x{18AF}\x{1900}-\x{194F}\x{1950}-\x{197F}\x{1980}-\x{19DF}\x{19E0}-\x{19FF}\x{1A00}-\x{1A1F}\x{1B00}-\x{1B7F}\x{1D00}-\x{1D7F}\x{1D80}-\x{1DBF}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{1F00}-\x{1FFF}\x{20D0}-\x{20FF}\x{2100}-\x{214F}\x{2C00}-\x{2C5F}\x{2C60}-\x{2C7F}\x{2C80}-\x{2CFF}\x{2D00}-\x{2D2F}\x{2D30}-\x{2D7F}\x{2D80}-\x{2DDF}\x{2F00}-\x{2FDF}\x{2FF0}-\x{2FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3100}-\x{312F}\x{3130}-\x{318F}\x{3190}-\x{319F}\x{31C0}-\x{31EF}\x{31F0}-\x{31FF}\x{3200}-\x{32FF}\x{3300}-\x{33FF}\x{3400}-\x{4DBF}\x{4DC0}-\x{4DFF}\x{4E00}-\x{9FFF}\x{A000}-\x{A48F}\x{A490}-\x{A4CF}\x{A700}-\x{A71F}\x{A800}-\x{A82F}\x{A840}-\x{A87F}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}\-\.\d]+)((\.([a-zA-Z\x{0080}-\x{00FF}\x{0100}-\x{017F}\x{0180}-\x{024F}\x{0250}-\x{02AF}\x{0300}-\x{036F}\x{0370}-\x{03FF}\x{0400}-\x{04FF}\x{0500}-\x{052F}\x{0530}-\x{058F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0700}-\x{074F}\x{0750}-\x{077F}\x{0780}-\x{07BF}\x{07C0}-\x{07FF}\x{0900}-\x{097F}\x{0980}-\x{09FF}\x{0A00}-\x{0A7F}\x{0A80}-\x{0AFF}\x{0B00}-\x{0B7F}\x{0B80}-\x{0BFF}\x{0C00}-\x{0C7F}\x{0C80}-\x{0CFF}\x{0D00}-\x{0D7F}\x{0D80}-\x{0DFF}\x{0E00}-\x{0E7F}\x{0E80}-\x{0EFF}\x{0F00}-\x{0FFF}\x{1000}-\x{109F}\x{10A0}-\x{10FF}\x{1100}-\x{11FF}\x{1200}-\x{137F}\x{1380}-\x{139F}\x{13A0}-\x{13FF}\x{1400}-\x{167F}\x{1680}-\x{169F}\x{16A0}-\x{16FF}\x{1700}-\x{171F}\x{1720}-\x{173F}\x{1740}-\x{175F}\x{1760}-\x{177F}\x{1780}-\x{17FF}\x{1800}-\x{18AF}\x{1900}-\x{194F}\x{1950}-\x{197F}\x{1980}-\x{19DF}\x{19E0}-\x{19FF}\x{1A00}-\x{1A1F}\x{1B00}-\x{1B7F}\x{1D00}-\x{1D7F}\x{1D80}-\x{1DBF}\x{1DC0}-\x{1DFF}\x{1E00}-\x{1EFF}\x{1F00}-\x{1FFF}\x{20D0}-\x{20FF}\x{2100}-\x{214F}\x{2C00}-\x{2C5F}\x{2C60}-\x{2C7F}\x{2C80}-\x{2CFF}\x{2D00}-\x{2D2F}\x{2D30}-\x{2D7F}\x{2D80}-\x{2DDF}\x{2F00}-\x{2FDF}\x{2FF0}-\x{2FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{3100}-\x{312F}\x{3130}-\x{318F}\x{3190}-\x{319F}\x{31C0}-\x{31EF}\x{31F0}-\x{31FF}\x{3200}-\x{32FF}\x{3300}-\x{33FF}\x{3400}-\x{4DBF}\x{4DC0}-\x{4DFF}\x{4E00}-\x{9FFF}\x{A000}-\x{A48F}\x{A490}-\x{A4CF}\x{A700}-\x{A71F}\x{A800}-\x{A82F}\x{A840}-\x{A87F}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}]){2,63})+)$/u",$audience_email,$matches);
		if(empty($matches)){
			$result = array ("Response" => array("status"=>401,"message"=>"Wrong email format","error"=>$matches));
			return $result;
		}
		/*
		if (!filter_var($audience_email, FILTER_VALIDATE_EMAIL)) {
			$result = array ("Response" => array("status"=>401,"message"=>"Wrong email format"));
			return $result;
		}
		*/
		/*
		if ($password==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty password"));
			return $result;
		}
		
		if ($project_id==-1 && $project_id==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty project"));
			return $result;
		}
		*/
		$this->CONDUCTTR_PROJECT_ID = $project_id;
		$st = $this->db->prepare('SELECT * FROM projects WHERE PROJECT_ID=:project_id ');
		$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($data)){
			$this->CONDUCTTR_CONSUMER_KEY = $data[0]['CONSUMER_KEY'];
			$this->CONDUCTTR_CONSUMER_SECRET = $data[0]['CONSUMER_SECRET'];
			$this->CONDUCTTR_ACCESS_TOKEN = $data[0]['ACCESS_TOKEN'];
			$this->CONDUCTTR_ACCESS_TOKEN_SECRET = $data[0]['ACCESS_TOKEN_SECRET'];
			
			$this->REGISTRATION_REQUIRED = $data[0]['REGISTRATION_REQUIRED'];

			$this->BADGES_GROUP_ID = $data[0]['BADGES_GROUP_ID'];
			$this->ROLES_GROUP_ID = $data[0]['ROLES_GROUP_ID'];
		}
		else return array("Response" => array("status"=>401,"message"=>"Not valid project", "error"=>$st->errorInfo()));			
		
		$st = $this->db->prepare('SELECT * FROM audience WHERE audience_email=:audience_email AND project_id=:project_id');
		$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
		$st->bindValue(':project_id', $this->CONDUCTTR_PROJECT_ID, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		
		/* User is already registered */
		if (!empty($data)){
			if($this->REGISTRATION_REQUIRED){
				if ( $data[0]['password'] != md5($password)){
					$result = array ("Response" => array("status"=>401,"message"=>"Incorrect password","pass"=>$data[0]['password'],"pass2"=>md5($password),"pass3"=>$password));
					return $result;
				}
			}
			$_SESSION['audience_id'] = $data[0]['id'];
			$audience_id=$data[0]['id'];
			if ( true ){
				$_SESSION['audience_id'] = $data[0]['id'];
				$audience_id=$data[0]['id'];
				$result = array ("Response" => array("status"=>200,"message"=>"Login successful","action"=>"login"));
				/* Login API Call to Conducttr */
				$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/login";		
				$params = array(
					"audience_email"=>$audience_email,
					"password"=>"overwritten",
				);
				$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
				OAuthStore::instance("2Leg", $options);		
				$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
				$secrets = array(
					'signature_methods' => array('HMAC-SHA1'),
					'token' => $this->CONDUCTTR_ACCESS_TOKEN,
					'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
					'nonce' => $this->makeNonce(),			
					'timestamp' => time(),
					'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
					'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
				);
				$req->sign(0, $secrets);
				$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
				$options = array(
					CURLOPT_HEADER => false,
					CURLOPT_URL => $signed_url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false
				);
				$curl = curl_init();
				curl_setopt_array($curl, $options);
				$response = curl_exec($curl);  
				if (!$response) {  
					$response = curl_error($curl);  
				}  
				curl_close($curl);		
				return $result;
			}
			else $result = array ("Response" => array("status"=>401,"message"=>"Incorrect password"));
			

			return $result;
		}
		/* New user */
		else {
			if($this->REGISTRATION_REQUIRED){
				$result = array ("Response" => array("status"=>401,"message"=>"Please register first"));
				return $result;	
			}
			$params = array(
				"audience_email"=>$audience_email,
				"password"=>"overwritten"
			);
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/registration";
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "POST", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),			
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$curl_options = array(
				CURLOPT_HEADER => true,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();

			$header = $req->getQueryString(true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml',   $header));   
			curl_setopt($curl, CURLOPT_POST, 1);                                         
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params); 
	
			curl_setopt_array($curl, $curl_options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);
			
			$roles = array();
			$st = $this->db->prepare('INSERT INTO audience (audience_email,project_id,roles) VALUES (:audience_email,:project_id,:roles)');
			$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
			$st->bindValue(':project_id', $this->CONDUCTTR_PROJECT_ID, PDO::PARAM_INT);
			$st->bindValue(':roles', serialize($roles), PDO::PARAM_STR);
			if($st->execute()) {
				$_SESSION['audience_id'] = $this->db->lastInsertId();
				$result = array ("Response" => array("status"=>200,"message"=>"Signup successful","action"=>"registration"));
			}
			else $result = array ("Response" => array("status"=>403,"message"=>"Error","error"=>$st->errorInfo()));
			return $result;
		} return array ("Response" => array("status"=>401,"message"=>"Error" ,"error"=>$st->errorInfo() ));
	}
	
	function reset ($audience_email,$project_id){
		if ($audience_email==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty email"));
			return $result;
		}
		if (!filter_var($audience_email, FILTER_VALIDATE_EMAIL)) {
			$result = array ("response" => array("status"=>401,"message"=>"Invalid email"));
			return $result;
		}
		if ($project_id==""){
			$result = array ("Response" => array("status"=>401,"message"=>"Empty project"));
			return $result;
		}
		$this->CONDUCTTR_PROJECT_ID = $project_id;
		$st = $this->db->prepare('SELECT * FROM audience WHERE audience_email=:audience_email AND project_id=:project_id');
		$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
		$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		//return a;
		if (!empty($data)){
			$audience_id = $data[0]['id'];
			$audience_first_name = $data[0]['audiece_first_name'];
			//$audinece_last_name = $data[0]['audiece_last_name'];

			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < 8; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			
			$st = $this->db->prepare('UPDATE audience SET password=:password WHERE id=:audience_id');
			$st->bindValue(':audience_id', $audience_id, PDO::PARAM_STR);
			$st->bindValue(':password', md5($randomString), PDO::PARAM_INT);
			if($st->execute()){
				//mail ( $audience_email , "Communicator Password reset" , "Your new password is: ".$randomString  );	
				
				$log = array();
				$mail = new PHPMailer(true);
				try{
					$mail->IsSMTP();
					$mail->SMTPDebug  = 0;
					$mail->Debugoutput = 'html';
					$mail->Host       = 'smtpout.secureserver.net';
					$mail->Port       = 25;
					$mail->SMTPAuth   = true;
					$mail->Username	   = "s.spindler@cosmicvoyageenterprises.com";
					$mail->Password   = "diablo123";
					$mail->SetFrom('s.spindler@cosmicvoyageenterprises.com','Sarah Spindler');
					$mail->AddAddress($audience_email);
					$mail->Subject = 'Reset Password';
					$msg = "Hello ".$audience_first_name."<br><br>
							Your new password is: ".$randomString."<br><br>
							Go to <a href='http://cve.cm.cr/'>http://cve.cm.cr/</a>  and login with the password provided. Then you can change it in your profile if you want.<br><br>
							Remember that is really important that you access the Communicator frequently. All Cosmic Voyage Enterprises communications are handled there.<br><br>
							Speak soon";
					
							
					$mail->MsgHTML($msg);
					$mail->AltBody = 'Your new password is: '.$randomString;
					$mail->Send();
				}
				catch (phpmailerException $e) {
					$log[]= $e->errorMessage(); //Pretty error messages from PHPMailer
				} 
				catch (Exception $e) {
					$log[]= $e->getMessage(); //Boring error messages from anything else!
				}
				
				
				return array ("Response" => array("status"=>200,"message"=>"Success", "password"=>$randomString,"log"=>$log));
			}
			else return array ("Response" => array("status"=>401,"message"=>"Email not registered","error"=>$st->errorInfo()));
			
		}
		else return array ("Response" => array("status"=>401,"message"=>"Email not registered"));
		
	}

	function send_answer($matchphrase,$character,$type,$index){	
		$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/communicator";	
		$params = array(
			"audience_email"=>$this->audience_email,
			"matchphrase" =>$matchphrase,
			"character" =>$character,
			"type" =>$type,
		);
		$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
		OAuthStore::instance("2Leg", $options);		
		$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
		$secrets = array(
			'signature_methods' => array('HMAC-SHA1'),
			'token' => $this->CONDUCTTR_ACCESS_TOKEN,
			'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
			'nonce' => $this->makeNonce(),			
			'timestamp' => time(),
			'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
			'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
		);
		$req->sign(0, $secrets);
		$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_URL => $signed_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);  
		if (!$response) {  
			$response = curl_error($curl);  
		}  
		curl_close($curl);	
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){	
			$st = $this->db->prepare('UPDATE messages SET already_read = true WHERE audience_id = :audience_id AND id=:id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':id', $index, PDO::PARAM_INT);
			//if($st->execute())return array("Response"=>"OK","signed_url"=>$signed_url, "response"=>$response );
			if($st->execute())return array("Response"=>"OK");
			else return array("Response"=>$st->errorInfo());
		}
		/* OLD CODE - Inventory Based */	
		else{	
			$st = $this->db->prepare('UPDATE inventory_items SET already_read = true WHERE audience_id = :audience_id AND id=:id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':id', $index, PDO::PARAM_INT);
			//if($st->execute())return array("Response"=>"OK","signed_url"=>$signed_url, "response"=>$response );
			if($st->execute())return array("Response"=>"OK");
			else return array("Response"=>$st->errorInfo());
		}
	}
	
	function send_gate($matchphrase,$index){	
		
		$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/".$matchphrase;	
		$params = array(
			"audience_email"=>$this->audience_email
		);
		$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
		OAuthStore::instance("2Leg", $options);		
		$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
		$secrets = array(
			'signature_methods' => array('HMAC-SHA1'),
			'token' => $this->CONDUCTTR_ACCESS_TOKEN,
			'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
			//'nonce' => md5(md5(date('H:i:s')).md5(time())),						'timestamp' => time(),
			'nonce' => $this->makeNonce(),			
			'timestamp' => time(),
			'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
			'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
		);
		$req->sign(0, $secrets);
		$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_URL => $signed_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);  
		if (!$response) {  
			$response = curl_error($curl);  
		}  

		curl_close($curl);		
		$response = json_decode($response);

		$result = array("Response"=>"Error","message" => $response->results[0]);
		
		foreach($response->results[0] as $key => $value){
			if($value==true || $value==1){
				$st = $this->db->prepare('UPDATE messages SET already_read = true WHERE audience_id = :audience_id AND id=:id');
				$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
				$st->bindValue(':id', $index, PDO::PARAM_INT);
				if($st->execute())return array("Response"=>"OK");
				else return array("Response"=>"OK","error"=>$st->errorInfo());
			}
			else $result=array("Response"=>"Error","key" => $key, "value"=>$value );
		}

		return $result;
	}
	
	function get_message_feeds(){		
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/get_communicator";	
			
			$params = array(
				"audience_email"=>$this->audience_email
			);
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),			
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$options = array(
				CURLOPT_HEADER => false,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);		
			$results = json_decode($response);
			
			/* UNLOCKED ITEMS */
			$st =  $this->db->prepare('UPDATE messages SET unlocked=:unlocked WHERE audience_id=:audience_id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':unlocked',false, PDO::PARAM_INT);
			$st->execute();
			
			for ($i=0; $i<sizeof($results->results);  $i++){
				$total_count = 0;
				$total_question_count = 0;

				foreach ($results->results[$i] as $key => $value){
					for ($j=0; $j<sizeof($value);  $j++){
						$id = $value[$j][1];
						$message_feed_id = $value[$j][16];
						$name = $value[$j][5];
						$body = $value[$j][3];
						$type = $value[$j][17];
						$character_name = $value[$j][15];

						$count = 0; 
						$is_question=false;
						/* Debug */
						/*
						echo "<b>MESSAGE ID: </b>".$id;
						echo "<br>";

						echo "<b>MESSAGE FEED ID: </b>".$message_feed_id;
						echo "<br>";

						echo "<b>MESSAGE NAME: </b>".$name;
						echo "<br>";

						echo "<b>Body: </b>".$body;
						echo "<br>";
						echo "<b>Type: </b>".$type;
						echo "<br>";

						echo "<b>Character name: </b>".$character_name;
						
						echo "<br><br>";
						*/
						/* Debug */
						if ($type!="Mail" && $type!="Blog"){
							$parsed_body = preg_replace('/\<\/div\>/',"</div>\n",$body);
							$parsed_body = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "",$parsed_body);
							$parsed_body = preg_replace("/\n+/", "\n",$parsed_body);
							$parsed_body = preg_replace("/\|name\|/", $this->audience_first_name,$parsed_body);
							$parsed_body = preg_replace("/\|lname\|/", $this->audience_last_name,$parsed_body);
							$array = explode("\n",$parsed_body);
							$count = 0;
							for ($w=0;$w<sizeof($array);$w++){
								if (!empty($array[$w])){
									if ($array[$w][0]!="d" && $array[$w][0]!="q" && $array[$w][0]!="x" && $array[$w][0]!="g" && $array[$w][0]!="o"){
										$count++;
									}	
									if (($array[$w][0]=="q" || $array[$w][0]=="x" || $array[$w][0]=="g" || $array[$w][0]=="o") && ($array[$w][1]==".")){
										$is_question|=true;
									}
								}
							}
						}
						else{
							preg_match("/q\\./", $body, $question);
							if(empty($question)){
								$parsed_body = preg_replace("/<div.*?>/", "",$body);
								$parsed_body = preg_replace("/<\/div>/", "",$parsed_body);						
							}
							else{
								$parsed_body = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$body);
							}
							$parsed_body = preg_replace("/\n+/", "\n",$parsed_body);
							$parsed_body = preg_replace("/\|name\|/", $this->audience_first_name,$parsed_body);
							$parsed_body = preg_replace("/\|lname\|/", $this->audience_last_name,$parsed_body);
							$array = explode("\[",$parsed_body);
							$array = explode("\n",$parsed_body);
							$count = 0;
							for ($w=0;$w<sizeof($array);$w++){
								if (!empty($array[$w])){
									if ($array[$w][0]!="d" && $array[$w][0]!="q" && $array[$w][0]!="x" && $array[$w][0]!="g" && $array[$w][0]!="o" ){
										$count++;
									}
								}
							}
							$array = explode("\n",$parsed_body);
							for ($w=0;$w<sizeof($array);$w++){
								if (!empty($array[$w])){	
									if (($array[$w][0]=="q" || $array[$w][0]=="x" || $array[$w][0]=="g" || $array[$w][0]=="0" ) && ($array[$w][1]==".")){
										$is_question|=true;
									}
								}
							}
						}
						$st = $this->db->prepare('INSERT INTO messages (id, audience_id, name, type, body, message_feed_id, character_name, question, count, unlocked) VALUES (:id, :audience_id, :name, :type, :body, :message_feed_id, :character_name, :question, :count, :unlocked) ON DUPLICATE KEY UPDATE unlocked=true');	
						$st->bindValue(':id', $id, PDO::PARAM_STR);
						$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
						$st->bindValue(':name',$name, PDO::PARAM_STR);
						$st->bindValue(':message_feed_id',$message_feed_id, PDO::PARAM_INT);
						$st->bindValue(':character_name',$character_name, PDO::PARAM_INT);
						$st->bindValue(':body',$parsed_body, PDO::PARAM_STR);
						$st->bindValue(':type',$type, PDO::PARAM_STR);
						$st->bindValue(':question',$is_question, PDO::PARAM_BOOL);
						$st->bindValue(':count',$count, PDO::PARAM_INT);
						$st->bindValue(':unlocked',true, PDO::PARAM_BOOL);
						$st->execute();
						$total_count+=$count;
						if($is_question)$total_question_count++;

					}
				}
			}
		}
		/* OLD CODE - Inventory Based */	
		else{
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/get_audience_inventory";	
			$params = array(
				"audience_email"=>$this->audience_email
			);
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),			
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$options = array(
				CURLOPT_HEADER => false,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);		
			$results = json_decode($response);
			
			/* UNLOCKED ITEMS */
			$st =  $this->db->prepare('UPDATE inventory_items SET unlocked=:unlocked WHERE audience_id=:audience_id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':unlocked',false, PDO::PARAM_INT);
			$st->execute();
		
			for ($i=0; $i<sizeof($results->results);  $i++){
				$inventory_count = 0; 
				
				$type = $results->results[$i]->inventory_name;
				preg_match("/\{(.*?)\}/", $results->results[$i]->inventory_name, $matches);
				$inventory_name = preg_replace( "/\{(.*?)\}/", "", $type);
				if( $matches[0] == "{whatsup}" || $matches[0] == "{msngr}"  ){
						$type="Msngr";
				}
				else if ( $matches[0]== "{cmail}" || $matches[0] == "{mail}"){
					$type="Mail";
				}
				else if( $matches[0] == "{fakebook}" || $matches[0] == "{gosocial}"){
					$type="GoSocial";
				}				
				else if(( $matches[0] == "{tuiter}") ||( $matches[0] == "{tuitter}" || $matches[0] == "{microblog}")){
					$type="Microblog";
				}
				else if( $matches[0] == "{media}" || $matches[0] == "{file}"){
					$type="Media";
				}
				else if( $matches[0] == "{blog}" ){
					$type="Blog";
				}			
				$st =  $this->db->prepare('INSERT INTO inventory_attributes (audience_id, inventory_name, type ) VALUES (:audience_id, :inventory_name, :type) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
				$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
				$st->bindValue(':inventory_name',$inventory_name, PDO::PARAM_STR);
				$st->bindValue(':type',$type, PDO::PARAM_STR);
				$st->execute();
				
				$inventory_id = $this->db->lastInsertId();
				
				for ($j=0; $j<sizeof($results->results[$i]->items);  $j++){
					$count++;
					
					if( $matches[0] == "{whatsup}" || $matches[0] == "{msngr}"  ){
						$type="msngr";
					}
					else if ( $matches[0]== "{cmail}" || $matches[0] == "{mail}"){
						$type="mail";
					}
					else if( $matches[0] == "{fakebook}" || $matches[0] == "{gosocial}"){
						$type="gosocial";
					}				
					else if(( $matches[0] == "{tuiter}") ||( $matches[0] == "{tuitter}" || $matches[0] == "{microblog}")){
						$type="microblog";
					}
					else if( $matches[0] == "{media}" || $matches[0] == "{file}"){
						$type="media";
					}	
					else if( $matches[0] == "{blog}" ){
						$type="blog";
					}	
					$is_question=false;
					if ($type!="mail" && $type!="blog" ){
						$messages_array = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$results->results[$i]->items[$j]->body);
						$messages_array = preg_replace("/\n+/", "\n",$messages_array);
						$messages_array = preg_replace("/\|name\|/", $this->audience_first_name,$messages_array);
						$messages_array = preg_replace("/\|lname\|/", $this->audience_last_name,$messages_array);
						$array = explode("\n",$messages_array);
						$count = 0;
						for ($w=0;$w<sizeof($array);$w++){
							if (!empty($array[$w])){
								
								if ($array[$w][0]!="d" && $array[$w][0]!="q" && $array[$w][0]!="w" && $array[$w][0]!="g" && $array[$w][0]!="o"){
										$count++;
									}
								if (($array[$w][0]=="q" || $array[$w][0]=="w" || $array[$w][0]=="g" || $array[$w][0]=="o") && ($array[$w][1]==".")){
									$is_question|=true;
								}
							}
						}
					}
					else{
						preg_match("/q\\./", $results->results[$i]->items[$j]->body, $question);

						if(empty($question)){
							$messages_array = preg_replace("/<div.*?>/", "",$results->results[$i]->items[$j]->body);
							$messages_array = preg_replace("/<\/div>/", "",$messages_array);						
						}
						else{
							$messages_array = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$results->results[$i]->items[$j]->body);
						}
						$messages_array = preg_replace("/\n+/", "\n",$messages_array);
						$messages_array = preg_replace("/\|name\|/", $this->audience_first_name,$messages_array);
						$messages_array = preg_replace("/\|lname\|/", $this->audience_last_name,$messages_array);
						$array = explode("\[",$messages_array);
						$count = 0;
						for ($w=0;$w<sizeof($array);$w++){
							if (!empty($array[$w])){
								$count++;
							}
						}
						$array = explode("\n",$messages_array);
						for ($w=0;$w<sizeof($array);$w++){
							if (!empty($array[$w])){	
								
								if (($array[$w][0]=="q" || $array[$w][0]=="w" || $array[$w][0]=="g" ) && ($array[$w][1]==".")){
									$is_question|=true;
								}
							}
						}
					}
					$item_name = $results->results[$i]->items[$j]->name;
					$insert = false;
					$item_roles = array();
					preg_match_all("/\/(.*?)\//", $results->results[$i]->items[$j]->name, $item_roles);
					if(!empty($item_roles[1])){
						for($r=0;$r<sizeof($item_roles[1]);$r++){
							if(in_array($item_roles[1][$r], unserialize($this->roles))){
								$insert = true;
							}
						}
					}
					else{
						$insert = true;
					}
					if($insert){
						$st = $this->db->prepare('INSERT INTO inventory_items (audience_id, inventory_name, item_name, type, body, inventory_id, question, count, unlocked) VALUES (:audience_id, :inventory_name, :item_name,:type,:body,:inventory_id, :question, :count, :unlocked) ON DUPLICATE KEY UPDATE body=:body,question=:question, count=:count, unlocked=:unlocked');
						$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
						$st->bindValue(':inventory_name',$inventory_name, PDO::PARAM_STR);
						$st->bindValue(':inventory_id',$inventory_id, PDO::PARAM_INT);
						$st->bindValue(':item_name',$item_name, PDO::PARAM_STR);
						$st->bindValue(':body',$messages_array, PDO::PARAM_STR);
						$st->bindValue(':type',$type, PDO::PARAM_STR);
						$st->bindValue(':question',$is_question, PDO::PARAM_BOOL);
						$st->bindValue(':count',$count, PDO::PARAM_INT);
						$st->bindValue(':unlocked',true, PDO::PARAM_BOOL);
						$st->execute();
						$inventory_count += $count;
					}
				}	
				$item_name = $results->results[$i]->items[$j]->name;
				$st =  $this->db->prepare('UPDATE inventory_attributes SET count=:count WHERE id=:inventory_id AND audience_id=:audience_id');
				$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
				$st->bindValue(':inventory_id',$inventory_id, PDO::PARAM_INT);
				$st->bindValue(':count',$inventory_count, PDO::PARAM_INT);
				$st->execute();
				$inventory_id = $this->db->lastInsertId();
			}
			/* RESET ITEMS */
			$st =  $this->db->prepare('UPDATE inventory_items SET already_read=:already_read WHERE audience_id=:audience_id AND unlocked=false');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':already_read',false, PDO::PARAM_INT);
			$st->execute();
				
		}
	}
	
	function get_audience_messages(){
		$st = $this->db->prepare('SELECT type, COUNT(*) FROM messages WHERE audience_id=:audience_id AND already_read=false AND unlocked=true GROUP BY type ');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
	
	function refresh_message_feeds($message_feed_id){
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
		
			$st = $this->db->prepare('SELECT * FROM messages WHERE audience_id =:audience_id AND message_feed_id=:message_feed_id AND already_read=false AND unlocked=true ORDER BY messages.order ASC');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':message_feed_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			
			$st = $this->db->prepare('UPDATE messages SET already_read = true WHERE audience_id = :audience_id AND message_feed_id=:message_feed_id AND question=false AND unlocked=true');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':message_feed_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
		}
		/* OLD CODE - Inventory Based */	
		else {
			$st = $this->db->prepare('SELECT * FROM inventory_items WHERE audience_id =:audience_id AND inventory_id=:inventory_id AND already_read=false AND unlocked=true ORDER BY id ASC');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':inventory_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
		
			$st = $this->db->prepare('UPDATE inventory_items SET already_read = true WHERE audience_id = :audience_id AND inventory_id=:inventory_id AND question=false AND unlocked=true');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':inventory_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
			
		}
		return $data;

	}
	function select_message_feeds($type){	
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$st = $this->db->prepare('SELECT message_feed_id, character_name, body, SUM(case when already_read=false and question=true then 1 else 0 end) AS question_count, SUM(messages.count) AS message_count, SUM(case when already_read=false then messages.count else 0 end) AS new_message_count FROM messages WHERE audience_id=:audience_id AND type=:type AND unlocked=true GROUP BY message_feed_id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':type', $type, PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
		}
		/* OLD CODE - Inventory Based */	
		else {	
			$st = $this->db->prepare('SELECT body, inventory_name as character_name, inventory_id as message_feed_id, SUM(case when ( already_read=false AND question=true AND unlocked=true) then 1 else 0 end) AS question_count, SUM(case when (unlocked=true AND already_read=false AND question=false) then inventory_items.count else 0 end) AS new_message_count, SUM(inventory_items.count) AS message_count FROM inventory_items WHERE audience_id=:audience_id AND type=:type GROUP BY inventory_id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':type', strtolower($type), PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
		}
		return $data;		
	}

	function select_messages($message_feed_id){	
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$st = $this->db->prepare('SELECT * FROM messages WHERE audience_id = :audience_id AND message_feed_id=:message_feed_id AND unlocked=true ORDER BY messages.order ASC');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':message_feed_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			
			$st = $this->db->prepare('UPDATE messages SET already_read = true WHERE audience_id = :audience_id AND message_feed_id=:message_feed_id AND question=false AND unlocked=true');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':message_feed_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
		}
		/* OLD CODE - Inventory Based */	
		else {
			$st = $this->db->prepare('SELECT id, body, inventory_id as message_feed_id, question, already_read FROM inventory_items WHERE audience_id = :audience_id AND inventory_id=:inventory_id AND unlocked=true ORDER BY id ASC');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':inventory_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			$st = $this->db->prepare('UPDATE inventory_items SET already_read = true WHERE audience_id = :audience_id AND inventory_id=:inventory_id AND question=false AND unlocked=true');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':inventory_id', $message_feed_id, PDO::PARAM_INT);
			$st->execute();
		}
		// $st->errorInfo();
		return $data;	
	}

	function get_badges(){
		$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/child_groups";	
		$params = array(
			"audience_email"=>$this->audience_email,
			"root_group_id" => $this->BADGES_GROUP_ID
		);
		$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
		OAuthStore::instance("2Leg", $options);		
		$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
		$secrets = array(
			'signature_methods' => array('HMAC-SHA1'),
			'token' => $this->CONDUCTTR_ACCESS_TOKEN,
			'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
			'nonce' => $this->makeNonce(),
			'timestamp' => time(),
			'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
			'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
		);
		$req->sign(0, $secrets);
		$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_URL => $signed_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);  
		if (!$response) {  
			$response = curl_error($curl);  
		}  
		curl_close($curl);		
		$groups = json_decode($response);
		
		return $groups;	
	}
	function update_profile($update){
		$st = $this->db->prepare('SELECT * FROM audience WHERE id=:audience_id ');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
	
		if($update){
			$_POST = array();
			$params = array(
				"audience_email"=>$data[0]['audience_email'],
				"audience_phone"=>$data[0]['audience_phone'],
				"audience_first_name"=>$data[0]['audience_first_name'],
				"audience_last_name"=>$data[0]['audience_last_name']
			);
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/update_profile";
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "POST", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),				
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$curl_options = array(
				CURLOPT_HEADER => false,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();
			$header = $req->getQueryString(true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml', $header));   
			curl_setopt($curl, CURLOPT_POST, 1);                                         
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params); 
			curl_setopt_array($curl, $curl_options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);		
		}
		else $response = 'Not updated';
		
		
		return $data;
	}

	function get_stats(){
		$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
		OAuthStore::instance("2Leg", $options);	
		$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/get_stats";		
		$params = array(
			"audience_email"=>$this->audience_email
		);
		$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
		$secrets = array(
			'signature_methods' => array('HMAC-SHA1'),
			'token' => $this->CONDUCTTR_ACCESS_TOKEN,
			'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
			'nonce' => $this->makeNonce(),			
			'timestamp' => time(),
			'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
			'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
		);
		$req->sign(0, $secrets);
		$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_URL => $signed_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);  
		if (!$response) {  
			$response = array('results'=>array('points'=>0,'max_points'=>0,'progress'=>0));
		}  
		curl_close($curl);		
		$results = json_decode($response);
		return $results;
	}
	function count_messages ($type){
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$st = $this->db->prepare('SELECT SUM(case when already_read=false AND unlocked=true then 1 else 0 end) AS Messages FROM messages WHERE audience_id=:audience_id and type!=:type ');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':type', $type, PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if(empty($data)) return (0);
			else {
				if ($data[0]['Messages']!=null) return $data[0]['Messages'];
				else return 0;
			}
		}
		/* OLD CODE - Inventory Based */	
		else {
			$st = $this->db->prepare('SELECT SUM(case when already_read=false AND unlocked=true then 1 else 0 end) AS Messages FROM inventory_items WHERE audience_id=:audience_id and type!=:type ');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':type', strtolower($type), PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if(empty($data)) return (0);
			else {
				if ($data[0]['Messages']!=null) return $data[0]['Messages'];
				else return 0;
			}
		}
	}
	function new_count_messages ($message_feed_id,$type){
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$st = $this->db->prepare('SELECT SUM(case when already_read=false AND unlocked=true then 1 else 0 end) AS Messages FROM messages WHERE audience_id=:audience_id and message_feed_id!=:message_feed_id ');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':message_feed_id', $message_feed_id, PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if(empty($data)) return (0);
			else {
				if ($data[0]['Messages']!=null) return $data[0]['Messages'];
				else return 0;
			}
		}
		/* OLD CODE - Inventory Based */	
		else {
			$st = $this->db->prepare('SELECT SUM(case when already_read=false AND unlocked=true then 1 else 0 end) AS Messages FROM inventory_items WHERE audience_id=:audience_id and type!=:type ');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':type', strtolower($type), PDO::PARAM_STR);
			$st->execute();
			$data=$st->fetchAll(PDO::FETCH_ASSOC);
			if(empty($data)) return (0);
			else {
				if ($data[0]['Messages']!=null) return $data[0]['Messages'];
				else return 0;
			}
		}
	}
	
	function get_badges_group(){
		if($this->BADGES_GROUP_ID!=null)
			return $this->BADGES_GROUP_ID;
		else return 0;
	}
	function get_delay(){
		if($this->DELAY!=null)
			return $this->DELAY;
		else return 3000;
	}
	
	function set_delay($delay, $project_id){
		
		$st = $this->db->prepare('UPDATE projects SET DELAY =:DELAY WHERE PROJECT_ID = :PROJECT_ID');
		$st->bindValue(':DELAY', $delay, PDO::PARAM_INT);
		$st->bindValue(':PROJECT_ID', $project_id, PDO::PARAM_INT);
		if($st->execute()) return array("Response" => array("status"=>200,"message"=>"OK"));
		else return array("Response" => array("status"=>401,"message"=>"Error","error"=>$st->errorInfo()));
		 
		
	}
	function push($audience_email, $project_id){
		
		$st = $this->db->prepare('SELECT * FROM audience LEFT JOIN projects ON (audience.project_id=projects.PROJECT_ID) WHERE audience.audience_email=:audience_email AND projects.project_id=:project_id ');
		$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
		$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
	
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		
		if (!empty($data)){
			$this->CONDUCTTR_PROJECT_ID = $data[0]['PROJECT_ID'];
			$this->CONDUCTTR_CONSUMER_KEY = $data[0]['CONSUMER_KEY'];
			$this->CONDUCTTR_CONSUMER_SECRET = $data[0]['CONSUMER_SECRET'];
			$this->CONDUCTTR_ACCESS_TOKEN = $data[0]['ACCESS_TOKEN'];
			$this->CONDUCTTR_ACCESS_TOKEN_SECRET = $data[0]['ACCESS_TOKEN_SECRET'];
			$this->REGISTRATION_REQUIRED = $data[0]['REGISTRATION_REQUIRED'];
			$this->BADGES_GROUP_ID = $data[0]['BADGES_GROUP_ID'];
			$this->ROLES_GROUP_ID = $data[0]['ROLES_GROUP_ID'];
			$this->DELAY = $data[0]['DELAY'];

			$this->audience_id = $data[0]['id'];
			$this->audience_email = $data[0]['audience_email'];
			$this->audience_phone = $data[0]['audience_phone'];
			$this->audience_first_name = $data[0]['audience_first_name'];
			$this->audience_last_name = $data[0]['audience_last_name'];
			$this->roles = $data[0]['roles'];
			$this->profile_image = $data[0]['profile_image'];
		}
		
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/get_communicator";	
			
			$params = array(
				"audience_email"=>$this->audience_email
			);
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),			
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$options = array(
				CURLOPT_HEADER => false,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);		
			$results = json_decode($response);
			
			/* UNLOCKED ITEMS */
			$st =  $this->db->prepare('UPDATE messages SET unlocked=:unlocked WHERE audience_id=:audience_id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':unlocked',false, PDO::PARAM_INT);
			$st->execute();
			
			for ($i=0; $i<sizeof($results->results);  $i++){
				$total_count = 0;
				$total_question_count = 0;

				foreach ($results->results[$i] as $key => $value){
					for ($j=0; $j<sizeof($value);  $j++){
						$id = $value[$j][1];
						$message_feed_id = $value[$j][16];
						$name = $value[$j][5];
						$body = $value[$j][3];
						$type = $value[$j][17];
						$character_name = $value[$j][15];

						$count = 0; 
						$is_question=false;
						/* Debug */
						/*
						echo "<b>MESSAGE ID: </b>".$id;
						echo "<br>";

						echo "<b>MESSAGE FEED ID: </b>".$message_feed_id;
						echo "<br>";

						echo "<b>MESSAGE NAME: </b>".$name;
						echo "<br>";

						echo "<b>Body: </b>".$body;
						echo "<br>";
						echo "<b>Type: </b>".$type;
						echo "<br>";

						echo "<b>Character name: </b>".$character_name;
						
						echo "<br><br>";
						*/
						/* Debug */
						if ($type!="Mail" && $type!="Blog"){
							$parsed_body = preg_replace('/\<\/div\>/',"</div>\n",$body);
							$parsed_body = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "",$parsed_body);
							$parsed_body = preg_replace("/\n+/", "\n",$parsed_body);
							$parsed_body = preg_replace("/\|name\|/", $this->audience_first_name,$parsed_body);
							$parsed_body = preg_replace("/\|lname\|/", $this->audience_last_name,$parsed_body);
							$array = explode("\n",$parsed_body);
							$count = 0;
							for ($w=0;$w<sizeof($array);$w++){
								if (!empty($array[$w])){
									if ($array[$w][0]!="d" && $array[$w][0]!="q" && $array[$w][0]!="w" && $array[$w][0]!="g" && $array[$w][0]!="o"){
										$count++;
									}	
									if (($array[$w][0]=="q" || $array[$w][0]=="w" || $array[$w][0]=="g" || $array[$w][0]=="o") && ($array[$w][1]==".")){
										$is_question|=true;
									}
								}
							}
						}
						else{
							preg_match("/q\\./", $body, $question);
							if(empty($question)){
								$parsed_body = preg_replace("/<div.*?>/", "",$body);
								$parsed_body = preg_replace("/<\/div>/", "",$parsed_body);						
							}
							else{
								$parsed_body = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$body);
							}
							$parsed_body = preg_replace("/\n+/", "\n",$parsed_body);
							$parsed_body = preg_replace("/\|name\|/", $this->audience_first_name,$parsed_body);
							$parsed_body = preg_replace("/\|lname\|/", $this->audience_last_name,$parsed_body);
							$array = explode("\[",$parsed_body);
							$array = explode("\n",$parsed_body);
							$count = 0;
							for ($w=0;$w<sizeof($array);$w++){
								if (!empty($array[$w])){
									if ($array[$w][0]!="d" && $array[$w][0]!="q" && $array[$w][0]!="w" && $array[$w][0]!="g" && $array[$w][0]!="o" ){
										$count++;
									}
								}
							}
							$array = explode("\n",$parsed_body);
							for ($w=0;$w<sizeof($array);$w++){
								if (!empty($array[$w])){	
									if (($array[$w][0]=="q" || $array[$w][0]=="w" || $array[$w][0]=="g" || $array[$w][0]=="0" ) && ($array[$w][1]==".")){
										$is_question|=true;
									}
								}
							}
						}
						$st = $this->db->prepare('INSERT INTO messages (id, audience_id, name, type, body, message_feed_id, character_name, question, count, unlocked) VALUES (:id, :audience_id, :name, :type, :body, :message_feed_id, :character_name, :question, :count, :unlocked) ON DUPLICATE KEY UPDATE unlocked=true');	
						$st->bindValue(':id', $id, PDO::PARAM_STR);
						$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
						$st->bindValue(':name',$name, PDO::PARAM_STR);
						$st->bindValue(':message_feed_id',$message_feed_id, PDO::PARAM_INT);
						$st->bindValue(':character_name',$character_name, PDO::PARAM_INT);
						$st->bindValue(':body',$parsed_body, PDO::PARAM_STR);
						$st->bindValue(':type',$type, PDO::PARAM_STR);
						$st->bindValue(':question',$is_question, PDO::PARAM_BOOL);
						$st->bindValue(':count',$count, PDO::PARAM_INT);
						$st->bindValue(':unlocked',true, PDO::PARAM_BOOL);

						$st->execute();
						$total_count+=$count;
						if($is_question)$total_question_count++;

					}
				}
			}
		}
		/* OLD CODE - Inventory Based */	
		else{
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/get_audience_inventory";	
			$params = array(
				"audience_email"=>$this->audience_email
			);
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => $this->makeNonce(),			
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			$options = array(
				CURLOPT_HEADER => false,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);		
			$results = json_decode($response);
			
			/* UNLOCKED ITEMS */
			$st =  $this->db->prepare('UPDATE inventory_items SET unlocked=:unlocked WHERE audience_id=:audience_id');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':unlocked',false, PDO::PARAM_INT);
			$st->execute();
		
			for ($i=0; $i<sizeof($results->results);  $i++){
				$inventory_count = 0; 
				
				$type = $results->results[$i]->inventory_name;
				preg_match("/\{(.*?)\}/", $results->results[$i]->inventory_name, $matches);
				$inventory_name = preg_replace( "/\{(.*?)\}/", "", $type);
				if( $matches[0] == "{whatsup}" || $matches[0] == "{msngr}"  ){
						$type="Msngr";
				}
				else if ( $matches[0]== "{cmail}" || $matches[0] == "{mail}"){
					$type="Mail";
				}
				else if( $matches[0] == "{fakebook}" || $matches[0] == "{gosocial}"){
					$type="GoSocial";
				}				
				else if(( $matches[0] == "{tuiter}") ||( $matches[0] == "{tuitter}" || $matches[0] == "{microblog}")){
					$type="Microblog";
				}
				else if( $matches[0] == "{media}" || $matches[0] == "{file}"){
					$type="Media";
				}
				else if( $matches[0] == "{blog}" ){
					$type="Blog";
				}			
				$st =  $this->db->prepare('INSERT INTO inventory_attributes (audience_id, inventory_name, type ) VALUES (:audience_id, :inventory_name, :type) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
				$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
				$st->bindValue(':inventory_name',$inventory_name, PDO::PARAM_STR);
				$st->bindValue(':type',$type, PDO::PARAM_STR);
				$st->execute();
				
				$inventory_id = $this->db->lastInsertId();
				
				for ($j=0; $j<sizeof($results->results[$i]->items);  $j++){
					$count++;
					
					if( $matches[0] == "{whatsup}" || $matches[0] == "{msngr}"  ){
						$type="msngr";
					}
					else if ( $matches[0]== "{cmail}" || $matches[0] == "{mail}"){
						$type="mail";
					}
					else if( $matches[0] == "{fakebook}" || $matches[0] == "{gosocial}"){
						$type="gosocial";
					}				
					else if(( $matches[0] == "{tuiter}") ||( $matches[0] == "{tuitter}" || $matches[0] == "{microblog}")){
						$type="microblog";
					}
					else if( $matches[0] == "{media}" || $matches[0] == "{file}"){
						$type="media";
					}	
					else if( $matches[0] == "{blog}" ){
						$type="blog";
					}	
					$is_question=false;
					if ($type!="mail" && $type!="blog" ){
						$messages_array = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$results->results[$i]->items[$j]->body);
						$messages_array = preg_replace("/\n+/", "\n",$messages_array);
						$messages_array = preg_replace("/\|name\|/", $this->audience_first_name,$messages_array);
						$messages_array = preg_replace("/\|lname\|/", $this->audience_last_name,$messages_array);
						$array = explode("\n",$messages_array);
						$count = 0;
						for ($w=0;$w<sizeof($array);$w++){
							if (!empty($array[$w])){
								
								if ($array[$w][0]!="d" && $array[$w][0]!="q" && $array[$w][0]!="w" && $array[$w][0]!="g" && $array[$w][0]!="o"){
										$count++;
									}
								if (($array[$w][0]=="q" || $array[$w][0]=="w" || $array[$w][0]=="g" || $array[$w][0]=="o") && ($array[$w][1]==".")){
									$is_question|=true;
								}
							}
						}
					}
					else{
						preg_match("/q\\./", $results->results[$i]->items[$j]->body, $question);

						if(empty($question)){
							$messages_array = preg_replace("/<div.*?>/", "",$results->results[$i]->items[$j]->body);
							$messages_array = preg_replace("/<\/div>/", "",$messages_array);						
						}
						else{
							$messages_array = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$results->results[$i]->items[$j]->body);
						}
						$messages_array = preg_replace("/\n+/", "\n",$messages_array);
						$messages_array = preg_replace("/\|name\|/", $this->audience_first_name,$messages_array);
						$messages_array = preg_replace("/\|lname\|/", $this->audience_last_name,$messages_array);
						$array = explode("\[",$messages_array);
						$count = 0;
						for ($w=0;$w<sizeof($array);$w++){
							if (!empty($array[$w])){
								$count++;
							}
						}
						$array = explode("\n",$messages_array);
						for ($w=0;$w<sizeof($array);$w++){
							if (!empty($array[$w])){	
								
								if (($array[$w][0]=="q" || $array[$w][0]=="w" || $array[$w][0]=="g" ) && ($array[$w][1]==".")){
									$is_question|=true;
								}
							}
						}
					}
					$item_name = $results->results[$i]->items[$j]->name;
					$insert = false;
					$item_roles = array();
					preg_match_all("/\/(.*?)\//", $results->results[$i]->items[$j]->name, $item_roles);
					if(!empty($item_roles[1])){
						for($r=0;$r<sizeof($item_roles[1]);$r++){
							if(in_array($item_roles[1][$r], unserialize($this->roles))){
								$insert = true;
							}
						}
					}
					else{
						$insert = true;
					}
					if($insert){
						$st = $this->db->prepare('INSERT INTO inventory_items (audience_id, inventory_name, item_name, type, body, inventory_id, question, count, unlocked) VALUES (:audience_id, :inventory_name, :item_name,:type,:body,:inventory_id, :question, :count, :unlocked) ON DUPLICATE KEY UPDATE body=:body,question=:question, count=:count, unlocked=:unlocked');
						$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
						$st->bindValue(':inventory_name',$inventory_name, PDO::PARAM_STR);
						$st->bindValue(':inventory_id',$inventory_id, PDO::PARAM_INT);
						$st->bindValue(':item_name',$item_name, PDO::PARAM_STR);
						$st->bindValue(':body',$messages_array, PDO::PARAM_STR);
						$st->bindValue(':type',$type, PDO::PARAM_STR);
						$st->bindValue(':question',$is_question, PDO::PARAM_BOOL);
						$st->bindValue(':count',$count, PDO::PARAM_INT);
						$st->bindValue(':unlocked',true, PDO::PARAM_BOOL);
						$st->execute();
						$inventory_count += $count;
					}
				}	
				$item_name = $results->results[$i]->items[$j]->name;
				$st =  $this->db->prepare('UPDATE inventory_attributes SET count=:count WHERE id=:inventory_id AND audience_id=:audience_id');
				$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
				$st->bindValue(':inventory_id',$inventory_id, PDO::PARAM_INT);
				$st->bindValue(':count',$inventory_count, PDO::PARAM_INT);
				$st->execute();
				$inventory_id = $this->db->lastInsertId();
			}
			/* RESET ITEMS */
			$st =  $this->db->prepare('UPDATE inventory_items SET already_read=:already_read WHERE audience_id=:audience_id AND unlocked=false');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->bindValue(':already_read',false, PDO::PARAM_INT);
			$st->execute();
				
		}		 
		
		return array("Response"=>"OK");
	}
	function print_icons(){
		/* NEW CODE - MESSAGE FEED Based */	
		if($this->INVENTORY==false){
			$st = $this->db->prepare('SELECT Distinct type, SUM(case when audience_id=:audience_id AND unlocked=true then 1 else 0 end) as Total, SUM(case when already_read=false AND audience_id=:audience_id AND unlocked=true then 1 else 0 end) as NotRead FROM messages WHERE audience_id=:audience_id GROUP BY type');
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->execute();
			$icons=$st->fetchAll(PDO::FETCH_ASSOC);
		}
		/* OLD CODE - Inventory Based */	
		else {
			$st = $this->db->prepare('SELECT Distinct inventory_attributes.type, SUM(case when inventory_items.unlocked=true AND inventory_items.audience_id=1 then 1 else 0 end) as Total, SUM(case when inventory_items.already_read=false AND inventory_items.unlocked=true AND inventory_items.audience_id=:audience_id  then 1 else 0 end) as NotRead FROM inventory_attributes LEFT Join inventory_items On (LOWER(inventory_attributes.type)=LOWER (inventory_items.type)) WHERE inventory_attributes.audience_id=:audience_id GROUP BY inventory_attributes.type');		
			$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
			$st->execute();
			$icons=$st->fetchAll(PDO::FETCH_ASSOC);
		}
		return $icons;
		
	}
	function makeNonce() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}
if (isset($_REQUEST["action_from_conducttr"])){
	$api = new Conducttr_API(-1);
	
	$possible_method = array("GET","POST", "PUT", "DELETE");
	$value = array ("Response" => "Error", "message"=>"An error has occurred");

	$action=strtolower($_REQUEST["action_from_conducttr"]);
	switch ($action){	
		case "set_delay" :
			if ( isset($_REQUEST["delay"]) && isset($_REQUEST["project_id"]))
				$value = $api->set_delay($_REQUEST["delay"],$_REQUEST["project_id"]);
			else
				$value = array ("Response" => "Error", "message"=>"Invalid arguments");
			exit( json_encode($value));
			break;
		case "push" :
			if ( isset($_REQUEST["audience_email"]) && isset($_REQUEST["project_id"]))
				$value = $api->push($_REQUEST["audience_email"],$_REQUEST["project_id"]);
			else
				$value = array ("Response" => "Error", "message"=>"Invalid arguments");
			exit( json_encode($value));
			break;	
				
	}
	exit( json_encode($value));

}
else if (isset($_REQUEST["action"])){
	
	define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'); 
	if(!IS_AJAX) {
		$result = array ("Response" => array("status"=>401,"message"=>"Access denied"));
		exit (json_encode($result));

	}
	
	if(isset($_SESSION['audience_id']))
		$api = new Conducttr_API($_SESSION['audience_id']);
	else
		$api = new Conducttr_API(-1);

	$possible_method = array("GET","POST", "PUT", "DELETE");
	$value = array ("Response" => "Error", "message"=>"An error has occurred");

	$action=strtolower($_REQUEST["action"]);
	switch ($action){	
		
		case "create_db":
			$value = $api->create_db();
			break;	 
			
		case "oauth_call":
			if ( isset($_REQUEST["method"])  && isset($_REQUEST["matchphrase"]) && in_array($_REQUEST["method"], $possible_method))
				$value = $api->oauth_call($_REQUEST["method"],$_REQUEST["matchphrase"],$_REQUEST["audience_phone"]);
			else 	
				$value = array ("Response" => "Error");
			exit (json_encode($value));
			break;
				
		case "send_answer":
			if ( isset($_REQUEST["matchphrase"]) && isset($_REQUEST["character"]) && isset($_REQUEST["type"]) && isset($_REQUEST["index"]) ){		
				$value = $api->send_answer($_REQUEST["matchphrase"],$_REQUEST["character"],$_REQUEST["type"],$_REQUEST["index"]);
			}
			else 	
				$value = array ("Response" => "Error");
			exit (json_encode($value));
			break;	
		case "send_gate":
			if ( isset($_REQUEST["matchphrase"])  && isset($_REQUEST["index"]) ){		
				$value = $api->send_gate($_REQUEST["matchphrase"],$_REQUEST["index"]);
			}
			else 	
				$value = array ("Response" => "Error", "message"=>"Invalid arguments");
			exit (json_encode($value));
			break;				
		case "get_stats" :
			$value = $api->get_stats();
			exit (json_encode($value));

			break;
		case "get_delay" :
			$value = $api->get_delay();
			exit ($value);
			break;	
		
		case "set_delay" :
			if ( isset($_REQUEST["delay"]) && isset($_REQUEST["project_id"]))
				$value = $api->set_delay($_REQUEST["delay"],$_REQUEST["project_id"]);
			else
				$value = array ("Response" => "Error", "message"=>"Invalid arguments");

			exit( json_encode($value));
			break;		
		
		
		case "update_profile" :
			if ( isset($_REQUEST["audience_phone"]))
				$value = $api->update_profile($_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";	
			break;				
		case "select_message_feeds" : 
			if ( isset($_REQUEST["type"])){
				$value = $api->select_message_feeds($_REQUEST["type"]);
				exit( json_encode($value));
			}
			else
				$value = "Missing argument";
			break;			
			
		case "select_messages" : 
			if ( isset($_REQUEST["message_feed_id"])){
				$value = $api->select_messages($_REQUEST["message_feed_id"]);
				exit(json_encode($value));
			}
			else
				$value = "Missing argument";
			break;
			
		case "refresh_message_feeds" : 

			if ( isset($_REQUEST["message_feed_id"])) {
				$api->get_message_feeds();
				$value = $api->refresh_message_feeds($_REQUEST["message_feed_id"]);
				exit (json_encode($value));
			}
			else{
				$value = array ("Response" => array("status"=>401,"message"=>"Missing argument"));

				exit (json_encode($value));
			}
			break;
		case "check_push_notifications" : 

			if ( isset($_REQUEST["message_feed_id"])) {
				$value = $api->refresh_message_feeds($_REQUEST["message_feed_id"]);
				exit (json_encode($value));
			}
			else{
				$value = array ("Response" => array("status"=>401,"message"=>"Missing argument"));

				exit (json_encode($value));
			}
			break;	
		case "get_badges" : 
			$value = $api->get_badges();
			exit (json_encode($value));
			break;	
		case "register" : 
			if ( isset($_REQUEST["audience_email"]) && isset($_REQUEST["audience_email_confirm"]) && isset($_REQUEST["password"]) && isset($_REQUEST["password_confirm"]) && isset($_REQUEST["project_id"])) {
				
				$value = $api->register($_REQUEST["audience_email"],$_REQUEST["audience_email_confirm"],$_REQUEST["password"],$_REQUEST["password_confirm"], $_REQUEST["project_id"]);
				exit (json_encode($value));
			}
			else
				$value = "Missing argument";
			break;	
		case "login" : 
			if ( isset($_REQUEST["audience_email"])&& isset($_REQUEST["password"]) && isset($_REQUEST["project_id"])) {
				$value = $api->login(strtolower($_REQUEST["audience_email"]),$_REQUEST["password"],$_REQUEST["project_id"]);
				exit (json_encode($value));
			}
			else
				$value = "Missing argument";
			break;	
		case "reset" : 
			if ( isset($_REQUEST["audience_email"]) && isset($_REQUEST["project_id"])) {
				$value = $api->reset($_REQUEST["audience_email"],$_REQUEST["project_id"]);
				exit (json_encode($value));
			}
			else
				$value = "Missing argument";
			break;	
		
		case "count_messages" : 
			if ( isset($_REQUEST["type"])) {
				$value = $api->count_messages($_REQUEST["type"]);
				exit (json_encode($value));
			}
			else
				exit(0);
			break;
		case "new_count_messages" : 
			if ( isset($_REQUEST["message_feed_id"]) && isset($_REQUEST["type"])) {
				$value = $api->new_count_messages($_REQUEST["message_feed_id"],$_REQUEST["type"]);
				exit (json_encode($value));
			}
			else
				exit(0);
			break;			
    }
}
?>
