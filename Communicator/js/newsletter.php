<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Conducttr - Email preferences </title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="description" content="" />
</head>
<body>
<style>
body{
	background-color: #E9EAED;
}
#preferences_container{
		
	background-color: white;
	border: 1px solid #E1E2E3;
	position: absolute;
	top: 0;
	bottom: 0;
	margin: auto;
	left: 0;
	right: 0;
	overflow:auto;
	width:600px;
	max-width:94%;
	height: 530px;
	max-height: 98%;
	border-radius: 3px;
	padding: 1% 3% 1% 3%;
}	
.preference{
	width:100%;
	overflow:auto;
	
}	
.preference_name{
	float:left;
	width:260px;
	max-width:100%;
	font-weight:bold;
}
.preference_description{
	margin-top: 30px;
	padding-bottom: 10px;
	margin-bottom: 30px;
	border-bottom: 1px solid #cecece;
}


.onoffswitch {
	float:left;
    position: relative; width: 50px;
    -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
}
.onoffswitch-checkbox {
    display: none;
}
.onoffswitch-label {
    display: block; overflow: hidden; cursor: pointer;
    border: 2px solid #666666; border-radius: 15px;
}
.onoffswitch-inner {
    display: block; width: 200%; margin-left: -100%;
    -moz-transition: margin 0.3s ease-in 0s; -webkit-transition: margin 0.3s ease-in 0s;
    -o-transition: margin 0.3s ease-in 0s; transition: margin 0.3s ease-in 0s;
}
.onoffswitch-inner:before, .onoffswitch-inner:after {
    display: block; float: left; width: 50%; height: 15px; padding: 0; line-height: 15px;
    font-size: 10px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
    -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
    border-radius: 15px;
    box-shadow: 0px 7.5px 0px rgba(0,0,0,0.08) inset;
}
.onoffswitch-inner:before {
    content: "Yes";
    padding-left: 10px;
    background-color: #EC1F23; color: #FFFFFF;
    border-radius: 15px 0 0 15px;
}
.onoffswitch-inner:after {
    content: "No";
    padding-right: 10px;
    background-color: #FFFFFF; color: #666666;
    text-align: right;
    border-radius: 0 15px 15px 0;
}
.onoffswitch-switch {
    display: block; width: 15px; margin: 0px;
    background: #FFFFFF;
    border: 2px solid #666666; border-radius: 15px;
    position: absolute; top: 0; bottom: 0; right: 31px;
    -moz-transition: all 0.3s ease-in 0s; -webkit-transition: all 0.3s ease-in 0s;
    -o-transition: all 0.3s ease-in 0s; transition: all 0.3s ease-in 0s; 
    background-image: -moz-linear-gradient(center top, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 80%); 
    background-image: -webkit-linear-gradient(center top, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 80%); 
    background-image: -o-linear-gradient(center top, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 80%); 
    background-image: linear-gradient(center top, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 80%);
    box-shadow: 0 1px 1px white inset;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
    margin-left: 0;
}
.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
    right: 0px; 
}

@media only screen and (min-device-width: 320px) and (max-device-width: 800px) {
#preferences_container{ border:0;}

}
</style>


<?
include_once "Oauth/OAuthStore.php";
include_once "Oauth/OAuthRequester.php";

define("CONDUCTTR_CONSUMER_KEY", "94cb532b04484965c34ef46d68350045054181800");
define("CONDUCTTR_CONSUMER_SECRET", "3dfeb8a54b844d09b4035765da68cdf3");
define("CONDUCTTR_PROJECT_ID", "1742");
define("CONDUCTTR_OAUTH_HOST","https://my.conducttr.com");
define("CONDUCTTR_REQUEST_TOKEN_URL", CONDUCTTR_OAUTH_HOST . "/oauth/request-token");
define("CONDUCTTR_AUTHORIZE_URL", CONDUCTTR_OAUTH_HOST . "/oauth/authorize");
define("CONDUCTTR_ACCESS_TOKEN_URL", CONDUCTTR_OAUTH_HOST . "/oauth/access-token");
define("CONDUCTTR_API_URL", "https://api.conducttr.com/v1/project/");

$options = array('consumer_key' => CONDUCTTR_CONSUMER_KEY, 'consumer_secret' => CONDUCTTR_CONSUMER_SECRET);
OAuthStore::instance("2Leg", $options);

if ($_REQUEST['b']==1){
	try{
		$audience_email = $_REQUEST['audience_email'];
		
		$newsletter= "&newsletter=";
		if(isset($_REQUEST['newsletter']))$newsletter=$newsletter.$_REQUEST['newsletter']."&newsletter-yes";
		else $newsletter=$newsletter."0"."&newsletter-no";
		
		$features= "&features=";
		if(isset($_REQUEST['features']))$features=$features.$_REQUEST['features']."&features-yes";
		else $features=$features."0"."&features-no"; 
		
		$alerts= "&alerts=";
		if(isset($_REQUEST['alerts']))$alerts=$alerts.$_REQUEST['alerts']."&alerts-yes";
		else $alerts=$alerts."0"."&newsletter-no";
		
		$thoughts= "&thoughts=";
		if(isset($_REQUEST['thoughts']))$thoughts=$thoughts.$_REQUEST['thoughts']."&thoughts-yes";
		else $thoughts=$thoughts."0"."&thoughts-no";
		
		// Obtain a request object for the request we want to make
		$request = new OAuthRequester(CONDUCTTR_REQUEST_TOKEN_URL, "POST");
		$result = $request->doRequest(0);
		parse_str($result['body'], $params);
		$request_uri = CONDUCTTR_API_URL.CONDUCTTR_PROJECT_ID."/subscriptions?audience_email=".$audience_email.$newsletter.$features.$alerts.$thoughts;

		$request = new OAuthRequester($request_uri, 'POST', $params);
		$result = $request->doRequest();
		$status = json_decode($result['body']);
		if ($status->response->status == "200"){ 
			echo "<div id='preferences_container'>";
				echo "<h2>Communications Preferences</h2>";
				echo "<h4>Your preferences has been updated</h4>";
			echo "</div>";
		}
		else{
			echo "An error occurred please try again later";
	
		}
		//var_dump($subscriptions->results);
	}
	catch(OAuthException2 $e){
		echo "An error occurred please try again later" . $e->getMessage();
	}

}
else{
	try{
		$audience_email = $_REQUEST['audience_email'];
		
		// Obtain a request object for the request we want to make
		$request = new OAuthRequester(CONDUCTTR_REQUEST_TOKEN_URL, "GET");
		$result = $request->doRequest(0);
		parse_str($result['body'], $params);
		// now make the request. 
		$request_uri = CONDUCTTR_API_URL.CONDUCTTR_PROJECT_ID."/subscriptions?audience_email=".$audience_email ;
		$request = new OAuthRequester($request_uri, 'GET', $params);
		$result = $request->doRequest();
		$subscriptions = json_decode($result['body']);

		echo "<div id='preferences_container'>";
			echo "<h2>Communications Preferences</h2>";
			echo "<h4>Please use the buttons below to choose which emails you'd like to receive from us</h4><br>";
			echo "<form method='POST' action=''>";
				echo "<input type='hidden' name='audience_email' value='".$_REQUEST['audience_email']."'>";

				echo "<input type='hidden' name='b' value='1'>";
				
				echo "<div class='preference'>";
					echo "<div class='preference_name'>The monthly Communique</div>";
					echo "<div class='onoffswitch'>
								<input type='checkbox' name='newsletter' class='onoffswitch-checkbox' id='newsletter' value='1' ";
					if($subscriptions->results[0]->newsletter==1)echo "checked";			
					echo			">
								<label class='onoffswitch-label' for='newsletter'>
									<span class='onoffswitch-inner'></span>
									<span class='onoffswitch-switch'></span>
								</label>
							</div>";
					echo "<div class='preference_description'>A round up of transmedia projects and events</div>";

				echo "</div>";

				echo "<div class='preference'>";
					echo "<div class='preference_name'>New Conducttr features</div>";
					echo "<div class='onoffswitch'>
								<input type='checkbox' name='features' class='onoffswitch-checkbox' id='features' value='1' ";
					if($subscriptions->results[1]->features==1)echo "checked";			
					echo		">
								<label class='onoffswitch-label' for='features'>
									<span class='onoffswitch-inner'></span>
									<span class='onoffswitch-switch'></span>
								</label>
							</div>";
						echo "<div class='preference_description'>Getting the most from your Conducttr subscription</div>";
				
				echo "</div>";
				echo "<div class='preference'>";
					echo "<div class='preference_name'>Alerts </div>";
					echo "<div class='onoffswitch'>
								<input type='checkbox' name='alerts' class='onoffswitch-checkbox' id='alerts' value='1' ";
					if($subscriptions->results[2]->alerts==1)echo "checked";			
					echo		">
								<label class='onoffswitch-label' for='alerts'>
									<span class='onoffswitch-inner'></span>
									<span class='onoffswitch-switch'></span>
								</label>
							</div>";
					echo "<div class='preference_description'>Must know news about upgrades, bugs to be fixed, service outages</div>";
				
				echo "</div>";
				echo "<div class='preference'>";

					echo "<div class='preference_name'>Thought Leadership </div>";
					echo "<div class='onoffswitch'>
								<input type='checkbox' name='thoughts' class='onoffswitch-checkbox' id='thoughts' value='1' ";
					if($subscriptions->results[3]->thoughts==1)echo "checked";			
					echo		">
								<label class='onoffswitch-label' for='thoughts'>
									<span class='onoffswitch-inner'></span>
									<span class='onoffswitch-switch'></span>
								</label>
							</div>";
					echo "<div class='preference_description' >Only content from us (TSL) - presentations, videos & blog posts (interviews maybe, podcasts</div>";
				echo "</div>";

				echo "<center><input type='submit' value='Update preferences'></center>";
			echo "</form>";
		echo "</div>";
	}
	catch(OAuthException2 $e){
		echo "An error occurred please try again later";
	}
}
?>
</body>
</html>
