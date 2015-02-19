<?php
session_start(); 
include_once "api.php";

if (isset($_SESSION['audience_id']) && isset($_SESSION['PROJECT_ID'])){	
	if (isset($_GET['b'])){		
		$audience_id = $_SESSION['audience_id'];
		$api = new Conducttr_API($_SESSION['audience_id']);
		$api->get_message_feeds();
		$icons = $api->print_icons(); 
	}
	else {
		$audience_id = $_SESSION['audience_id'];
		$api = new Conducttr_API($_SESSION['audience_id']);
		$icons = $api->print_icons(); 
	}
}
else{
	header('Location: index.php'); 
}
?>
<!doctype html>
<html lang="en" >
	<head>
		<meta charset="utf-8">
		<title> Conducttr Communicator </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link href="css/main_communicator.css"  rel="stylesheet" type="text/css" />

		<?php 	
			echo '<link rel="shortcut icon" href="styles/'.$_SESSION['PROJECT_ID'].'/images/favicon.ico" />';
			echo '<link rel="stylesheet" href="styles/'.$_SESSION['PROJECT_ID'].'/communicator.css" type="text/css" />';  
			echo '<link href="styles/'.$_SESSION['PROJECT_ID'].'/images/favicon.png" rel="apple-touch-icon" />';
		?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
		<script type="text/javascript" src="js/jquery.knob.js"></script>
				
	</head>
	<body>
		<div class="wrapper" id="desktop-wrapper">
			<div class="header" id="desktop-header" > 
				<?php echo "<img src='styles/".$_SESSION['PROJECT_ID']."/images/header.png' style='height:75%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;'>"; ?>
			</div>
			<div style="width:80%;margin:auto;padding-top: 1%;height:84%">
				<?php 				
					for ($i=0; $i<sizeof($icons);  $i++){
						if(strtolower ($icons[$i]['type'])=='blog' ||strtolower ($icons[$i]['type'])=='msngr' || strtolower ($icons[$i]['type'])=='microblog' || strtolower ($icons[$i]['type'])=='gosocial' || strtolower ($icons[$i]['type'])=='mail' || strtolower ($icons[$i]['type'])=='media'){

							if($icons[$i]['type']!='Media'){
								echo '<a id="'.$icons[$i]['type'].'" class="app_icon" href="'.$icons[$i]['type'].'.php" style="float:left;margin-left:8px;margin-right:8px;margin-top:4px;margin-bottom:4px;position:relative;width: 25%;text-align: center;text-decoration: none; font-weight: bold;">';
									if ($icons[$i]['NotRead']>0)echo "<div class='dot'></div>";
									echo "<img src='styles/".$_SESSION['PROJECT_ID']."/images/".$icons[$i]['type'].".png'  style='width:100%;'>";
									echo '<span>'.$icons[$i]['type'].'</span>';
								echo '</a>';	
							}
						}
					}					
				?>
				<div  style='overflow: auto;position: absolute;right: 0;left: 0;bottom: 7%;'>

					<div id='stats' style='position:relative;width:100%'>
						
					</div>
				</div>
			</div>
			<table id="buttons">
				<tr>
					<td onclick="window.location='profile.php?b=d'" ><img src="<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/profile.png" style="height:30px;"><span>Profile</span></td>
					<?php if($api->get_badges_group() != 0) echo' <td onclick="window.location=\'badges.php?b=d\'" ><img src="styles/'.$_SESSION['PROJECT_ID'].'/images/badges.png" style="height:30px;"><span>Badges</span></td>'; ?>
					<td onclick="window.location='files.php?b=d'"><img src="<?php echo "styles/".$_SESSION['PROJECT_ID']; ?>/images/files.png" style="height:25px;"><span>Files</span></td>
				</tr>
			</table>
		</div>
		<script>
			$(document).ready(function(){
				
				$.ajax({
					type: "GET",
					url: "api.php",
					data: {  
						'action': 'get_stats'
					},
					dataType: "json",
					success: function(data){
						console.log(JSON.stringify(data));	
						if (typeof data.results[2].progress !== 'undefined') {
							$("#stats").append("<div style='position:relative;width:50%;float:left;text-align:center;'><span> Progress </span><br><br><input type='text' value='0' id='progress' style='float:left;'></div>");
								$("#progress").knob({
									'readOnly':true,
									'inputColor': "red",
									'fgColor' : "red",
									'bgColor' : "transparent",
									'width' : 80,
									'height' : 80,
									'skin': "tron",
									'thickness': ".3",
									format : function (value) {
										return value + '%';
									},
								});
							setTimeout(function(){ 
							
								$({animatedVal: 0}).animate({animatedVal: progress}, {
									duration: 2000,
									easing: "swing", 
									step: function() { 
										$("#progress").val(Math.ceil(this.animatedVal)).trigger("change"); 
									}
								});
							}, 500);
							
						}
						if (typeof data.results[0].points !== 'undefined' && typeof data.results[1].max_points !== 'undefined') {
							$("#stats").append("<div style='position:relative;width:50%;float:right;text-align:center;'><span> Points </span><br><br><input type='text' value='0' id='points' style='float:right;'></div>");
								$("#points").knob({
									'readOnly':true,
									'inputColor': "yellow",
									'fgColor' : "yellow",
									'bgColor' : "transparent",
									'width' : 80,
									'height' : 80,
									'skin': 'tron',
									'thickness': ".3",
									'min':0,
									'max':0,
								});
							var points = data.results[0].points;
							var max_points = data.results[1].max_points;
							$('#points').trigger(
								'configure',
								{
									"max":max_points
				
								}
							);
							setTimeout(function(){ 
							$({animatedVal: 0}).animate({animatedVal: points}, {
								duration: 2000,
								easing: "swing", 
								step: function() { 
									$("#points").val(Math.ceil(this.animatedVal)).trigger("change"); 
								}
							}); 

						}, 500);
						}
									 
						var progress = data.results[2].progress; 		
						
						$('#points').trigger(
							'configure',
							{
								"max":max_points
			
							}
						);
						setTimeout(function(){ 
							$({animatedVal: 0}).animate({animatedVal: points}, {
								duration: 2000,
								easing: "swing", 
								step: function() { 
									$("#points").val(Math.ceil(this.animatedVal)).trigger("change"); 
								}
							}); 
							
							$({animatedVal: 0}).animate({animatedVal: progress}, {
								duration: 2000,
								easing: "swing", 
								step: function() { 
									$("#progress").val(Math.ceil(this.animatedVal)).trigger("change"); 
								}
							});
						}, 500);
					},
					error:function(data){
					console.log("ERROR: " + JSON.stringify(data));
					}
				});			
			});
		</script>
	</body>
</html>
