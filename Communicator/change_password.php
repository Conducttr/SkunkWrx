<?php
session_start(); 
include_once "config.php";
include_once "api.php";


if(!isset($_SESSION["audience_id"])) {
	header( 'Location: index.php') ; 
}
else{
	$audience_id = $_SESSION['audience_id'];
	$project_id = $_SESSION['PROJECT_ID'];
	
	$api = new Conducttr_API($_SESSION['audience_id']);
	$db = new PDO('mysql:host='.MYSQL_DBHOST.';dbname='.MYSQL_DBNAME,MYSQL_USER,MYSQL_PASS);
	$query="SELECT * FROM audience WHERE id = '".$audience_id."'";		
	$st = $db->prepare($query);
	$st->execute();
	//print_r($st->errorInfo());
	$data=$st->fetchAll(PDO::FETCH_ASSOC);
	
	if(isset($_POST['update'])){
		if($_POST['password_new'] != $_POST['password_new_confirm']){
			echo "<script type='text/javascript'>alert('Error: passwords are different');</script>";
		}
		
		else{ 
			
			if (!empty($data)){
				//echo "Old pass: ".$data[0]['password'];
				//echo "Old pass input: ".md5($_POST['password_old']);

				if (md5($_POST['password_old'])==$data[0]['password']){
					///$query="UPDATE audience SET password=:password WHERE id = :id'
					$st = $db->prepare('UPDATE audience SET password=:password WHERE id = :id');
					$st->bindValue(':id', $audience_id, PDO::PARAM_INT);
					$st->bindValue(':password',  md5($_POST['password_new']), PDO::PARAM_STR);
					if($st->execute())echo "<script type='text/javascript'>alert('Password updated correctly');</script>";
					else{
						echo "<script type='text/javascript'>alert('An error has ocurred, please try again');</script>";
					}
				}
				else echo "<script type='text/javascript'>alert('Error the password don't match');</script>";

			}
		}
		
		
		
	}
	//$data = $api->update_profile(isset($_POST['update']));
	//print_r($data);
	if(!empty($data)){
		$audience_email=$data[0]['audience_email'];
		$audience_phone=$data[0]['audience_phone'];
		$audience_first_name=$data[0]['audience_first_name'];
		$audience_last_name=$data[0]['audience_last_name'];
		$profile_image=$data[0]['profile_image'];
		$roles=$data[0]['roles'];
	}
}

?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title> Profile </title>
		
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
		
		<script>
			
			var enabled = false;
			function edit(){
				jQuery('#audience_phone').prop('disabled', enabled);
				jQuery('#audience_email').prop('disabled', enabled);
				jQuery('#audience_first_name').prop('disabled', enabled);
				jQuery('#audience_last_name').prop('disabled', enabled);
				//jQuery('#submit_btn').prop('disabled', enabled);		
				jQuery('#audience_phone').toggleClass('disabled');
				jQuery('#audience_email').toggleClass('disabled');
				jQuery('#audience_first_name').toggleClass('disabled');
				jQuery('#audience_last_name').toggleClass('disabled');	
				jQuery('#profileImage').toggle();
				jQuery('#submit_btn').toggle();
				enabled = !enabled;
			}
			function Spin(){
				var opts = {
					lines: 13, // The number of lines to draw
					length: 20, // The length of each line
					width: 10, // The line thickness
					radius: 30, // The radius of the inner circle
					corners: 1, // Corner roundness (0..1)
					rotate: 0, // The rotation offset
					direction: 1, // 1: clockwise, -1: counterclockwise
					color: '#000', // #rgb or #rrggbb or array of colors
					speed: 1, // Rounds per second
					trail: 60, // Afterglow percentage
					shadow: false, // Whether to render a shadow
					hwaccel: false, // Whether to use hardware acceleration
					className: 'spinner', // The CSS class to assign to the spinner
					zIndex: 2e9, // The z-index (defaults to 2000000000)
					top: '50%', // Top position relative to parent
					left: '50%' // Left position relative to parent
				};
				var target = document.getElementById('spinner');
				var spinner = new Spinner(opts).spin(target);	
				
			}
			
		</script>
	</head>
	<body>
		<div id="profile-wrapper" class="wrapper" >
			<div id="profile-header" class="header"> 
				<?php
				echo '<a title="Back button" href="profile.php?b='.$_GET['b'].'" style="left:26px;top: 0;bottom: 0;margin: auto;position:absolute;width: 40px; height: 20px;"><img src="styles/'.$project_id.'/images/back.png"></a>';
				?>
				<img src="styles/<?php echo $project_id;?>/images/header.png" style="height:75%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;">
				<a title="Home button" href='desktop.php<?php if(isset($_REQUEST['update']))echo"?b=1";?>'  style="right:26px;top: 0;bottom: 0;margin: auto;position:absolute;z-index:1;width:30px; height:30px;"><img src="styles/<?php echo $project_id;?>/images/home.png"></a>				
			</div>
			<div id='profile-area'>
				<form action='change_password.php' method='POST' enctype='multipart/form-data' onsubmit='Spin();return true;' style='height: 100%;'>
					<div id="spinner"></div>
					<div id='profile-area-header'>
						<!--
						<a title="Edit" href="#" onclick="edit();return false;" style="position:absolute;top:5px;right:5px;overflow:auto;text-decoration:none;color:white;font-size:12px;"><img src="images/edit.png"><br>Edit</a>
						-->
						<?php
							

						echo "<div class='profile_photo' title='Change profile photo'>";
							echo "<img id='profile_image' src='styles/".$project_id."/".$profile_image."'>";
							//echo "<input class='profileImage' type='file' id='profileImage' name='profileImage' accept='image/*'/>";
						echo "</div>";

						echo "<div class='name' >";
							if($audience_first_name!=""){	
								echo $audience_first_name; 
							}
							else {
								if($audience_last_name==""){	
									echo "Your Name";
								}
							}
							echo " ";
							if($audience_last_name!="")echo $audience_last_name;
						echo "<br>";
						echo "<span style='font-weight:normal;font-size:13px;'>";
							echo implode(' , ', unserialize($roles));
						echo "</span>";
						echo "</div>";

						?>	
					</div>
					<div id='profile-area-body'>
						<br><br>
						<div id='details'>
							<br>
							<span style='font-weight:bold;font-size:15px;padding-left:10px;'>Contact Details</span>
							<br><br>
							<?php
								if($project_id=='126'){

									//echo "<input type='text' id='audience_first_name' name='field[audience_first_name]' value='".$audience_first_name."' placeholder='Name'><br><br>";
									//echo "<input type='text' id='audience_last_name' name='field[audience_last_name]' value='".$audience_last_name."' placeholder='Last name'><br><br>";
									//echo "<input type='text' id='audience_phone' name='field[audience_phone]' value='".$audience_phone."' placeholder='Phone'><br><br>";
									//echo "<input type='text' id='audience_email' name='field[audience_email]' value='".$audience_email."' placeholder='Email'><br><br>";
									
									echo "<input type='password' id='password_old' name='password_old' placeholder='Old Password'><br><br>";		
									echo "<input type='password' id='password_new' name='password_new'  placeholder='New Password'><br><br>";
									echo "<input type='password' id='password_new_confirm' name='password_new_confirm'  placeholder='Confirm New Password'><br><br>";

									//echo "<input type='file' id='profileImage' name='profileImage' style='display:none'><br><br>";
									
									echo "<input type='hidden' id='update' name='update' value='1'>";
									echo "<input type='hidden' id='b' name='b' value='".$_REQUEST['b']."'>";
									
									//echo "<center><input type='submit' id='submit_btn' value='Update'></center><br>";
									echo "<center><input type='submit' id='submit_btn' value='Update'></center><br>";
								}
							?>
								<!--<button id="update_button" onClick='update_profile();' style='margin-bottom: 40px;display:none;'>Update your profile</button>-->
						</div>
					</div>
				</form>
			</div>
			<table id="buttons">
				<tr>
					<td onclick="window.location='profile.php?b=<?php echo $_GET['b'];?>'"> <img src="styles/<?php echo $project_id;?>/images/profile.png" style="height:30px;"><span>Profile</span></td>
					<?php if($api->get_badges_group() != 0) echo' <td onclick="window.location=\'badges.php?b=p&p='.$_GET['b'].'\'" ><img src="styles/'.$_SESSION['PROJECT_ID'].'/images/badges.png" style="height:30px;"><span>Badges</span></td>'; ?>
					<td onclick="window.location='files.php?b=p&p=<?php echo $_GET['b'];?>'"><img src="styles/<?php echo $project_id;?>/images/files.png" style="height:25px;"><span>Files</span></td>
				</tr>
			</table>	
		</div>		
	</body>
</html>
