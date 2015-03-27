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
		$audience_first_name = $value[0]['audience_first_name'];

		$audience_last_name = $value[0]['audience_last_name'];
		$project_id = $value[0]['project_id'];	
		$delay =$api->get_delay();
	}
	else{
		$profile_image = 'styles/'.$_SESSION['PROJECT_ID'].'/profiles/you.png';
		$audience_first_name = 'You';
		$project_id = $_SESSION['PROJECT_ID'];		
		$delay = 3000;
	}
} 
?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title> Msngr </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link href="css/main_communicator.css"  rel="stylesheet" type="text/css" />

		<?php 	
			echo '<link rel="shortcut icon" href="styles/'.$project_id.'/images/favicon.ico" />';
			echo '<link rel="stylesheet" href="styles/'.$project_id.'/communicator.css" type="text/css" />';  
			echo '<link href="styles/'.$project_id.'/images/favicon.png" rel="apple-touch-icon" />';
		?>
		<meta content='True' name='HandheldFriendly' />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		
		<link href="css/lightbox.css" rel="stylesheet"  type="text/css" />
		<link href="css/patternLock.css"  rel="stylesheet" type="text/css" />

		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
		<script type="text/javascript" src="js/spin.min.js"></script>
		<script type="text/javascript" src="js/communicator.js"></script>
		<script type="text/javascript" src="js/lightbox.min.js"></script>
		<script type="text/javascript" src="js/patternLock.min.js"></script>		
		<script type="text/javascript" src="js/preventOverScroll.js"></script>
		<script type="text/javascript" src="js/date.js"></script>
		
		<script>
			var AUDIENCE_ID = '<?php echo $audience_id;?>';
			var PROFILE_IMAGE = '<?php echo $profile_image;?>';
			var AUDIENCE_FIRST_NAME = '<?php echo $audience_first_name;?>';
			var PROJECT_ID = '<?php echo $project_id;?>';
			var DELAY = '<?php echo $delay;?>';

			var type = 'Msngr';
		</script>

	</head>
	<body>

		<div id="msngr-wrapper" class="wrapper" >

			<div class="header" id="msngr-header"> 
				<a id='back' title="Back button" href="#" onclick="back(type);return false;" ><span class="helper"></span><img src="styles/<?php echo $project_id;?>/images/back.png"></a>
				<div style="height:65%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;margin:auto;" ><img src='styles/<?php echo $project_id;?>/images/msngr_nohex.png' style="height:100%;"><img src='styles/<?php echo $project_id;?>/images/msngr_logo.png' style="height:100%;" ></div>
				<a id='home' title="Home button" href="desktop.php" ><span class="helper"></span><img src="styles/<?php echo $project_id;?>/images/home.png"></a>
			</div>
			<div id="spinner"></div>
			
			<div id="content-area" class="msngr-area"></div>						
			
			<div id="send-message-area"></div>

			<table id="buttons">
				<tr>
					<td onclick="window.location='profile.php?b=w'" ><img src="styles/<?php echo $project_id;?>/images/profile.png" style="height:30px;"><span>Profile</span></td>
					<?php if($api->get_badges_group() != 0) echo' <td onclick="window.location=\'badges.php?b=w\'" ><img src="styles/'.$_SESSION['PROJECT_ID'].'/images/badges.png" style="height:30px;"><span>Badges</span></td>'; ?>
					<td onclick="window.location='files.php?b=w'"><img src="styles/<?php echo $project_id;?>/images/files.png" style="height:25px;"><span>Files</span></td>
				</tr>
			</table>

		</div>
		<audio id='notification'><source src="styles/<?php echo $project_id;?>/sounds/notification.mp3" type="audio/mp3"></audio>
	</body>
</html>
