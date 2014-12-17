<?php
session_start();
/* OAUTH Library  - Edit this if you change the path of the OAuth folder */ 
include_once "Oauth/OAuthRequestSigner.php";

define("MYSQL_DBHOST", "localhost");
define("MYSQL_DBNAME", "communicator");
define("MYSQL_USER", "communicator");
define("MYSQL_PASS", "Eiglesias07");

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
				$this->BADGES_GROUP_ID = $data[0]['BADGES_GROUP_ID'];
				$this->ROLES_GROUP_ID = $data[0]['ROLES_GROUP_ID'];
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
		//return json_encode($data);
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
			'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
				'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
			$this->BADGES_GROUP_ID = $data[0]['BADGES_GROUP_ID'];
			$this->ROLES_GROUP_ID = $data[0]['ROLES_GROUP_ID'];
		}
		else return array("Response" => array("status"=>401,"message"=>"Not valid project", "error"=>$st->errorInfo()));			
		
		$st = $this->db->prepare('SELECT * FROM audience WHERE audience_email=:audience_email AND project_id=:project_id');
		$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
		$st->bindValue(':project_id', $this->CONDUCTTR_PROJECT_ID, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		//return a;
		if (!empty($data)){
			//if ( $data[0]['password'] == md5($password)){
			if ( true ){
				$_SESSION['audience_id'] = $data[0]['id'];
				$audience_id=$data[0]['id'];
				/* CHECK ROLES */
				$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/child_groups";		
				$params = array(
					"audience_email"=>$audience_email,
					"root_group_id"=>$this->ROLES_GROUP_ID
				);
				$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
				OAuthStore::instance("2Leg", $options);		
				$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
				$secrets = array(
					'signature_methods' => array('HMAC-SHA1'),
					'token' => $this->CONDUCTTR_ACCESS_TOKEN,
					'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
					'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
				$st = $this->db->prepare('UPDATE audience SET roles=:roles WHERE id=:audience_id');
				$st->bindValue(':audience_id', $audience_id, PDO::PARAM_INT);
				$st->bindValue(':roles', serialize($roles), PDO::PARAM_STR);
				if($st->execute()) $result = array ("Response" => array("status"=>200,"message"=>"Login successful",'groups'=>$groups,'roles'=>serialize($roles),"error"=>$st->errorInfo() ));
				else $result = array ("Response" => array("status"=>200,"message"=>"Login successful,roles not updated","error"=>$st->errorInfo()));
				return $result;
			}
			else $result = array ("Response" => array("status"=>401,"message"=>"Incorrect password"));
			return $result;
		}
		else {

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
				'nonce' => md5(md5(date('H:i:s')).md5(time())),
				'timestamp' => time(),
				'consumer_key' => $this->CONDUCTTR_CONSUMER_KEY,
				'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET
			);
			$req->sign(0, $secrets);
			$signed_url = sprintf('%s?%s', $CONDUCTTR_REQUEST_URL, $req->getQueryString(false));
			//$signed_url = preg_replace('/&?password=[^&]*/', '', $signed_url);
			//$signed_url = preg_replace('/&?project_id=[^&]*/', '', $signed_url);

			$curl_options = array(
				CURLOPT_HEADER => true,
				CURLOPT_URL => $signed_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false
			);
			$curl = curl_init();

			$header = $req->getQueryString(true);
			//$header = preg_replace('/&?password=[^&]*/', '', $header);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml',   $header));   
			curl_setopt($curl, CURLOPT_POST, 1);                                         
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params); 
	
			curl_setopt_array($curl, $curl_options);
			$response = curl_exec($curl);  
			if (!$response) {  
				$response = curl_error($curl);  
			}  
			curl_close($curl);
			sleep(4);	
			/* CHECK ROLES */
			$CONDUCTTR_REQUEST_URL = "https://api.conducttr.com/v1/project/".$this->CONDUCTTR_PROJECT_ID."/child_groups";		
			$params = array(
				"audience_email"=>$audience_email,
				"root_group_id"=>$this->ROLES_GROUP_ID
			);
			$options = array('consumer_key' => $this->CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => $this->CONDUCTTR_CONSUMER_SECRET);
			OAuthStore::instance("2Leg", $options);		
			$req = new OAuthRequestSigner($CONDUCTTR_REQUEST_URL, "GET", $params);
			$secrets = array(
				'signature_methods' => array('HMAC-SHA1'),
				'token' => $this->CONDUCTTR_ACCESS_TOKEN,
				'token_secret' => $this->CONDUCTTR_ACCESS_TOKEN_SECRET,
				'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
			$st = $this->db->prepare('INSERT INTO audience (audience_email,project_id,roles) VALUES (:audience_email,:project_id,:roles)');
			$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
			$st->bindValue(':project_id', $this->CONDUCTTR_PROJECT_ID, PDO::PARAM_INT);
			$st->bindValue(':roles', serialize($roles), PDO::PARAM_STR);
			if($st->execute()) {
				$_SESSION['audience_id'] = $this->db->lastInsertId();
				$result = array ("Response" => array("status"=>200,"message"=>"Signup successful",'groups'=>$groups,'roles'=>serialize($roles),"error"=>$st->errorInfo() ));
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
				mail ( $audience_email , "Communicator Password reset" , "Your new password is: ".$randomString  );	
				return array ("Response" => array("status"=>200,"message"=>"Success", "password"=>$randomString));
			}
			else return array ("Response" => array("status"=>401,"message"=>"Email not registered","error"=>$st->errorInfo()));
			
		}
		else return array ("Response" => array("status"=>401,"message"=>"Email not registered"));
		
	}

	function send_answer($matchphrase,$index){	
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
			'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
		$st = $this->db->prepare('UPDATE inventory_items SET already_read = true WHERE audience_id = :audience_id AND id=:id');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->bindValue(':id', $index, PDO::PARAM_INT);
		if($st->execute())return array("Response"=>"OK");
		else return array("Response"=>$st->errorInfo());
	}

	function get_inventory_attributes(){
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
			'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
				if ($type!="mail" && $type!="blog"){
					//print_r($results->results[$i]->items[$j]->body);
					$messages_array = preg_replace("/<\/?([a-z][a-z0-9]*)\b[^>]*>/", "\n",$results->results[$i]->items[$j]->body);
					$messages_array = preg_replace("/\n+/", "\n",$messages_array);
					$messages_array = preg_replace("/\|name\|/", $this->audience_first_name,$messages_array);
					$messages_array = preg_replace("/\|lname\|/", $this->audience_last_name,$messages_array);
					$array = explode("\n",$messages_array);
					$count = 0;
					for ($w=0;$w<sizeof($array);$w++){
						if (!empty($array[$w])){
							if ($array[$w][0]!="d"){
								$count++;
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
				}
				$item_name = $results->results[$i]->items[$j]->name;
				
	
				preg_match("/q\\./", $messages_array, $question);
				$is_question = !empty($question);
				
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
				//print_r($item_roles[1]);
				//print_r('<br>');
				//print_r(unserialize($this->roles));
				//print_r('<br>');
				if($insert){
					//print_r('<br>Insert '.$results->results[$i]->items[$j]->name.'<br>');
					$st = $this->db->prepare('INSERT INTO inventory_items (audience_id, inventory_name, item_name, type, body, inventory_id, question, count, unlocked) VALUES (:audience_id, :inventory_name, :item_name,:type,:body,:inventory_id, :question, :count, :unlocked) ON DUPLICATE KEY UPDATE body=:body, count=:count, unlocked=:unlocked');
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
					//print_r($st->errorInfo());
					$inventory_count += $count;
				}
				//else print_r('Not inserted '.$results->results[$i]->items[$j]->name.'<br>');
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
	
	function get_audience_messages(){
		$st = $this->db->prepare('SELECT type, COUNT(*) FROM inventory_items WHERE audience_id=:audience_id AND already_read=false AND unlocked=true GROUP BY type ');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}
	
	function refresh_inventory_items($inventory_index){
		$st = $this->db->prepare('SELECT * FROM inventory_items WHERE audience_id =:audience_id AND inventory_id=:inventory_id AND already_read=false AND unlocked=true ORDER BY id ASC');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->bindValue(':inventory_id', $inventory_index, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		

		$st = $this->db->prepare('UPDATE inventory_items SET already_read = true WHERE audience_id = :audience_id AND inventory_id=:inventory_id AND question=false AND unlocked=true');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->bindValue(':inventory_id', $inventory_index, PDO::PARAM_INT);
		$st->execute();
		
		return $data;

	}

	function select_inventory_attributes($type){	
		
		//$st = $this->db->prepare('SELECT inventory_name,inventory_id, SUM(case when already_read=false and question=true then 1 else 0 end) AS question_count, COUNT(*) AS message_count FROM inventory_items WHERE audience_phone=:audience_phone AND type=:type GROUP BY inventory_id');	
		$st = $this->db->prepare('SELECT inventory_name,inventory_id, SUM(case when already_read=false and question=true AND unlocked=true then 1 else 0 end) AS question_count, SUM(count) AS message_count FROM inventory_items WHERE audience_id=:audience_id AND type=:type GROUP BY inventory_id');	

		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->bindValue(':type', $type, PDO::PARAM_STR);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		return $data;		
	}

	function select_inventory_items($inventory_id){	
		
		$st = $this->db->prepare('SELECT * FROM inventory_items WHERE audience_id = :audience_id AND inventory_id=:inventory_id AND unlocked=true AND NOT (question=true AND already_read=true) ORDER BY id ASC');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->bindValue(':inventory_id', $inventory_id, PDO::PARAM_INT);
		$st->execute();
		$data=$st->fetchAll(PDO::FETCH_ASSOC);
		$st = $this->db->prepare('UPDATE inventory_items SET already_read = true WHERE audience_id = :audience_id AND inventory_id=:inventory_id AND question=false AND unlocked=true');
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->bindValue(':inventory_id', $inventory_id, PDO::PARAM_INT);
		$st->execute();
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
			'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
				'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
		/*
		return array(
				"audience_email"=>$this->audience_email,
				"audience_phone"=>$this->audience_phone,
				"audience_first_name"=>$this->audience_first_name,
				"audience_last_name"=>$this->audience_last_name,
				"project_id"=>$this->CONDUCTTR_PROJECT_ID,
				"roles"=>$this->roles,
				"profile_image"=>$this->profile_image,
				"response"=>$response,
		);	
		*/
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
			'nonce' => md5(md5(date('H:i:s')).md5(time())),
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
		$st = $this->db->prepare('SELECT SUM(case when already_read=false AND unlocked=true then 1 else 0 end) AS Messages FROM inventory_items WHERE audience_id=:audience_id and type!=:type ');
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
	function get_badges_group(){
		if($this->BADGES_GROUP_ID!=null)
			return $this->BADGES_GROUP_ID;
		else return 0;
	}
	function print_icons(){
		$st = $this->db->prepare('SELECT Distinct inventory_attributes.type, SUM(case when inventory_items.unlocked=true AND inventory_items.audience_id=1 then 1 else 0 end) as Total, SUM(case when inventory_items.already_read=false AND inventory_items.unlocked=true AND inventory_items.audience_id=:audience_id  then 1 else 0 end) as NotRead FROM inventory_attributes LEFT Join inventory_items On (LOWER(inventory_attributes.type)=LOWER (inventory_items.type)) WHERE inventory_attributes.audience_id=:audience_id GROUP BY inventory_attributes.type');		
		$st->bindValue(':audience_id', $this->audience_id, PDO::PARAM_INT);
		$st->execute();
		$icons=$st->fetchAll(PDO::FETCH_ASSOC);
		return $icons;
	}
}

if (isset($_REQUEST["action"])){
	
	define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'); 
	if(!IS_AJAX) {
		$result = array ("Response" => array("status"=>401,"message"=>"Access denied",));
		return $result;
	}
	
	if(isset($_SESSION['audience_id']))
		$api = new Conducttr_API($_SESSION['audience_id']);
	else
		$api = new Conducttr_API(-1);

	$possible_method = array("GET","POST", "PUT", "DELETE");
	$value = "An error has occurred";

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
			$value = array("Response" => "OK"); 
			exit (json_encode($value));
			//exit ($value);
			break;
		case "send_answer":
			if ( isset($_REQUEST["matchphrase"])  && isset($_REQUEST["index"]) ){		
				$value = $api->send_answer($_REQUEST["matchphrase"],$_REQUEST["index"]);
			}
			else 	
				$value = array ("Response" => "Error");
		
			//$value = array("Response" => "ok"); 
			exit (json_encode($value));
			//exit ($value);
			break;		
		case "get_stats" :
			$value = $api->get_stats();
			exit (json_encode($value));

			break;
		case "update_profile" :
			if ( isset($_REQUEST["audience_phone"]))
				$value = $api->update_profile($_REQUEST["audience_phone"]);
			else
				$value = "Missing argument";	
			break;				
		case "select_inventory_attributes" : 
			if ( isset($_REQUEST["type"])){
				$value = $api->select_inventory_attributes($_REQUEST["type"]);
				exit( json_encode($value));
			}
			else
				$value = "Missing argument";
			break;			
			
		case "select_inventory_items" : 
			if ( isset($_REQUEST["inventory_id"])){
				$value = $api->select_inventory_items($_REQUEST["inventory_id"]);
				exit(json_encode($value));
			}
			else
				$value = "Missing argument";
			break;
			
		case "refresh_inventory_items" : 

			if ( isset($_REQUEST["inventory_id"])) {
				
				$api->get_inventory_attributes();
				
				$value = $api->refresh_inventory_items($_REQUEST["inventory_id"]);
				//$value = array();
				exit (json_encode($value));
			}
			else
				$value = "Missing argument";
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
				$value = $api->login(strtolower($_REQUEST["audience_email"]),md5($_REQUEST["password"]),$_REQUEST["project_id"]);
				//$value = array("Response"=>"TEST");
				exit (json_encode($value));
			}
			else
				$value = "Missing argument";
			break;	
		case "reset" : 
			if ( isset($_REQUEST["audience_email"]) && isset($_REQUEST["project_id"])) {
				$value = $api->reset($_REQUEST["audience_email"],$_REQUEST["project_id"]);
				//$value = array("Response"=>"TEST");
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
    }
}
?>
