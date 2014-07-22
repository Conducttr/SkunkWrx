<!--
 --------------------------------- Client - Server  verstion -------------------------------------
 ----------------------------- Interactive Video with Conducttr ----------------------------------

This code allows

-> Send API Calls to Conducttr based on the timeline 

-> Retrieve data from Conducttr


--------------------------------- Conducttr - 2014 ---------------------------------- 
--------------------------------- www.conducttr.com --------------------------------- 
-->

<html>
	<head>
		<title>Interactive Video With Conducttr - Client-Server version</title>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>	
	</head>
	<body>
		<div id="player"></div>
		<script>
		

			/* Conducttr api.php file path  - Edit this only if you change the path of the api.php file */ 
			var API_URL = "api.php"; 
			
			var VIDEOS = [];
			var VIDEO_JOURNEY= [];
			var TELL_CONDUCTTR_IM_HERE= [];
			var ACTUAL_VIDEO;
			var ACTUAL_VIDEO_ID;
			var VIDEO_WIDTH;
			var VIDEO_HEIGHT;
						
			var AUDIENCE_PHONE = '<?php echo $_GET['audience_phone']; ?>';

			setup();
		
			var currentTime;
			var lastTime = -1;			
						
			/* YOUTUBE IFRAME API */
			//This code loads the IFrame Player API code asynchronously.
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

			//This function creates an <iframe> (and YouTube player)after the API code downloads.
			var player;

			function onYouTubeIframeAPIReady() {
				player = new YT.Player('player', {
					height: VIDEO_HEIGHT,
					width: VIDEO_WIDTH,
					videoId: ACTUAL_VIDEO_ID,
					events: {
						'onReady': onPlayerReady,
						'onStateChange': onPlayerStateChange
					}
				});
			}

			//When the player starts, call resetNextVideo (You need to set this logic in Conducttr)
			function onPlayerReady(event) {
				event.target.playVideo();
				reset_NextVideo();
			}

			//The API calls this function when the player's state changes, when the video ends the the Default video is loaded.
			function onPlayerStateChange(event) {
				if (event.data == YT.PlayerState.ENDED ) {   
					ACTUAL_VIDEO = VIDEO_JOURNEY[ACTUAL_VIDEO][1];
					player.loadVideoById(VIDEOS[ACTUAL_VIDEO]);
					reset_NextVideo();
				}
			}
	
			/* Every second check if there is an Outbound || Inbound  Trigger to be fired */		
			setInterval(function (){
				
				currentTime = parseInt(player.getCurrentTime(), 10); 
				/* Check if there is an Outbound trigger to be fired */
				if (lastTime!=currentTime){
					lastTime = currentTime;
					for(var i = 0; i < TELL_CONDUCTTR_IM_HERE.length; i++) {
						if (TELL_CONDUCTTR_IM_HERE[i][0] == ACTUAL_VIDEO){
							var seconds = TELL_CONDUCTTR_IM_HERE[i][1];
							if (currentTime==seconds){ 
								conducttr_call (TELL_CONDUCTTR_IM_HERE[i][2]);
							}
						}
					}
					/* Check if there have been changes in the Database (Interactions from the Audience) */	
					get_NextVideo();
				}
			},1000);

			//FROM THE VIDEO TO CONDUCTTR Trigger - via API for Oauth Call	
			function conducttr_call(matchphrase){
				$.ajax({  
					type: "GET",  
					url: API_URL,
					data: {'action':"oauth_call", "method" : "GET", "matchphrase" : matchphrase , 'audience_phone': AUDIENCE_PHONE},
					dataType: "json"
				});  	
			}; 
			//INBOUND Trigger - Unauth API call to Conducttr
			function get_NextVideo(){
				$.ajax({  
					type: "GET",  
					url: API_URL,
					data: {'action':"get_NextVideo", 'audience_phone': AUDIENCE_PHONE},
					dataType: "json",
					success: function (result) {
						var nextvideo = result[0].nextvideo - 1;		
						if ((nextvideo>0)&&(nextvideo <= VIDEOS.length)&&(nextvideo != ACTUAL_VIDEO)){
							var nextvideo_id = VIDEOS[nextvideo];
							player.loadVideoById(nextvideo_id); 
							ACTUAL_VIDEO=nextvideo; 
						}
					}
				});  
			}; 
			
			//Reset Audience - Unauth API call to Conducttr 
			function reset_NextVideo(){
				$.ajax({  
					type: "GET",  
					url: API_URL,
					data: {'action':"reset_NextVideo", 'audience_phone': AUDIENCE_PHONE},
					dataType: "json"
				}); 
			};
			//Reset Audience - Unauth API call to Conducttr 
			function setup(){
				$.ajax({  
					type: "GET",  
					url: API_URL,
					data: {'action':"get_setup"},
					async: false,
					dataType: "json",
					success: function (result) {
						VIDEOS = result.videos;
						VIDEO_JOURNEY =  result.video_journey;
						TELL_CONDUCTTR_IM_HERE = result.tell_conducttr_im_here;
						VIDEO_WIDTH = result.video_width;
						VIDEO_HEIGHT = result.video_height;
						
						for(var i = 0; i < TELL_CONDUCTTR_IM_HERE.length; i++) {
							TELL_CONDUCTTR_IM_HERE[i][0] = TELL_CONDUCTTR_IM_HERE[i][0] -1;
							var hms = TELL_CONDUCTTR_IM_HERE[i][1];
							var a = hms.split(':');
							var seconds = ((+a[0]) * 60 + (+a[1]));
							TELL_CONDUCTTR_IM_HERE[i][1] = seconds;
						}
			
						for(var i = 0; i < VIDEO_JOURNEY.length; i++) {
							VIDEO_JOURNEY[i][0] = VIDEO_JOURNEY[i][0] - 1;
							VIDEO_JOURNEY[i][1] = VIDEO_JOURNEY[i][1] - 1;
						}
						
						ACTUAL_VIDEO = VIDEO_JOURNEY[0][0];
						ACTUAL_VIDEO_ID = VIDEOS[ACTUAL_VIDEO];
					}
					
				}); 
			};
		</script>
	</body>
</html>
