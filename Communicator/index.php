<?php
session_start();
session_unset();


$subdomain = array_shift((explode(".",$_SERVER['HTTP_HOST'])));
include_once "api.php";
$api = new Conducttr_API(-1);

if(is_numeric($subdomain)){
	$CONDUCTTR_PROJECT_ID = $subdomain;
	$CONDUCTTR_PROJECT_NAME = "";
}
else{
	$CONDUCTTR_PROJECT_ID = -1;
	$CONDUCTTR_PROJECT_NAME = $subdomain;
}
$PROJECT_ID = $api->check_project($CONDUCTTR_PROJECT_ID,$CONDUCTTR_PROJECT_NAME );
if ($PROJECT_ID){

?>

<!Doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title> Conducttr Communicator </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link href="css/main_communicator.css"  rel="stylesheet" type="text/css" />

		<?php
			echo '<link rel="shortcut icon" href="styles/'.$PROJECT_ID.'/images/favicon.ico" />';
			echo '<link rel="stylesheet" href="styles/'.$PROJECT_ID.'/communicator.css" type="text/css" />';
			echo '<link href=styles/'.$PROJECT_ID.'/"images/favicon.png" rel="apple-touch-icon" />'; 
		?> 
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
		<script type="text/javascript" src="js/spin.min.js"></script>

		<style type="text/css">
			label,input,select,h1 {font-family:Arial;}
			ui, li {list-style-type: none;} 
			.chzn-container {min-width:150px;}
			#conducttr-widget-team-members select.conducttr-widget-country-code,
			.conducttr-widget-country-code{width:47px;}
		 </style>	 
		 <style type="text/css">
			.ui-datepicker{display:none}
			/*----------------------------AJAX Loading Spinner---------------------------*/
			.loading{
				display:none;
				z-index:101;
				overflow:hidden;
				position:absolute;
			}
			.loading-content{
				font:normal 16px Tahoma, Geneva, sans-serif;
				width:150px;
				height:auto;
				background:no-repeat center #fff;
				border:2px solid #bcbcbc;
				text-align:center;
				padding:15px;
				-moz-border-radius:4px;
				-webkit-border-radius:4px;
			}
			.loading-spinner{
				margin:10px;
			}
			.loading-message{
				font-size:14px;
				margin:10px;
			}
			
			.mini-loading-spinner{
				display:inline;
				float:left;
				margin-right:10px;
			}
			

		</style>	

		
	</head>		
	<body>
		<div class="wrapper" id='signup-wrapper'>
			<div class="header" id='signup-header'> 
				
				<?php echo '<img src="styles/'.$PROJECT_ID.'/images/header.png" style="height:75%;position: absolute;top: 0;left: 0;bottom: 0;right: 0;">'; ?> 

			</div>
			<br>
			<div id='intro' ></div>
	
			<br><br>
			<div id="widget-login">

				<span style="padding-left:15px">Register / Log in </span>
				<br>
				<div>
					<ul id="login-list">	
						<li><label for="login-email">Email</label><input type="text" id="login-email" /></li>
						<li>
							<!--<label for="login-password">Password</label>-->

							<input type="hidden" id="login-password" value='pass'/>
						</li>
						<li><button  id='loginButton' onClick="login();" style="margin-bottom: 10px;" >Log in </button></li>

					</ul>
				</div>
			</div>
			<div id="spinner"></div>

			<br><br>
		</div>
				
		<script>
			function login(){
				var audience_email = document.getElementById('login-email').value;
				var password = document.getElementById('login-password').value;
				var project_id = <?php echo $PROJECT_ID;?>;
				
				var patt = new RegExp(/^(?!\.)((?!.*\.{2})[a-zA-Z0-9\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFFu20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF\.!#$%&'*+-/=?^_`{|}~\-\d]+)@(?!\.)([a-zA-Z0-9\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFF\u20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF\-\.\d]+)((\.([a-zA-Z\u0080-\u00FF\u0100-\u017F\u0180-\u024F\u0250-\u02AF\u0300-\u036F\u0370-\u03FF\u0400-\u04FF\u0500-\u052F\u0530-\u058F\u0590-\u05FF\u0600-\u06FF\u0700-\u074F\u0750-\u077F\u0780-\u07BF\u07C0-\u07FF\u0900-\u097F\u0980-\u09FF\u0A00-\u0A7F\u0A80-\u0AFF\u0B00-\u0B7F\u0B80-\u0BFF\u0C00-\u0C7F\u0C80-\u0CFF\u0D00-\u0D7F\u0D80-\u0DFF\u0E00-\u0E7F\u0E80-\u0EFF\u0F00-\u0FFF\u1000-\u109F\u10A0-\u10FF\u1100-\u11FF\u1200-\u137F\u1380-\u139F\u13A0-\u13FF\u1400-\u167F\u1680-\u169F\u16A0-\u16FF\u1700-\u171F\u1720-\u173F\u1740-\u175F\u1760-\u177F\u1780-\u17FF\u1800-\u18AF\u1900-\u194F\u1950-\u197F\u1980-\u19DF\u19E0-\u19FF\u1A00-\u1A1F\u1B00-\u1B7F\u1D00-\u1D7F\u1D80-\u1DBF\u1DC0-\u1DFF\u1E00-\u1EFF\u1F00-\u1FFF\u20D0-\u20FF\u2100-\u214F\u2C00-\u2C5F\u2C60-\u2C7F\u2C80-\u2CFF\u2D00-\u2D2F\u2D30-\u2D7F\u2D80-\u2DDF\u2F00-\u2FDF\u2FF0-\u2FFF\u3040-\u309F\u30A0-\u30FF\u3100-\u312F\u3130-\u318F\u3190-\u319F\u31C0-\u31EF\u31F0-\u31FF\u3200-\u32FF\u3300-\u33FF\u3400-\u4DBF\u4DC0-\u4DFF\u4E00-\u9FFF\uA000-\uA48F\uA490-\uA4CF\uA700-\uA71F\uA800-\uA82F\uA840-\uA87F\uAC00-\uD7AF\uF900-\uFAFF]){2,63})+)$/);
				
				if(audience_email=="" || password=="")
					alert("Please fill all the fields");	
				else{
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
				
				
					jQuery.ajax({  
						type: "POST", 
						url: "api.php",
						dataType: "json",
						data: {	
							'action' : 'login',
							'audience_email' : audience_email,
							'password' : password,
							'project_id' : project_id
						},
						success:function(result){
							console.log("Success: " + JSON.stringify(result));
							if(result.Response.status=="200"){
								if(result.Response.action=="registration"){
									window.location = "profile.php?b=1"

								}
								else if(result.Response.action=="login"){
									window.location = "desktop.php?b=1"
								}
							
							}
							else{
								spinner.stop();
								alert(result.Response.message);	
							}
						},
						error:function(result){
							console.log("Error: " + JSON.stringify(result));
							spinner.stop();							
							alert("An Error has ocurred, please refresh the page and try again");

						},
						abort:function(result){
							console.log("Abort: " + JSON.stringify(result));
							spinner.stop();
							alert("An Error has ocurred, please refresh the page and try again");
						}
					});
				}
			};
			
			function register(){
				var audience_email = document.getElementById('signup-email').value;
				var audience_email_confirm = document.getElementById('signup-email-confirm').value;
				var password = document.getElementById('signup-password').value;
				var password_confirm = document.getElementById('signup-password-confirm').value;
				var project_id = <?php echo $PROJECT_ID;?>;

				if(audience_email=="" || audience_email_confirm=="" || password=="" || password_confirm=="")alert("Please fill all the fields");	
				
				jQuery.ajax({  
					type: "POST", 
					url: "api.php",
					dataType: "json",
					data: {	
						'action' : 'register',
						'audience_email' : audience_email,
						'audience_email_confirm' : audience_email_confirm,
						'password' : password,
						'password_confirm' : password_confirm,
						'project_id' : project_id
					},
					success:function(result){
						console.log("Success: " + JSON.stringify(result));
						if(result.Response.status=="200"){
							if(result.Response.action=="registration"){
								window.location = "profile.php?b=1"

							}
							else if(result.Response.action=="login"){
								window.location = "desktop.php?b=1"
							}
							
						}
						else{
							alert(result.Response.message);	
						}
					},
					error:function(result){
						console.log("Error: " + JSON.stringify(result));
						alert("An Error has ocurred, please refresh the page and try again");
					},
					abort:function(result){
						console.log("Abort: " + JSON.stringify(result));
						alert("An Error has ocurred, please refresh the page and try again");
					}
					
				});
			};
		</script>
	</body>
</html>
<?php
}
else{
?>
<!Doctype html>
<html lang="en" style="background: #EDEDED;">
	<head>
		<meta charset="utf-8">
		<title> Conducttr Communicator </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="shortcut icon" href="images/favicon.ico">
		<link rel="stylesheet" href="communicator.css" type="text/css" />
		<link href="css/main_communicator.css"  rel="stylesheet" type="text/css" />

		<link href="images/favicon.png" rel="apple-touch-icon" />
	
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
	</head>
	<body>
		<div style="">
			<div  style="position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;width:500px;height:150px;text-align:center;font-size:28px;font-weight:bold;">
				This domain could be powered by
				</br></br>
				<span style="font-size:30px;"> <span>
				<a href='http://www.conducttr.com'><img src='images/Conducttr_logo.png' style="width:400px;"></a>
			</div>
		</div>	
	</body>
	</html>

<?php
}
?>
