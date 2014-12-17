<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title> Digital Locker </title>
		
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<link rel="shortcut icon" href="images/favicon.ico">
		<link rel="stylesheet" href="chatsfield.css" type="text/css" />
		<link href="images/favicon.png" rel="apple-touch-icon" />
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		<meta name="description" content="" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script>

		var lastname = "";
		var lastposition = "right";
		var lastJSONobject;
		var messages_array = [];
		var last_length;

		function Refresh_messages(){
			$(document).ready(function(){
				$.ajax({
					type: "POST",
					url: "api.php",
					data: {  
						'action': 'fakebook_refresh',
						'audience_phone' : "447936636769",
					},
					dataType: "json",
					success: function(data){
						messages_array = [];
						JSONObject_to_array(data.results);
						console.log("New messages array: " + messages_array);
						console.log("New messages array length: " + messages_array.length);
						console.log("Old length: " + last_length);

						Array_print( messages_array,last_length );
						last_length = messages_array.length;
					},
					error:function(data){
					   console.log("ERROR: " + JSON.stringify(data));
					}
				});
			});
		}
		function SendAnswer(objButton){
			
			$("#send-message-area").empty();
			$("#fakebook-area").append('<div class="message_wrapper"><div class="message_right"><span>You</span><br/>'+objButton.name+' </div></div>');
			$("#fakebook-area").animate({scrollTop: $('#fakebook-area').prop("scrollHeight")}, 500);
			setTimeout($("#send-message-area").append('<div id="typing-area"> Typing </div>'),1000);
			var typing_area = document.getElementById('typing-area');

			var typing_interval = setInterval(function() {
				if ((typing_area.innerHTML += '.').length == 4) 
					typing_area.innerHTML = '';
				//clearInterval( int ); // at some point, clear the setInterval
			}, 500);
			
			$.ajax({
				type: "POST",
				url: "api.php",
				data: {  
					'action': 'oauth_call',
					'method' : 'GET',
					'matchphrase' : objButton.value,
					'audience_phone' : "447936636769",
				},
				dataType: "json",
				success: function(data){
					clearInterval(typing_interval);
					$('#fakebook-area').height("88%");
					$('#send-message-area').height("0%");
					$("#send-message-area").empty();
					console.log("Success: " + JSON.stringify(data));
					Refresh_messages();
				},
				error:function(data){
				   console.log("ERROR: " + JSON.stringify(data));
				}
			});
		}		
		function JSONObject_to_array(JSONobject){
			var row;
			var key;
			var val;
			for (key in JSONobject){
				if ( typeof JSONobject[key] == "array"  ){
				}
				else if (typeof JSONobject[key] == "object") {
					JSONObject_to_array(JSONobject[key]);
				}
				else{
					var stream =  JSONobject[key].replace(/\t/g, "");
					stream = stream.split("\n");
					for(var i = 0; i < stream.length; i++){
						if (stream[i].length>0) messages_array.push(stream[i]);
					}
				}
			}
		}
		function Array_print(messages,start){
			var i = start;
			interval = setInterval(function() {
			//for ( var i = start; i<messages.length; i++){
				switch (messages[i][0]){
					//video
					case "a":		
						var message = messages[i].slice(2);							
						var regExp = /\[(.*?)\]/;
						var names = regExp.exec(message);
						if (names != null){
							message = message.replace(/\[(.*?)\]/g, "");

							//$("#chat-area").append('<div class="message_wrapper"><img class="profile_photo" style="float:'+lastposition+'"; src="images/javier_profile.png"><div class="message_'+lastposition+'"><span>'+names[1]+'</span><br/>')
							//	$("#chat-area").append('<iframe width="100%" src="//www.youtube.com/embed/yCj_3_M8fAQ"></iframe>');
							//$("#chat-area").append('</div></div>');
							var regExp = /\{(.*?)\}/;
							var audios = regExp.exec(message);
							if (audios != null){
								message = message.replace(/\{(.*?)\}/g, "");
								var audio = audios[1].trim();
								$("#depositbox-area").append('<div class="soundvault_sound"><span>'+names[1]+'</span><br/>'+message+'<br/><br/><audio controls><source src="'+audio+'" type="audio/mpeg"></audio></div>');
							}
						}
						break;
					//video
					case "v":		
						var message = messages[i].slice(2);							
						var regExp = /\[(.*?)\]/;
						var names = regExp.exec(message);
						if (names != null){
							message = message.replace(/\[(.*?)\]/g, "");

							//$("#chat-area").append('<div class="message_wrapper"><img class="profile_photo" style="float:'+lastposition+'"; src="images/javier_profile.png"><div class="message_'+lastposition+'"><span>'+names[1]+'</span><br/>')
							//	$("#chat-area").append('<iframe width="100%" src="//www.youtube.com/embed/yCj_3_M8fAQ"></iframe>');
							//$("#chat-area").append('</div></div>');
							var regExp = /\{(.*?)\}/;
							var videos = regExp.exec(message);
							if (videos != null){
								message = message.replace(/\{(.*?)\}/g, "");
								var video = videos[1].trim();
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
								video = video+"?showinfo=0&controls=0&autohide=1";
								$("#depositbox-area").append('<div class="yootoob_video"><span>Recommended for you</span><br/><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe><br/><span>'+names[1]+'</span></div>');
							}
						}
						break;	
						
					//Comment
					case "c":
						var comment = messages[i].slice(2);							
						var regExp = /\[(.*?)\]/;
						var names = regExp.exec(comment);
						if (names != null){
							comment = comment.replace(/\[(.*?)\]/g, "");
						} 
						
						$("#depositbox-area").append('<div class="yootoob_comment"><img class="profile_photo"; src="images/javier_profile.png"><span>'+names[1]+'</span><br/> '+comment+' </div>');
						break;
					//Reply
					case "r":
						var comment = messages[i].slice(2);							
						var regExp = /\[(.*?)\]/;
						var names = regExp.exec(comment);
						if (names != null){
							comment = comment.replace(/\[(.*?)\]/g, "");
						} 
						$("#depositbox-area").append('<div class="yootoob_comment"><div class="yootoob_reply"><img class="profile_photo"; src="images/javier_profile.png"><span>'+names[1]+'</span><br/> '+comment+' </div></div>');
						break;		
						
					default:
						if ( messages[i].length > 0){
							messages[i] = messages[i].slice(3);
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec( messages[i]);
							if (names != null){
		
								messages[i] = messages[i].replace(/\[(.*?)\]/g, "");
							
								$("#depositbox-area").append('<div class="message_wrapper"><div class="'+lastposition+'"><span>'+names[1]+'</span><br/>'+messages[i]+' </div></div>');
							}
							else 	$("#depositbox-area").append('<div class="message_wrapper"><div class="'+lastposition+'"><span></span><br/>'+messages[i]+' </div></div>');
						}
					}	
					$("#soundvault-area").animate({scrollTop: $('#fakebook-area').prop("scrollHeight")}, 500);
					i++;
					if (i >= messages.length)clearInterval(interval);
				},1000);				
			}		
		</script>
	</head>
	<body>
		<div class="wrapper" id="depositbox-wrapper">
			<div id="header"> 
				<a href="index.php" style="width:20px;height:20px;left:20px;top: 0;bottom: 0;margin: auto;position:absolute;"><img src="images/back"></a>
			</div>
			<div id="depositbox-area"></div>
			<div id="send-message-area"></div>
		</div>
		<script>
			$(document).ready(function(){
				$.ajax({
					type: "POST",
					url: "api.php",
					data: {  
						'action': 'depositbox',
						'audience_phone' : "447936636769",
					},
				
					dataType: "json",
					success: function(data){
						console.log("Success: " + JSON.stringify(data));
						//$("#fakebook-area").append('<div class="message_right"><span>NOMBRE</span><br/>TEXTO </div>');
						//$("#fakebook-area").animate({scrollTop: $('#fakebook-area').prop("scrollHeight")}, 500);
						//lastJSONobject = data.results;
						JSONObject_to_array(data.results);
						last_length = messages_array.length;
						console.log( "Array: " + messages_array); 
						console.log( "Array length: " + messages_array.length); 
						Array_print(messages_array,0);
						//JSON_print_r(data.results);
					},
					error:function(data){
					   console.log("ERROR: " + JSON.stringify(data));
					}
				});
			});
		</script>

			<?php
				//JSON_print_r($results->results);
			?>
	</body>
</html>
