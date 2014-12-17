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
						<!--<li style="text-align:right;"><a href="password_reset.php" style="color:white;font-size:12px;"> Reset your password</a></li>-->

					</ul>
				</div>
			</div>
			<div id="spinner"></div>

			<br><br>
			<!--
			<div style="width:90%;overflow: auto;margin:auto;padding-top: 10px;color:white;font-weight:bold; font-size: 15px;background-color:rgba(255,255,255,0.3);border-radius:5px;">
				<span style="padding-left:15px"> New user? Sign up </span>
				<br>
				<div id="widget-signup">
					<ul id="signup-list">	
						<li><label for="signup-email">Email</label><input type="text" id="signup-email" /></li>
						<li><label for="signup-email-confirm" >Email (confirm)</label><input type="text" id="signup-email-confirm" /></li>
						<li>
							<label for="signup-password">Password</label>
							<input type="password" id="signup-password"/>
						</li>
						<li>
							<label for="signup-password-confirm">Password (confirm)</label>
							<input type="password" id="signup-password-confirm"/>
						</li>
						<li><button  onClick="register();" style="margin-bottom: 10px;" >Sign up </button></li>
					</ul>
				</div>
			</div>
			-->
		</div>
				
		<script>
			function login(){
				var audience_email = document.getElementById('login-email').value;
				var password = document.getElementById('login-password').value;
				var project_id = <?php echo $PROJECT_ID;?>;

				if(audience_email=="" || password=="")alert("Please fill all the fields");	
						
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
								window.location = "desktop.php?b=1"
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
							window.location = "desktop.php?b=1"
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
