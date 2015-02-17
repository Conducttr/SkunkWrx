var feeds_array = [];
var messages_array = [];
var messages_id_array = [];
var index_array = [];
var question_array = [];
var unfolding_interval;
var index = -1;
var message_feed_id = -1;

var typing_interval;
var question_height;
var	message_count = 0;
var spinner;

var gate = [];
var gate_index = -1;

var selected=false;
var CHARACTER_NAME;
var waitingForAnswer=false;


$(document).ready(function(){
	var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
	
	if( iOS ){
		question_height=8;
		document.addEventListener('touchmove', function(e) { e.preventDefault(); }, false);
		var scroller = document.getElementById('content-area');
		preventOverScroll(scroller);
	}
	else question_height=5.5;
	
	/* Push Notifications */
	setInterval(function(){ 
		if(selected){
			$.ajax({
				type: "GET",
				url: "api.php",
				data: {  
					'action': 'check_push_notifications',
					'message_feed_id': message_feed_id
				},
				dataType: "json",
				success: function(data){

					messages_array = [];
					index_array = [];
					if(data!=null && type =='mail'){
						$(".mail").find('.mail_body').hide();
					}	
					//if (data.length>0){
					if (true){
						messages_to_array(data);
						if(type=="Msngr") Array_print(messages_array,DELAY);	
						else Array_print(messages_array,3000);	
						$.ajax({
							type: "GET",
							url: "api.php",
							data: {  
							'action': 'count_messages',
							'type': type,
							},
							dataType: "json",
							success: function(data){
								if (data>message_count){
									$('#home').prepend("<div class='dot'></div>");
									var notification = $('#notification')[0]; 
									notification.play();    
									navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
									if (navigator.vibrate) {
										navigator.vibrate(1000);
									}
								}
								message_count=data;
							},
							error:function(data){
							   console.log("ERROR: " + JSON.stringify(data));
							}
						});
					}				
				},
				error:function(data){
				   console.log("ERROR: " + JSON.stringify(data));
				}
			});
		}
	}, 30000);
	
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
	spinner = new Spinner(opts).spin(target);	
	
		
	$.ajax({
		type: "GET",
		url: "api.php",
		data: {  
			'action': 'select_message_feeds',
			'type': type,
		},
		dataType: "json",
		success: function(data){
			if (typeof data !== 'undefined' && data.length > 0) {
				feeds_array = data;
				message_feeds_to_array(data);
			}
			else {
				var msg  = ' No messages';
				switch (type){
					case "Mail": 
						msg="There aren't any emails ";
						break;
					case "Msngr":
						msg="There aren't any conversations  ";
						break;
					default:
						msg= "There isn't any content ";
				}		
				$("#content-area").append('<div style="font-weight:bold;text-align:center">'+msg+'</div>');
				spinner.stop();
			}	
		},
		error:function(data){
			console.log("ERROR: " + JSON.stringify(data));
			spinner.stop();
  
		}
	});
});	
function SendAnswer(matchphrase,index){
	$("#send-message-area").empty();
	$("#send-message-area").append('<div id="typing-area"><span id="typing"></span><span id="dot"></span></div>');
	var loading_text= "Loading ";
	switch (type){
		case "Mail": 
			loading_text="Sending ";
			break;
		case "Msngr":
			loading_text="Typing ";
			break;
		default:
			loading_text= "Loading ";
	}
	setTimeout(function(){document.getElementById('typing').innerHTML=loading_text;},500);
	var dot_area = document.getElementById('dot');

	typing_interval = setInterval(function() {
		if ((dot_area.innerHTML += '.').length == 4) 
			dot_area.innerHTML = '';
	}, 500);
	$.ajax({
		type: "GET",
		url: "api.php",
		data: {  
			'action': 'send_answer',
			'matchphrase' : matchphrase,
			'character' : CHARACTER_NAME,
			'type' : type,
			'index' : index
			},
		dataType: "json",
		success: function(data){
			$.ajax({
				type: "GET",
				url: "api.php",
				data: {  
					'action': 'refresh_message_feeds',
					'message_feed_id': message_feed_id
				},
				dataType: "json",
				success: function(data){
					clearInterval(typing_interval);
					$('#content-area').height("83%");
					$('#send-message-area').empty();
					$('#send-message-area').height("1%");
					messages_array = [];
					index_array = [];
					if(data!=null && type =='mail'){
						$(".mail").find('.mail_body').hide();
					}	
					messages_to_array(data);
					if(type=="Msngr") Array_print(messages_array,DELAY);	
					else Array_print(messages_array,3000);	
					
					$.ajax({
						type: "GET",
						url: "api.php",
						data: {  
							'action': 'count_messages',
							'type': type,
						},
						dataType: "json",
						success: function(data){
							if (data>message_count){
								$('#home').prepend("<div class='dot'></div>");
								var notification = $('#notification')[0]; 
								notification.play();    
							}
							message_count=data;
						},
						error:function(data){
						   console.log("ERROR: " + JSON.stringify(data));
						}
					});
				},
				error:function(data){
				   console.log("ERROR: " + JSON.stringify(data));
				}
			});
		},
		error:function(data){
			console.log("ERROR: " + JSON.stringify(data));
		}
	});
}
function message_feeds_to_array(JSONobject){
	var message_count = 0;
	var question_count = 0;
	messages_array = [];
	index_array = [];
	if(JSONobject.length==1){
		 index_array[0] = JSONobject[0].message_feed_id;
		 select(JSONobject[0].message_feed_id,0);
		 spinner.stop();
	}
	else{
		for (var i=0; i<JSONobject.length;i++){
			messages_array[i] = [];
			index_array[i] = JSONobject[i].message_feed_id;
			
			/* Find tagline */
			var regExp = /\[(.*?)\]/;			
			var names = regExp.exec(JSONobject[i].character_name);
			if (names != null){
				var character_name = names[1];
			}	
			else var character_name = JSONobject[i].character_name;

			var last_body =  JSONobject[i].body.split("\n");
			var myIndex = -1;
			var found = false;
			while (!found){
				myIndex++;
				if (myIndex<last_body.length){
					if (last_body[myIndex]!=""){	
						if(last_body[myIndex].trim()[0] != "" && last_body[myIndex].trim()[0] != "d" && last_body[myIndex].trim()[0] != "o" && last_body[myIndex].trim()[0] != "q" && last_body[myIndex].trim()[0] != "x" ){
							found=true;
						}
					}
				}
				else found=true;
			}		
			if(myIndex<last_body.length)  var tagline = last_body[myIndex].trim().slice(2).replace(/\[(.*?)\]/g, "");
			
			else var tagline = "" ;
			
			if (type!='blog')
				$("#content-area").append('<div class="feed" onClick="select('+JSONobject[i].message_feed_id+','+i+');"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+character_name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="feed_name" >'+character_name+'</div><div class="feed_tagline">'+ tagline+'</div><div class="feed_notifications">' + JSONobject[i].message_count +' Messages <br>' + JSONobject[i].new_message_count +' New messages <br> '+JSONobject[i].question_count+' Response pending</div></div></div>');
			else 
				$("#content-area").append('<div class="blog_wrapper" onClick="select('+i+');"><header><span>'+character_name+'</span> </header><div class="content" style="background:url(styles/'+PROJECT_ID+'/episodes/episode_'+(i+1)+'.jpg);background-size:cover;">'+ tagline+'</div></div>');
		}
		spinner.stop();
	}
}
function messages_to_array(JSONobject){
	var message_count;
	var question_count;
	messages_array = [];
	index_array = [];
	switch (type.toLowerCase()){
		case "blog":
		case "mail":
		case "media":
			for (var i=0; i<JSONobject.length;i++){
				if($.inArray(JSONobject[i].id, messages_id_array)==-1){
					messages_id_array.push(JSONobject[i].id);
					var unlocked_items =  JSONobject[i].body;
					var stream =  unlocked_items.replace(/\t/g, "");
					
					email_questions=[];
					if(JSONobject[i].question == 1){
						stream_splitted = stream.split("\n");
						body_without_questions= [];
						for(var k = 0; k < stream_splitted.length; k++){
							if (stream_splitted[k][0]=="q" && stream_splitted[k][1]=="."){
								email_questions.push(stream_splitted[k]);
							}
							else{
								body_without_questions.push(stream_splitted[k]);
							}
						}
						var composed_questions = email_questions.join('\n');
						stream = body_without_questions.join('\n');
					}
					
					stream = stream.split("[");
					for(var k = 1; k < stream.length; k++){
						if (stream[k].length>0) {							
							messages_array.push("["+stream[k]);
							index_array.push(JSONobject[i].id);
							question_array.push(JSONobject[i].question && JSONobject[i].already_read);
						}
					}
					/* Questions */
					for(var k = 0; k < email_questions.length; k++){
						if (email_questions[k].length>0) {							
							messages_array.push(email_questions[k]);
							index_array.push(JSONobject[i].id);
							question_array.push(JSONobject[i].question && JSONobject[i].already_read);
						}
					}
					
				}
				else console.log("Repeated message: "+JSONobject[i].id);
			}
			break;
		default:
			for (var i=0; i<JSONobject.length;i++){
				if($.inArray(JSONobject[i].id, messages_id_array)==-1){
					messages_id_array.push(JSONobject[i].id);
					var unlocked_items =  JSONobject[i].body;
					var stream =  unlocked_items.replace(/\t/g, "");
					stream = stream.split("\n");
					for(var k = 0; k < stream.length; k++){
						if(stream[k].length>0 ){
							messages_array.push(stream[k]);
							index_array.push(JSONobject[i].id);
							question_array.push(JSONobject[i].question && JSONobject[i].already_read);
						}
					}
				}
				else console.log("Repeated message: "+JSONobject[i].id);
			}
	}
}
	
function Array_print(messages, delay){
	var count_index = 0;
	unfolding_interval = setInterval(function() {
		if (messages.length<=0)return "Done";
		count_index++;
		index=index_array.shift();
		message=messages.shift().trim();
		question=question_array.shift().trim();

		//Questions - Fixed answers
		if (message[0]== "q" && question!=true){	
			var questions = message.slice(2);
			questions = questions.split("||");
			var number_of_questions = question_height*questions.length;
			$('#content-area').height(84-number_of_questions+"%");
			$('#send-message-area').height(number_of_questions+"%");
			$("#send-message-area").append('<div class="answer-divider"></div>');
			$('#send-message-area').hide();
			for(var j= 0; j < questions.length; j++){
				var regExp = /\[(.*?)\]/;
				var names = regExp.exec(questions[j]);
				if (names != null){
					questions[j] = questions[j].replace(/\[(.*?)\]/g, "");
					$("#send-message-area").append('<button class="answer" name="'+questions[j].trim()+'" value="'+names[1]+'" onClick="SendAnswer(\''+names[1].trim()+'\',\''+index+'\');" index="'+index+'">'+questions[j].trim()+'</button>');
					$("#send-message-area").append('<div class="answer-divider"></div>');
				}
			}
		}
		//Questions - Open Input
		else if (message[0]== "o" && question!=true){
			var openText = message.slice(2);	
		
			var regExp = /\[(.*?)\]/;
			var names = regExp.exec(openText);
			var prompt = "";
			if (names != null){
				prompt = names[1];
			}
			
			$("#send-message-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center;height:88%;padding-bottom:0px;padding-top: 4px;" ><input id="openTextInput" type="text" style="width:85%;height:70%;float:left;padding: 2px;" placeholder="." value="'+names[1]+' " autofocus  onfocus="this.value = this.value;"><button id="openTextButton" index="'+index+'" style="width:10%;height:75%;float:right;"></button></div>');
			$( "#openTextButton" ).click(function() {
				var matchphrase = $( "#openTextInput" ).val();
				matchphrase=matchphrase.replace(names[1],"");
				matchphrase="opentext:"+names[1]+" "+matchphrase;
				var index_from_the_input = $("#openTextButton").attr("index");
				SendAnswer(matchphrase,index_from_the_input);
			});
			input_height=7;
			$('#content-area').height(84-input_height+"%");
			$('#send-message-area').height(input_height+"%");
		}
		
		/* Unlocking pattern */
		else if (message[0]== "x" && question!=true){
			var pattern = message.slice(2);						
			var options = pattern.split(",");
			console.log(options);
			
			var patternValue = options[0];
			var seconds_to_fade = options[1];
			var seconds_to_solve = options[2];
			var margin = 15;
			var radius = 10;
			var matrix_rows = 3;
			var matrix_columns = 3;

			var pattern_height_absolute = ((radius*2)+(margin*2))*matrix_rows;
			var wrapper_height = $('#msngr-wrapper').height() - $('#msngr-header').height() - $('#buttons').height();
			var pattern_height =  35;
			
			$("#send-message-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center;background: #3382c0;height:100%;padding-top:0;padding-bottom:0;" ><div id="patternContainer" style="margin:auto" value="123" index="'+index+'" ></div></div>');
			$("#send-message-area").append('<div class="message_wrapper></div>');

			$('#content-area').height(86-pattern_height+"%");
			$('#send-message-area').height(pattern_height+"%");
			var lock;
			
			lock = new PatternLock('#patternContainer',{
				enableSetPattern : true,
				margin:margin,
				radius:radius,
				matrix: [matrix_rows,matrix_columns]
			});
			
			
			lock.setPattern(patternValue);
			waitingForAnswer=true;
			
			setTimeout(function(){ lock.reset(); }, seconds_to_fade);
			
			setTimeout(function(){ 
				console.log("waitingForAnswer: "+waitingForAnswer);

				if(waitingForAnswer==true){
					console.log("waitingForAnswer: "+waitingForAnswer);
					$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center">Pattern failed</div>');
					SendAnswer("patternIncorrectbyTime",index);
				}
				else {
					console.log("waitingForAnswer: "+waitingForAnswer);
					console.log("time is up");
				}

			}, seconds_to_solve);
			
			lock.checkForPattern(parseInt(patternValue),function(){
				console.log("The drawn pattern "+lock.getPattern());
				console.log("The one that was set "+patternValue);

				waitingForAnswer=false;
				$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center">Pattern correct</div>');

				SendAnswer("patternCorrect",index);
			},function(){
				console.log("The drawn pattern "+lock.getPattern());
				console.log("The one that was set "+patternValue);
				waitingForAnswer=false;
				$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center">Pattern incorrect</div>');

				SendAnswer("patternIncorrect",index);
			});  
		} 
		else{
			switch (type){
				//GoSocial - Fakebook //		
				case 'GoSocial':
					switch (message[0]){
						//Post
						case "t":
						case "p":
							var post = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(post);
							if (names != null){
								post = post.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name ="";

							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var images = regExp.exec(post);
							if (images != null){
								post = post.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, "");
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><br/>'+post+'</div>');
							}
							break;
						
						//Post with Image
						case "i":
							var post = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(post);
							if (names != null){
								post = post.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name ="";

							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var images = regExp.exec(post);
							if (images != null){
								post = post.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, "");
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><br/>'+post+'</div>');
							}
							break;
						//Audio	
						case "a":
							audio = message.slice(2);							
							var html_message = '';
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(audio);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								audio = audio.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									html_message='<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>';
								}
								else html_message='<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><br/>';
							}
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								audio = audio.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=audio+'<br><br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div></div>';										
							}
							else html_message+=audio+'</div></div>';
							$("#content-area").append(html_message);
							break;	
						//Video
						case "v":
							
							video = message.slice(2);							
							var html_message = '';
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(video);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								video = video.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									html_message='<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>';
								}
								else html_message='<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><br/>';
							}
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							var videos = regExp_video.exec(video);
							if (videos != null){
								video = video.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								video = video.trim();
								html_video = "https://www.youtube.com/embed/"+videos[1];
								html_video = html_video+"?showinfo=0&controls=0&autohide=1";
								html_message+=video+'<br><br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+html_video+'"  frameborder="0" ></iframe></div></div></div>';										
							}
							else html_message+=video+'</div></div>';
							$("#content-area").append(html_message);
							break;	
							
						//Comment
						case "c":
							var comment = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(comment);
							/*
							if (names != null){
								comment = comment.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							*/
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1).trim();
								if (name ==  "You") {
									var IMAGE_SRC='styles/'+PROJECT_ID+'/'+PROFILE_IMAGE;
									if (AUDIENCE_FIRST_NAME =='')
										name = 'You';
									else
										name = AUDIENCE_FIRST_NAME;
								}
								else var IMAGE_SRC = 'styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png';
							}
							else var name ="";
							comment = comment.replace(/\[(.*?)\]/g, name);

							if (messages.length>1){
								if(messages[1][0]=="p" || messages[1][0]=="i" || messages[1][0]=="v"){
									$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><img class="profile_photo" src="'+IMAGE_SRC+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span> '+comment+' </div>');
								}
								else{
									$("#content-area").append('<div class="gosocial_comment"><img class="profile_photo" src="'+IMAGE_SRC+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span> '+comment+' </div>');
								}
							}
							else{
								$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><img class="profile_photo" src="'+IMAGE_SRC+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';" ><span>'+name+'</span> '+comment+' </div>');
							}
							break;
						//Reply
						case "r":
							var comment = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(comment);
							if (names != null){
								comment = comment.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name = "";
							if (messages.length>1){
							
								if (messages[1][0]=="p" || messages[1][0]=="i" || messages[1][0]=="v"){
									$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span> '+comment+' </div></div>');
								}
								else {
									$("#content-area").append('<div class="gosocial_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span> '+comment+' </div></div>');
								}
							}
							else {
								$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span> '+comment+' </div></div>');
							}
							break;
						}
					break;
					// MicroBlog - Tuiter //
				case 'Microblog':
					switch (message[0]){
						//Tweet only text
						case "p":
						case "t":
						case "c":
							var tuit = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(tuit);
							if (names != null){
								tuit = tuit.replace(/\[(.*?)\]/g, "");
							} 	
							$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span><div class="tuit_content">'+tuit+'</div></div>');
							break;
						//Tweet with image	
						case "i":
							var tuit = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(tuit);
							if (names != null){
								tuit = tuit.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name =""; 
							
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var images = regExp.exec(tuit);
							
							if (images != null){
								tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><span class="screenname"> @'+name+'</span> <div class="tuit_content">'+tuit+'<br/ ><img src="'+images[1]+'"></div></div>');
							}
							else{ 
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><span class="screenname"> @'+name+'</span><div class="tuit_content">'+tuit+'</div></div>');
							}
							break;
						//Audio	
						case "a":
							audio = message.slice(2);							
							var html_message = '';
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(audio);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								audio = audio.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									html_message='<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><span>'+AUDIENCE_FIRST_NAME+'</span><span class="screenname"> @'+name+'</span><br/><div class="tuit_content">';
								}
								else html_message='<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+name+'</span><span class="screenname"> @'+name+'</span><br/><div class="tuit_content">';
							}
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								audio = audio.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=audio+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div></div>';										
							}
							else html_message+=audio+'</div></div>';
							$("#content-area").prepend(html_message);
							break;	
						case "v":
							var tuit = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(tuit);
							if (names != null){
								tuit = tuit.replace(/\[(.*?)\]/g, "");
							} 
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;

							var videos = regExp_video.exec(tuit);
							if (videos != null){
								tuit = tuit.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								var video = "https://www.youtube.com/embed/"+videos[1].trim();
								video = video+"?showinfo=0&controls=0&autohide=1";							
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span> <div class="tuit_content">'+tuit+'<br/><div class="video_container"><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></div></div></div>');
							}
							else{ 
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span><div class="tuit_content">'+tuit+'</div></div>');
							}
							break;
						
						}
					break;

				//Msngr //
				case "Msngr":
					switch (message[0]){
						//Conversation
						case "p":
						case "t":
						case "c":
							var message = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(message);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								message = message.replace(/\[(.*?)\]/g, "");
							}
							else var name = "";	
							var message_content = '<div class="message_wrapper" ><img class="profile_photo"  src="';
							
							if (name ==  "You") {
								var POSITION = 'right';
								var IMAGE = 'styles/'+PROJECT_ID+'/'+PROFILE_IMAGE;
								if (AUDIENCE_FIRST_NAME =='')
									var	NAME = 'You';
								else
									var	NAME = AUDIENCE_FIRST_NAME;
							}
							else if (name ==  "") {
								var POSITION = 'left';
								var IMAGE = '';
								var	NAME = name;
							}
							else{
								var POSITION = 'left';
								var IMAGE = 'styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png';
								var	NAME = name;
							}
							
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							message = message.trim();
							var videos = regExp_video.exec(message);
							if (videos != null){
								message = message.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								var video = "https://www.youtube.com/embed/"+videos[1];
								video = video+"?showinfo=0&controls=0&autohide=1";
								message+='<br><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe>';
							}
							else{
								var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
								var urls = regExp.exec(message);
								if (urls != null){

									var extension = urls[1].substr(urls[1].length-3, 3);
									message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
									switch (extension){
										case "jpeg":
										case "png":
										case "gif":
											message+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+urls[1]+'" style="vertical-align:middle"><span> '+name+'  </span></div><br>';
											break
										case "mp3":
										case "wav":
											message+='<br><div class="sound"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'" type="audio/mpeg"></audio></div>';
											break;
										default:
											message+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><a href="'+urls[1]+'" target="_blank">'+urls[1]+'</a></div><br>';	
									}
								}
							}	 
							$("#content-area").append('<div class="message_wrapper" ><div class="message_content  '+POSITION+'" ><img class="profile_photo" src="'+IMAGE+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="message_'+POSITION+'"><span>'+NAME+'</span><br/>'+message+'</div></div></div>');

						break;		
						//Image	
						case "i":
							image = message.slice(2);							
							var html_message = '';
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(image);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								image = image.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									if (AUDIENCE_FIRST_NAME =='')
										var	NAME = 'You';
									else
										var	NAME = AUDIENCE_FIRST_NAME;
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:right;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/profiles/you.png\') this.src = \'styles/'+PROJECT_ID+'/profiles/you.png\';"><div class="message_right image"><span>'+NAME+'</span><br/>';
								}
								else{
									
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="message_left image"><span>'+name+'</span><br/>';
								}
							}
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								image = image.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=image+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><a href="'+urls[1]+'" data-lightbox="'+urls[1]+'" data-title="'+image+'"><image src="'+urls[1]+'" style="vertical-align:middle"></a></div><br>';										
							}
							else html_message+=image+'</div></div>';
							$("#content-area").append(html_message);
							break;
						//Audio	
						case "a":
							audio = message.slice(2);							
							var html_message = '';
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(audio);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								audio = audio.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									if (AUDIENCE_FIRST_NAME =='')
										var	NAME = 'You';
									else
										var	NAME = AUDIENCE_FIRST_NAME;
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:right;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/profiles/you.png\') this.src = \'styles/'+PROJECT_ID+'/profiles/you.png\';"><div class="message_right audio"><span>'+NAME+'</span><br/>';
								}
								else html_message='<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="message_left audio"><span>'+name+'</span><br/>';
							}
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								audio = audio.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=audio+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div><br>';										
							}
							else html_message+=audio+'</div></div>';
							$("#content-area").append(html_message);
							break;	
						//Video
						case "v":
							
							video = message.slice(2);							
							var html_message = '';
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(video);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								video = video.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									if (AUDIENCE_FIRST_NAME =='')
										var	NAME = 'You';
									else
										var	NAME = AUDIENCE_FIRST_NAME;
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:right;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'" onerror="if (this.src != \'styles/'+PROJECT_ID+'/profiles/you.png\') this.src = \'styles/'+PROJECT_ID+'/profiles/you.png\';"><div class="message_right video"><span>'+NAME+'</span><br/>';
								}
								else html_message='<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="message_left video"><span>'+name+'</span><br/>';
							}
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							var videos = regExp_video.exec(video);
							if (videos != null){
								video = video.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								video = video.trim();
								var html_video = "https://www.youtube.com/embed/"+videos[1];
								html_video = html_video+"?showinfo=0&controls=0&autohide=1";
								html_message+=video+'<br><div class="video_container"><iframe style="padding-top:4px;" width="100%" src="'+html_video+'"  frameborder="0" ></iframe></div></div></div>';										
							}
							else html_message+=video+'</div></div>';
							$("#content-area").append(html_message);
							break;
						//Date	
						case "d":
						
							var date = message.slice(2);
							
							var m_names = new Array("January", "February", "March", 
							"April", "May", "June", "July", "August", "September", 
							"October", "November", "December");
							var d = new Date();
							var curr_date = d.getDate();
							var curr_month = d.getMonth();
							var curr_year = d.getFullYear();
							today = curr_date + " " + m_names[curr_month] + " " + curr_year;
							date = date.replace("|today|", today);							

							$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center">'+date+'</div>');
							break;	
	
						}
					break;			
				
				case "Blog":
					var email = message;							
					var regExp = /\[(.*?)\]/;
					var names = regExp.exec(email);
					if (names != null){
						email = email.replace(/\[(.*?)\]/g, "");
						if ( names[1] == "You" || names[1] == "you"  ){
							 var name = AUDIENCE_FIRST_NAME;
								var profile_image_path = PROFILE_IMAGE;
						}
							else {
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								var profile_image_path = 'images/'+name.toLowerCase().replace(/ /g,'')+'.png';
							}
						} 
						else var name = "";
						
						body = email.replace(/\<br\s*[\/]?\>/gi, "<br\>\n");
						body = email.replace(/\<\/span\>/gi, "\n");

						body = body.replace(/\n+/gi, "\n");
						body = body.split("\n");
					
						if (count_index==1){

								var html_tags = '<div class="blog" ><a class="minimize_icon" onClick="openBody(this.parentNode)" style="width: 20px;height: 20px;float: right;cursor:pointer;"><img src="images/minimize.png"></a>';
								var display = 'style="display:block;"';
						}
						else{
							var html_tags = '<div class="blog bold" ><a class="minimize_icon" onClick="openBody(this.parentNode)" style="width: 20px;height: 20px;float: right;cursor:pointer;"><img src="images/maximize.png"></a>';
							var display = '';
						}
							
						html_tags+='<span>'+name+'</span><br/><div style="padding-left:45px">'+body[0]+'</div><div class="blog_body" '+display+'>';
						
						for ( var j = 1; j<body.length; j++){
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							var videos = regExp_video.exec(body[j].trim());
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(body[j].trim());

							if (videos != null){
								body[j] = body[j].replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								body[j] = body[j].replace("v.", '');							

								video = videos[0].trim();
								video = video.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/,"");
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
								video = video+"?showinfo=0&controls=0&autohide=1";
								html_tags+=body[j]+'<br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+video+'"  frameborder="0" ></iframe></div><br>';
							}
							else if (videos == null && urls != null){
								body[j] = body[j].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");
								if (body[j][0]=='i' && body[j][1]=='.'){
									body[j] = body[j].slice(2);
									html_tags+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+urls[1]+'" style="vertical-align:middle"></div><br>';
								}
								else {
									var extension = urls[1].substr(urls[1].length-3, 3);
									body[j] = body[j].replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
									body[j] = body[j].replace("i.", '');							
									body[j] = body[j].replace("a.", '');
									switch (extension){
										case "jpeg":
										case "jpg":
										case "png":
										case "gif":
											html_tags+=body[j]+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+urls[1].trim()+'" style="vertical-align:middle"></div><br>';
											break
										case "mp3":
										case "wav":
											html_tags+=body[j]+'<br><div class="sound"><audio controls style="max-width: 100%;"><source src="'+urls[1].trim()+'" type="audio/mpeg"></audio></div>';
											break;
										default:
											html_tags+=body[j]+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="images/nohex_file.png" style="vertical-align:middle"><a href="'+urls[1].trim()+'" target="_blank"> FILE </a></div><br>';	
									}
								}	
							}
							else html_tags+=body[j]+'<br>';	
						}
						html_tags+='</div></div>';
						
						$("#content-area").append(html_tags);
					break;				
				case "Mail":
					var email = message;							
					var regExp = /\[(.*?)\]/;
					var names = regExp.exec(email);
					if (names != null){
						email = email.replace(/\[(.*?)\]/g, "");
						if ( names[1] == "You" || names[1] == "you"  ){
							 var name = AUDIENCE_FIRST_NAME;
								var profile_image_path = PROFILE_IMAGE;
						}
						else {
							var name =  names[1].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");

							name = name.charAt(0).toUpperCase() + name.slice(1);
							var profile_image_path = '/styles/'+PROJECT_ID+'/images/'+name.toLowerCase().trim()+'.png';
						}
					} 
					else var name = "";
						
					body = email.replace(/\<br\s*[\/]?\>/gi, "<br\>\n");
					body = email.replace(/\<\/span\>/gi, "\n");

					body = body.replace(/\n+/gi, "\n");
					body = body.split("\n");

					if (messages.length>0){
						$open_body = true;
						for ($k=0;$k<messages.length;$k++){
							if (messages[$k][0]!='q') $open_body=false;
						}
						if($open_body){
							var html_tags = '<div class="mail" onClick="openBody(this)"><a class="minimize_icon" onClick="openBody(this.parentNode)" style="width: 20px;height: 20px;float: right;cursor:pointer;"><img src="images/minimize.png"></a>';
							var display = 'style="display:block;"';
						}
					
						else {
							var html_tags = '<div class="mail bold" "><a class="minimize_icon" onClick="openBody(this.parentNode)" style="width: 20px;height: 20px;float: right;cursor:pointer;"><img src="images/maximize.png"></a>';
							var display = '';
						}	
					}
					else{
						var html_tags = '<div class="mail"><a class="minimize_icon" onClick="openBody(this.parentNode)" style="width: 20px;height: 20px;float: right;cursor:pointer;"><img src="images/minimize.png"></a>';
						var display = 'style="display:block;"';
					}
					var img = new Image();	
					img.src = profile_image_path;
					if (img.height != 0) html_tags+='<img class="profile_photo" src="'+profile_image_path+'"><span>'+name+'</span><br><span class="screenname"> \<'+name.toLowerCase()+'@mail.com\></span> <br/><div style="padding-left:45px">'+body[0]+'</div><div class="mail_body" '+display+'>';
					else{
						for (var i = 0, hash = 0; i < name.length; hash = name.charCodeAt(i++) + ((hash << 5) - hash));
						for (var i = 0, colour = "#"; i < 3; colour += ("00" + ((hash >> i++ * 8) & 0xFF).toString(16)).slice(-2));
						html_tags+='<div class="profile_photo" style="font-family:Roboto;position:relative;background-color:'+colour+'";><div style="color:white;position:absolute;left:0;right:0;top:0;bottom:0;margin:auto;width: 80%;height: 80%;font-size: 25px;text-align: center;font-weight:normal;">'+name[0]+'</div></div><span>'+name+'</span><br/><div style="padding-left:45px">'+body[0]+'</div><div class="mail_body" '+display+'>';
					}
						
					for ( var j = 1; j<body.length; j++){
						var stripped_message = body[j].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");
						if (stripped_message[0]=='v'){
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							var videos = regExp_video.exec(body[j].trim());
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(body[j].trim());
							if (videos != null){
								body[j] = body[j].replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								body[j] = body[j].replace("v.", '');							
								video="https://www.youtube.com/embed/"+videos[1];
								video = video+"?showinfo=0&controls=0&autohide=1";
								html_tags+=body[j]+'<br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+video+'"  frameborder="0" ></iframe></div><br>';
							}
							
						}
						else if(stripped_message[0]=='a' || stripped_message[0]=='i' || stripped_message[0]=='f'  ){
							var urls = regExp.exec(body[j].trim());
							body[j] = body[j].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");
							if (body[j][0]=='i' && body[j][1]=='.' ){
								body[j] = body[j].slice(2);
								html_tags+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+body[j].trim()+'" style="vertical-align:middle"></div><br>';
							}
							else if (body[j][0]=='a' && body[j][1]=='.'){
								body[j] = body[j].slice(2);
								html_tags+='<br><div class="sound"><audio controls style="max-width: 100%;"><source src="'+body[j].trim()+'" type="audio/mpeg"></audio></div>';
							}
							else {
								if (urls!=null){
									var extension = urls[1].substr(urls[1].length-3, 3);
									body[j] = body[j].replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
									body[j] = body[j].replace("i.", '');									
									switch (extension){
										case "jpeg":
										case "jpg":
										case "png":
										case "gif":
											html_tags+=body[j]+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+urls[1].trim()+'" style="vertical-align:middle"></div><br>';
											break
										case "mp3":
										case "wav":
											html_tags+=body[j]+'<br><div class="sound"><audio controls style="max-width: 100%;"><source src="'+urls[1].trim()+'" type="audio/mpeg"></audio></div>';
											break;
										default:
											html_tags+=body[j]+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="images/nohex_file.png" style="vertical-align:middle"><a href="'+urls[1].trim()+'" target="_blank"> FILE </a></div><br>';	
									}
								}
							}	
						}
						else html_tags+=body[j]+'<br>';	
					}
					html_tags+='</div></div>';
					$("#content-area").prepend(html_tags);
					break;
				case "media":
					var file_url = message;
					var regExp = /\[(.*?)\]/;
					var names = regExp.exec(file_url);
					if (names != null){
						var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
						file_url = file_url.replace(/\[(.*?)\]/g, "");
					}	
					else var name = "";	

					var regExp_video = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
					file_url = file_url.trim();
					var videos = regExp_video.exec(file_url);
					if (videos != null){
						file_url = file_url.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
						video = videos[0].trim();
						video = video.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/,"");
						video = video.slice(6);
						video = video.replace("watch?v=", "embed/");
						video = video+"?showinfo=0&controls=0&autohide=1";
						$("#content-area").prepend('<div style="position:relative">'+name+'<iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe></div>');
					}
					else{
						var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
						var urls = regExp.exec(file_url);
						var extension = urls[1].substr(urls[1].length-3, 3);
						switch (extension){
							case "jpeg":
							case "png":
							case "gif":
								$("#content-area").prepend('<div class="message_wrapper"><image src="'+file_url+'" style="vertical-align:middle"><span> '+name+'  </span><a href="'+file_url+'" target="_blank"> FILE </a></div>');	
							break
							case "mp3":
							case "wav":
								$("#content-area").prepend('<div class="message_wrapper"><span>'+names[1]+'</span><br/>'+message+'<br/><br/><audio controls><source src="'+file_url+'" type="audio/mpeg"></audio></div>');
							default:
								$("#content-area").prepend('<div class="message_wrapper"><image src="styles/'+PROJECT_ID+'/images/nohex_file.png" style="vertical-align:middle"><span> '+name+'  </span><a href="'+file_url+'" target="_blank"> FILE </a></div>');	

						}
					}

					break;
				}	
			}
		if(delay>0 && type!="Mail" && type!="Blog" && type!='Microblog' ){
			$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);
			$('#send-message-area').show();
		}
		if (messages.length==0){
			clearInterval(unfolding_interval);
			if(delay==0&&type!="Mail"&& type!="Blog" && type!="Microblog"){
				$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);
			}
			$('#send-message-area').show();
		}
	},delay);		
}
function select(id,index){
	message_feed_id = id;
	selected = true;
	$('#content-area').empty();
	$('#send-message-area').empty();
	$.ajax({
		type: "GET",
		url: "api.php",
		data: {  
			'action': 'select_messages',
			'message_feed_id': message_feed_id
		},
		dataType: "json",
		success: function(data){
			messages_to_array(data);
			$("#content-area").append('<div id="selected_feed" ><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+feeds_array[index].character_name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="feed_name" >'+feeds_array[index].character_name+'</div><div class="feed_tagline">Available</div></div></div>');
			$("#content-area").append('<br>');
			CHARACTER_NAME=feeds_array[index].character_name.toLowerCase();
			Array_print(messages_array,0);			
			$.ajax({
				type: "GET",
				url: "api.php",
				data: {  
					'action': 'new_count_messages',
					'message_feed_id' : message_feed_id,
					'type': type,
				},
				dataType: "json",
				success: function(data){
					message_count=data;
				},
				error:function(data){
				   console.log("ERROR: " + JSON.stringify(data));
				}
			});
		},
		error: function(data){
			console.log("ERROR: "+JSON.stringify(data));
		}

	});		
}
function back(){
	clearInterval(unfolding_interval);
	messages_id_array = [];
	selected = false;
	waitingForAnswer = false;
	
	if (index==-1 || feeds_array.length==1){
		window.location.href = 'desktop.php';
	}
	else{
		$("#content-area").empty();
		$("#send-message-area").empty();
		$("#content-area").height('84%');
		$("#send-message-area").height('1%');

		$.ajax({
			type: "GET",
			url: "api.php",
			data: {  
				'action': 'select_message_feeds',
				'type': type
			},
			dataType: "json",
			success: function(data){
				feeds_array = data;
				message_feeds_to_array(data);
			},
			error:function(data){
			   console.log("ERROR: " + JSON.stringify(data));
			}
		});
		index=-1;
	}
}
function openBody(element){
	if(type=="Mail"){
		if( $(element).find('.mail_body').is(':visible') ) {
			$("#content-area").animate({scrollTop: 0}, 300);
			$(element).find('.mail_body').hide("slow");
			$(element).find('.minimize_icon').find('img').attr("src", "images/maximize.png");
		}
		else {
			$(".mail").find('.mail_body').hide();
			$(".mail").find('.minimize_icon').find('img').attr("src", "images/maximize.png");

			$(element).find('.minimize_icon').find('img').attr("src", "images/minimize.png");

			$(element).find('.mail_body').show(200, function() {
				$("#content-area").animate({scrollTop: $(mail).offset().top-57}, 200);
			});
			var audio = $("#sound3")[0];
			audio.play();
		}
		$(mail).removeClass('bold');	
	}
	else if (type=="Blog"){
		if( $(element).find('.blog_body').is(':visible') ) {
			$("#content-area").animate({scrollTop: 0}, 300);
			$(element).find('.blog_body').hide("slow");
			$(element).find('.minimize_icon').find('img').attr("src", "images/maximize.png");
		}
		else {
			$(".blog").find('.blog_body').hide();
			$(".blog").find('.minimize_icon').find('img').attr("src", "images/maximize.png");
			$(element).find('.minimize_icon').find('img').attr("src", "images/minimize.png");

			$(element).find('.blog_body').show(200, function() {
				$("#content-area").animate({scrollTop: $(mail).offset().top-57}, 200);
			});
			var audio = $("#sound3")[0];
			audio.play();
		}
		$(mail).removeClass('bold');
	
	}			
}
