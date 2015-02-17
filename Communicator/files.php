<?php
session_start(); 
if(!isset($_SESSION["audience_id"])) {
	header( 'Location: index.php' ) ; 
}
else{
	include_once "api.php";

	$audience_id = $_SESSION['audience_id'];
	$api = new Conducttr_API($_SESSION['audience_id']);
	$value = $api->get_audience_details();
	if(!empty($value)){
		$profile_image = $value[0]['profile_image'];
		$audience_first_name = $value[0]['name'];
		$audience_last_name = $value[0]['lname'];
		$project_id = $value[0]['project_id'];	
	}
	else{
		$profile_image = 'profiles/you.png';
		$audience_first_name = 'You';
		$project_id = $_SESSION['PROJECT_ID'];		

	}
} 
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title> Files </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link href="css/main_communicator.css"  rel="stylesheet" type="text/css" />

		<?php 	
			echo '<link rel="shortcut icon" href="styles/'.$project_id.'/images/favicon.ico" />';
			echo '<link rel="stylesheet" href="styles/'.$project_id.'/communicator.css" type="text/css" />';  
			echo '<link href="styles/'.$project_id.'/images/favicon.png" rel="apple-touch-icon" />';
		?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
		<script type="text/javascript" src="js/spin.min.js"></script>
		<script type="text/javascript" src="js/communicator.js"></script>

		<script>
			var AUDIENCE_ID = '<?php echo $audience_id;?>';
			var PROFILE_IMAGE = '<?php echo $profile_image;?>';
			var AUDIENCE_FIRST_NAME = '<?php echo $audience_first_name;?>';
			var PROJECT_ID = '<?php echo $project_id;?>';

			var type = 'media';
		</script>
	</head>
	<body>
		<div id="files-wrapper" class="wrapper" >
			<div class="header" id="files-header"> 
				<?php
				echo '<a title="Back button" href="';
				if (isset($_REQUEST['b'])){
					switch($_GET['b']){
						case "w":
							echo 'msngr.php';
							break;
						case "f":
							echo 'gosocial.php';
							break;
						case "t":
							echo 'microblog.php';
							break;
						case "c":
							echo 'mail.php';
							break;	
						case "p":
							echo 'profiles.php?b='.$_GET['p'];
							break;
						case "b":
							echo 'badges.php?b='.$_GET['p'];
							break;	
						default:
							echo 'desktop.php';
					}
				}
				else echo 'desktop.php';
				echo '" style="left:26px;top: 0;bottom: 0;margin: auto;position:absolute;width: 40px; height: 20px;"><img src="styles/'.$project_id.'/images/back.png"></a>';

				?>				
				<img src='styles/<?php echo $project_id;?>/images/header.png' style="height:75%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;">
				<a id='home' title="Home button" href="desktop.php"  style="right:26px;top: 0;bottom: 0;margin: auto;position:absolute;width:30px; height:30px;"><img src="styles/<?php echo $project_id;?>/images/home.png"></a>

			</div>
			<div id="spinner"></div>

			<div id="content-area" class="files-area"></div>
			<div id="send-message-area"></div>

			<table id="buttons">
				<tr>
					<td onclick="window.location='profile.php?b=m&p=<?php echo $_GET['b'];?>'" ><img src="styles/<?php echo $project_id;?>/images/profile.png" style="height:30px;"><span>Profile</span></td>
					<?php if($api->get_badges_group() != 0) echo' <td onclick="window.location=\'badges.php?b=m&p='.$_GET['b'].'\'" ><img src="styles/'.$_SESSION['PROJECT_ID'].'/images/badges.png" style="height:30px;"><span>Badges</span></td>'; ?>
					<td><img src="styles/<?php echo $project_id;?>/images/files.png" style="height:25px;"><span>Files</span></td>
				</tr>
			</table>
		</div>
		<audio id='notification'><source src="styles/<?php echo $project_id;?>/sounds/notification.mp3" type="audio/mp3"></audio>

	</body>
</html>
