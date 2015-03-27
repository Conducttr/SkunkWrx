<?php
include_once "config.php";

if(isset($_REQUEST['audience_email']) && isset($_REQUEST['project_id'])){
 
	$db = new PDO('mysql:host='.MYSQL_DBHOST.';dbname='.MYSQL_DBNAME,MYSQL_USER,MYSQL_PASS);
	$db -> exec("set names utf8");
			
	$audience_email = $_REQUEST['audience_email'];
	$project_id = $_REQUEST['project_id'];
	$st = $db->prepare('SELECT * FROM audience WHERE audience_email=:audience_email AND project_id=:project_id');
	$st->bindValue(':audience_email', $audience_email, PDO::PARAM_STR);
	$st->bindValue(':project_id', $project_id, PDO::PARAM_INT);
	$st->execute();		
	$data=$st->fetchAll(PDO::FETCH_ASSOC);
	print_r($data);
	print_r($st->errorInfo());
	if (!empty($data)){
		$audience_id = $data[0]['id'];
		$st = $db->prepare('DELETE FROM messages WHERE audience_id=:audience_id;');
		$st->bindValue(':audience_id', $audience_id, PDO::PARAM_INT);
		$st->execute();
		print_r($st->errorInfo());

		$st = $db->prepare('DELETE FROM inventory_items WHERE audience_id=:audience_id;');
		$st->bindValue(':audience_id', $audience_id, PDO::PARAM_INT);
		$st->execute();
		print_r($st->errorInfo());
		
		$st = $db->prepare('SELECT * FROM inventory_items WHERE audience_id=:audience_id;');
		$st->bindValue(':audience_id', $audience_id, PDO::PARAM_INT);
		$st->execute();
		$messages=$st->fetchAll(PDO::FETCH_ASSOC);
		print_r($messages);

		print_r($st->errorInfo());
	}
	else echo 'Audience not found';
}
else echo 'Audience and project not specified';
?>
