<?php
session_start(); 

if(!isset($_SESSION["audience_id"] )) {
	header( 'Location: index.php' ) ; 
}
else{
	include_once "api.php";
	$audience_id = $_SESSION['audience_id'];
	$api = new Conducttr_API($_SESSION['audience_id']);
	$value = $api->get_audience_details();
	if(!empty($value)){
		$profile_image = $value[0]['profile_image'];
		$audience_first_name = " ".$value[0]['audience_first_name']." ";
		$audience_last_name = $value[0]['audience_last_name'];
		$project_id = $value[0]['project_id'];
		$delay =$api->get_delay();
	}
	else{
		$profile_image = 'styles/'.$_SESSION['PROJECT_ID'].'/profiles/you.png';
		$audience_first_name = 'You';
		$project_id = $_SESSION['PROJECT_ID'];	
		$delay =3000;
	}
} 
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title> Mail </title>
		
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
		<script type="text/javascript" src="js/patternLock.min.js"></script>		
		<script type="text/javascript" src="js/date.js"></script>	

		<script type="text/javascript" src="js/preventOverScroll.js"></script>		

		<script>
			var AUDIENCE_ID = '<?php echo $audience_id;?>';
			var PROJECT_ID = '<?php echo $project_id;?>';
			var PROFILE_IMAGE = '<?php echo $profile_image;?>';
			var AUDIENCE_FIRST_NAME = '<?php echo $audience_first_name;?>';
			var DELAY = '<?php echo $delay;?>';

			var type = 'Mail';
		</script>
	</head>
	<body>
		<div id="mail-wrapper" class="wrapper" >
			<div class="header" id="mail-header"> 
				<a id='back' title="Back button" href="#" onclick="back();return false;" ><img src="<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/mail_back.png"></a>
				<div style="height:65%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;margin:auto;" ><img src='<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/mail_nohex.png' style="height:100%;"><img src='<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/mail_logo.png' style="height:100%;" ></div>
				<a id='home' title="Home button" href="desktop.php" ><img src="<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/home.png"></a>
			</div>
			<div id="spinner"></div>

			<div id="content-area" class="mail-area"></div>
			<div id="send-message-area"></div>
			<table id="buttons">
				<tr>
					<td onclick="window.location='profile.php?b=c'" ><img src="<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/profile.png" style="height:30px;"><span>Profile</span></td>
					<?php if($api->get_badges_group() != 0) echo' <td onclick="window.location=\'badges.php?b=c\'" ><img src="styles/'.$_SESSION['PROJECT_ID'].'/images/badges.png" style="height:30px;"><span>Badges</span></td>'; ?>
					<td onclick="window.location='files.php?b=c'"><img src="<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/files.png" style="height:25px;"><span>Files</span></td>
				</tr>
			</table>
		</div>
		<audio id="sound1"><source src="styles/<?php echo $project_id;?>/sounds/Email sending_mixdown.mp3" type="audio/mp3" ></source></audio>
		<audio id="sound2"><source src="styles/<?php echo $project_id;?>/sounds/1978.mp3" type="audio/mp3" ></source></audio>
		<audio id="sound3"><source src="styles/<?php echo $project_id;?>/sounds/2242.mp3" type="audio/mp3" ></source></audio>
		<audio id="sound4"><source src="styles/<?php echo $project_id;?>/sounds/2243.mp3" type="audio/mp3"></source></audio>
		<audio id="sound5"><source src="styles/<?php echo $project_id;?>/sounds/MKFX_MULTIMEDIA_TONE_CORRECT_005.wav" type="audio/wav"></source></audio>
		
		<audio id='notification'><source src="styles/<?php echo $project_id;?>/sounds/notification.mp3" type="audio/mp3"></audio>

	</body>
</html>
