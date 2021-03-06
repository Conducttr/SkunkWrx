<!--
 ---------------------- Personalized Interactive Video ---------------------------
 ----------------- Client Side  - Interactive Video with Conducttr ------------------

This code allows

-> Send API Calls to Conducttr based on the timeline 

-> Retrieve data from Conducttr


*This code is intended for small audiences.

If you need it for bigger audiences, you must implement Server-side interactive
video with Oauth calls.
--------------------------------- Conducttr - 2014 ---------------------------------- 
--------------------------------- www.conducttr.com --------------------------------- 
-->

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>	
<div id="player"></div>

<script>
	
	/*------------------------------- Edit the information below ------------------------*/        
	
	
	/* Conducttr's API Consumer Key */
	var CONDUCTTR_CONSUMER_KEY = "MY_CONDUCTTR_CONSUMER_KEY"; 
	
	/* Conducttr Project ID */ 
	var CONDUCTTR_PROJECT_ID = "MY_CONDUCTTR_PROJECT_ID"; 
		
	/* VIDEOS - YOUTUBE_ID of the VIDEOS *NOTE: be careful with the order :) */
	var VIDEOS = ["FirstVideoID", "SecondVideoID" , "ThirdVideoID"];

	/* ADJUST THE SIZE OF THE VIDEO PLAYER*/
	var VIDEO_PLAYER_WIDTH = '690' ;
	var VIDEO_PLAYER_HEIGHT = '300';
	
	/* VIDEO_JOURNEY - Journey through the videos [VIDEOtoBEPLAYED, VIDEOTOBEPLAYEDBYDEFAULT] */
	var VIDEO_JOURNEY = [
		[1,1],
		[2,2],
		[3,3]
	];
	
	/* INTERACTIONS - TELL_CONDUCTTR_IM_HERE  
	 Specify in which video, when in it's timeline and with which 'matchphrase' the video will 'call' Conducttr
	
		[ID_VIDEO,TIME (mm:ss), MATCHPHRASE]  
		
	*/
	var TELL_CONDUCTTR_IM_HERE =[
		[1,"0:01","one"],
		[1,"0:03","two"],
		[1,"0:06","three"],
		[1,"0:12","four"],
		[1,"0:13","five"]
	];

	/* POLLING INTERVAL - ASK_FOR_NEXT_VIDEO
	
	Specify when the video will 'ask' Conducttr if there has been interactions
		
		[ID_VIDEO, START TIME (mm:ss), END TIME (mm:ss)] 
	 
	*/
	 
	var ASK_FOR_NEXT_VIDEO = [
		[1,"0:25","0:45"]
	];


	/*------------------------------- Edit the information above ------------------------*/        
	
	
	
	/* CODE to PARSE THE PARAMETERS */
	
	var AUDIENCE_PHONE = '<?php echo($_GET['audience_phone']); ?>';
	var FETCH_RESULTS = false;
	var lastTime = -1; 
	var currentTime;
	for(var i = 0; i < TELL_CONDUCTTR_IM_HERE.length; i++) {
		TELL_CONDUCTTR_IM_HERE[i][0] = TELL_CONDUCTTR_IM_HERE[i][0] -1;
		var hms = TELL_CONDUCTTR_IM_HERE[i][1];
		var a = hms.split(':');
		var seconds = ((+a[0]) * 60 + (+a[1]));
		TELL_CONDUCTTR_IM_HERE[i][1] = seconds;
	}
	for(var i = 0; i < ASK_FOR_NEXT_VIDEO.length; i++) {
		
		ASK_FOR_NEXT_VIDEO[i][0] = ASK_FOR_NEXT_VIDEO[i][0] -1;
		var startHMS = ASK_FOR_NEXT_VIDEO[i][1];
		var a = startHMS.split(':');
		var startSeconds = ((+a[0]) * 60 + (+a[1]));
		
		ASK_FOR_NEXT_VIDEO[i][1] = startSeconds;
		
		var endHMS = ASK_FOR_NEXT_VIDEO[i][2];
		var b = endHMS.split(':');
		var endSeconds = ((+b[0]) * 60 + (+b[1]));
		
		ASK_FOR_NEXT_VIDEO[i][2] = endSeconds;
	}
	for(var i = 0; i < VIDEO_JOURNEY.length; i++) {
		VIDEO_JOURNEY[i][0] = VIDEO_JOURNEY[i][0] - 1;
		VIDEO_JOURNEY[i][1] = VIDEO_JOURNEY[i][1] - 1;
	}
					
	/* Every second check if there is an Outbound Trigger to be fired */		
	setInterval(function(){
		currentTime = parseInt(player.getCurrentTime(), 10); 
		FETCH_RESULTS = false; 
		if (lastTime != currentTime){
			lastTime = currentTime;
			for(var i = 0; i < TELL_CONDUCTTR_IM_HERE.length; i++) {
				if (TELL_CONDUCTTR_IM_HERE[i][0] == ACTUAL_VIDEO){
					var seconds = TELL_CONDUCTTR_IM_HERE[i][1];
					if (currentTime==seconds) conducttr_call (TELL_CONDUCTTR_IM_HERE[i][2]);
				}
			}
			for(var i = 0; i < ASK_FOR_NEXT_VIDEO.length; i++) {
				if (ASK_FOR_NEXT_VIDEO[i][0] == ACTUAL_VIDEO){
					var startSeconds=ASK_FOR_NEXT_VIDEO[i][1];
					var endSeconds = ASK_FOR_NEXT_VIDEO[i][2];
					if (currentTime>=startSeconds && currentTime<=endSeconds ){
							FETCH_RESULTS = true; 
					}
				}
			}
		}
	},1000);

	//Every 5 seconds check if there's INBOUND Triggers
	setInterval(function(){
		if (FETCH_RESULTS == true){
			var decision = get_NextVideo();
		}			
	},5000);
	
	//OUTBOUND Trigger - Unauth API call to Conducttr
	function conducttr_call(matchphrase){
		$.ajax({  
			type: "GET",  
			url: "https://api.conducttr.com/v1/project/"+PROJECT_ID+"/unauth/"+matchphrase,
			data: {'consumer_key':CONSUMER_KEY, 'audience_phone': AUDIENCE_PHONE},
			dataType: "jsonp"
		});  
	}; 
	
	//INBOUND Trigger - Unauth API call to Conducttr
	function get_NextVideo(){
		$.ajax({  
			type: "GET",  
			url: "https://api.conducttr.com/v1/project/"+PROJECT_ID+"/unauth/NextVideo",
			data: {'consumer_key':CONSUMER_KEY, 'audience_phone': AUDIENCE_PHONE},
			dataType: "jsonp",
			success: function (result) {
				var nextvideo = result[0].next_video - 1;		
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
			type: "POST",  
			url: "https://api.conducttr.com/v1/project/"+PROJECT_ID+"/unauth/NextVideo",
			data: {'consumer_key':CONSUMER_KEY, 'audience_phone': AUDIENCE_PHONE , 'nextvideo' : 0 },
			dataType: "json"
		});  
	}; 

	/* YOUTUBE IFRAME API */
	//This code loads the IFrame Player API code asynchronously.
	var tag = document.createElement('script');

	tag.src = "https://www.youtube.com/iframe_api";
	var firstScriptTag = document.getElementsByTagName('script')[0];
	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	//This function creates an <iframe> (and YouTube player)after the API code downloads.
	var player;
		
	var ACTUAL_VIDEO = VIDEO_JOURNEY[0][0];
	var ACTUAL_VIDEO_ID = VIDEOS[ACTUAL_VIDEO];
		
	function onYouTubeIframeAPIReady() {
		player = new YT.Player('player', {
			height: VIDEO_PLAYER_HEIGHT,
			width: VIDEO_PLAYER_WIDTH,
			videoId: ACTUAL_VIDEO_ID,
			events: {
				'onReady': onPlayerReady,
				'onStateChange': onPlayerStateChange
			}
		});
	}

	//When the player starts, call resetProject 
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
</script>
