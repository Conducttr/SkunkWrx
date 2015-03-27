<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL);
include_once "config.php";
//ini_set("SMTP","ssl://smtp.gmail.com");
//ini_set("smtp_port","465");
//ini_set("SMTP","smtp.gmail.com");
//ini_set("smtp_port","587");
//ini_set("sendmail_from","eduardo@tstoryteller.com");


//ini_set("SMTP","ssl://smtp-mail.outlook.com");
//ini_set("smtp_port","587");
//ini_set("sendmail_from","edui_1988@hotmail.com");

//ini_set("SMTP","ssl://smtp-mail.outlook.com");
//ini_set("smtp_port","465");
//ini_set("sendmail_from","edui_1988@hotmail.com");
//ini_set("auth_username","edui_1988@hotmail.com");
//ini_set("auth_password","Luzenlaoscuridad");
/*
ini_set("SMTP","ssl://smtp.gmail.com");
ini_set("smtp_port","465");
ini_set("sendmail_from","eduardo@tstoryteller.com");
ini_set("auth_username","eduardo@tstoryteller.com");
ini_set("auth_password","flshmlmglln");

$to      = 'eilezaun@gmail.com';
$subject = 'the subject';
$message = 'hello';
$headers = 'From: edui_1988@hotmail.com' . "\r\n" .
 'Reply-To: edui_1988@hotmail.com';

if(mail($to, $subject, $message, $headers)){
      echo('ok');
    }
else{
      echo('not ok');
    }
	*/
require 'phpmailer/class.phpmailer.php';
require 'phpmailer/class.smtp.php';




if (isset($_REQUEST["team"]) && isset($_REQUEST["project_id"])) {

	$team = $_REQUEST["team"];
	$project_id = $_REQUEST["project_id"];
	$db = new PDO('mysql:host='.MYSQL_DBHOST.';dbname='.MYSQL_DBNAME,MYSQL_USER,MYSQL_PASS);
	$result = array();
	$log = array();

	$team =json_decode($team);
	$result[]=sizeof($team);
	for ($i=0;$i<sizeof($team);$i++){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($j = 0; $j < 8; $j++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		$st = $db->prepare('INSERT INTO audience (audience_first_name,audience_last_name,audience_email,project_id,password,roles) VALUES (:audience_first_name,:audience_last_name,:audience_email,:project_id,:password,:roles) ON DUPLICATE KEY UPDATE password=:password, roles=:roles, audience_first_name=:audience_first_name,audience_last_name=:audience_last_name');
		$st->bindValue(':audience_first_name', $team[$i]->audience_first_name, PDO::PARAM_STR);
		$st->bindValue(':audience_last_name', $team[$i]->audience_last_name, PDO::PARAM_STR);
		$st->bindValue(':audience_email', $team[$i]->audience_email, PDO::PARAM_STR);
		$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
		$st->bindValue(':password', md5($randomString), PDO::PARAM_STR);
		$st->bindValue(':roles', serialize($team[$i]->role), PDO::PARAM_STR);
		//$st->bindValue(':roles', array(), PDO::PARAM_STR);
		
		if($st->execute()){	
			
			$result[]=$db->lastInsertId();
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
				$mail->AddAddress($team[$i]->audience_email);
				$mail->Subject = 'Communicator Access';
				$msg = "Hello ".$team[$i]->audience_first_name.", <br><br>
				Please immediately login to the Communicator using this password: ".$randomString."<br><br>
				<center><a href='http://cve.cm.cr/'>http://cve.cm.cr/ </a></center><br>
				You can change your password if you want by accessing your profile.<br><br>
				We will use the communicator as Cosmic Voyage Enterprises secure medium of communication. We will be sharing with you important and exclusive information, so please make sure you access it frequently.<br><br>
				Talk to you soon!<br>";
				$mail->MsgHTML($msg);
				$mail->AltBody = 'Your new password is: '.$randomString;
				$mail->Send();
			}
			catch (phpmailerException $e) {
				$log[]= $e->errorMessage(); 
			} 
			catch (Exception $e) {
				$log[]= $e->getMessage(); 
			}
			
		}
		else{
			$result[]=$st->errorInfo();
			$result = array ("Response" => array("status"=>401,"message"=>"Error, try later"));
			exit( $_GET['callback'] . '('.json_encode($result).')');
		}
	}		
	$result = array ("response" => array("status"=>200,"message"=>"Everything is ok","log"=>$log));
	exit( $_GET['callback'] . '('.json_encode($result).')');
	

	//exit( json_encode($result));			
}
else{
	$result = array ("Response" => array("status"=>401,"message"=>"Missing arguments"));
	exit( $_GET['callback'] . '('.json_encode($result).')');	
}
?>