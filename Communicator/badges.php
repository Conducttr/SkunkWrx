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
		<title> Badges </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<?php 	
			echo '<link rel="shortcut icon" href="styles/'.$project_id.'/images/favicon.ico" />';
			echo '<link rel="stylesheet" href="styles/'.$project_id.'/communicator.css" type="text/css" />';  
			echo '<link href="styles/'.$project_id.'/images/favicon.png" rel="apple-touch-icon" />';
		?>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
		<script type="text/javascript" src="js/spin.min.js"></script>

	</head>
	<body>
		<div id="badges-wrapper" class="wrapper" >
			<div id="badges-header" class="header"> 
				<?php
					if (isset($_GET['b'])){
						echo '<a title="Back button" href="';
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
								echo 'profile.php?b='.$_GET['p'];
								break;
							case "m":
								echo 'files.php?b='.$_GET['p'];
								break;	
							default:
								echo 'desktop.php';
						}
					}
					else echo 'desktop.php;';
					echo '" style="left:26px;top: 0;bottom: 0;margin: auto;position:absolute;width: 40px; height: 20px;"><img src="styles/'.$project_id.'/images/back.png"></a>';

				?>
				<img src='styles/<?php echo $project_id;?>/images/header.png' style="height:75%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;">
				<a title="Home button" href="desktop.php"  style="right:26px;top: 0;bottom: 0;margin: auto;position:absolute;width:30px;height:30px;"><img src="styles/<?php echo $project_id;?>/images/home.png"></a>				
			</div>
			<div id="spinner"></div>

			<div id="content-area" class="badges-area">					

			<?php
				/*
				$api = new Conducttr_API($_SESSION['audience_id']);
				$groups = $api->get_badges();
				if (!empty($groups->results)){
					for ($i=0; $i<sizeof($groups->results);  $i++){
						echo "<div class='badge'>";
							echo "<div class='badge-image'>";
								echo"<img src='".$groups->results[$i]->image."'; alt='Badge Image' >";
							echo "</div>";
							echo "<div class='badge-description'>";
								echo "<br>";
								echo "<b>";
									echo $groups->results[$i]->name;
								echo "</b>";
								echo "<br>";
								echo $groups->results[$i]->description;
							echo "</div>";
							echo "<br>";
						echo "</div>";
					}
				}
				else{
					echo "<br/><center><span>You haven't earned any badges yet. </span></center>";

				}
				*/
			?>
			</div>
			<table id="buttons">
				<tr>
					<td onclick="window.location='profile.php?b=b&p=<?php echo $_GET['b'];?>'" ><img src="styles/<?php echo $project_id;?>/images/profile.png" style="height:30px;"><span>Profile</span></td>
					<?php if($api->get_badges_group() != 0) echo' <td><img src="styles/'.$_SESSION['PROJECT_ID'].'/images/badges.png" style="height:30px;"><span>Badges</span></td>'; ?>
					<td onclick="window.location='files.php?b=b&p=<?php echo $_GET['b'];?>'"><img src="styles/<?php echo $project_id;?>/images/files.png" style="height:25px;"><span>Files</span></td>
				</tr>
			</table>
		</div>
		<script>
			$(document).ready(function(){
				var opts = {
					lines: 13, // The number of lines to draw
					length: 20, // The length of each line
					width: 10, // The line thickness
					radius: 30, // The radius of the inner circle
					corners: 1, // Corner roundness (0..1)
					rotate: 0, // The rotation offset
					direction: 1, // 1: clockwise, -1: counterclockwise
					//color: '#000', // #rgb or #rrggbb or array of colors
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
						
				$.ajax({
					type: "GET",
					url: "api.php",
					data: {  
						'action': 'get_badges'
					},
					dataType: "json",
					success: function(data){
						console.log(JSON.stringify(data));
						if(data.results.length>0){
							for ( var i = 0; i<data.results.length; i++){
								$("#content-area").append("<div class='badge'><div class='badge-image'><img src='"+data.results[i].image+"'; alt='Badge Image'></div><div class='badge-description'><br><b>"+data.results[i].name+"</b><br>"+data.results[i].description+"</div><br></div>");
							}
						}
						else{
							$("#content-area").append("<br/><center><span>You haven't earned any badges yet. </span></center>");
						}
						spinner.stop();
					},
					error:function(data){
						console.log("ERROR: " + JSON.stringify(data));
						spinner.stop();
					}
					
				});	
						
			});
		</script>		
		
	</body>
</html>
