var inventory_attributes = [];
var messages_array = [];
var index_array = [];
var unfolding_interval;
var index = -1;
var attribute_index = -1;
var typing_interval;
var question_height;
var	message_count = 0;
var spinner;
$(document).ready(function(){
	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
		question_height=8;
	}
	else question_height=6;
	
	//$('body').on('touchmove', function (e) {
	//	if (!$('#content-area').has($(e.target)).length) //check if the div isn't being scrolled
	//	e.preventDefault();
	//});
	
	//$.nonbounce();
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
			'action': 'select_inventory_attributes',
			'type': type,
		},
		dataType: "json",
		success: function(data){
			console.log(data);
			if (typeof data !== 'undefined' && data.length > 0) {
				inventory_attributes = data;
				inventory_attribute_to_array(data);
				$.ajax({
					type: "GET",
					url: "api.php",
					data: {  
						'action': 'count_messages',
						'type': type,
					},
					dataType: "json",
					success: function(data){
						console.log("Message Count "+data);
						message_count=data;
					},
					error:function(data){
					   console.log("ERROR: " + JSON.stringify(data));
					}
				});
			}
			else {
				var msg  = ' No messages';
				switch (type){
					case "mail": 
						msg="There aren't any emails ";
						break;
					case "msngr":
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

function SendAnswer(objButton){
	$("#send-message-area").empty();
	$("#send-message-area").append('<div id="typing-area"><span id="typing"></span><span id="dot"></span></div>');
	var loading_text= "Loading ";
	console.log('Type '+type);
	switch (type){
		case "mail": 
			loading_text="Sending ";
			break;
		case "msngr":
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
			'matchphrase' : objButton.value,
			'index' : objButton.getAttribute("index")
			},
		dataType: "json",
		success: function(data){
			console.log(JSON.stringify(data));
			console.log(attribute_index);

			$.ajax({
				type: "GET",
				url: "api.php",
				data: {  
					'action': 'refresh_inventory_items',
					'inventory_id': attribute_index
				},
				dataType: "json",
				success: function(data){
					clearInterval(typing_interval);
					$('#content-area').height("83%");
					$('#no-bounce-wrapper').height("83%");

					
					$('#send-message-area').empty();
					$('#send-message-area').height("1%");
					messages_array = [];
					index_array = [];
					console.log(JSON.stringify(data));
					if(data!=null && type =='mail'){
						$(".mail").find('.mail_body').hide();
					}	
					inventory_items_to_array(data);
					Array_print(messages_array,1500);	
					
					$.ajax({
						type: "GET",
						url: "api.php",
						data: {  
							'action': 'count_messages',
							'type': type,
						},
						dataType: "json",
						success: function(data){
							console.log("New Message Count "+data);
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
function inventory_items_to_array(JSONobject){
	var message_count;
	var question_count;
	messages_array = [];
	index_array = [];
	switch (type){
		case "blog":
		case "mail":
			for (var i=0; i<JSONobject.length;i++){
				var unlocked_items =  JSONobject[i].body;
				var stream =  unlocked_items.replace(/\t/g, "");
				if(JSONobject[i].question == 1){
					stream = stream.replace(/\n/gi, "");
					stream = stream.trim();
					messages_array.push(stream);
					index_array.push(JSONobject[i].id);
				}
				else{
					stream = stream.split("[");
					for(var k = 1; k < stream.length; k++){
						if (stream[k].length>0) {
							messages_array.push("["+stream[k]);
							index_array.push(JSONobject[i].id);		
						}
					}
				}			
			}
			break;
		default:
			for (var i=0; i<JSONobject.length;i++){
				var unlocked_items =  JSONobject[i].body;
				var stream =  unlocked_items.replace(/\t/g, "");
				//var regExp = /q./;
				//var questions = regExp.exec(stream);
				stream = stream.split("\n");
				for(var k = 0; k < stream.length; k++){
					if(stream[k].length>0 ){
						messages_array.push(stream[k]);
						index_array.push(JSONobject[i].id);
					}
				}
			}
	}
}
function inventory_attribute_to_array(JSONobject){
	var message_count = 0;
	var question_count = 0;
	messages_array = [];
	index_array = [];
	if(JSONobject.length==1){
		 index_array[0] = JSONobject[0].inventory_id;
		 select(0);
		 spinner.stop();

	}
	else{
		for (var i=0; i<JSONobject.length;i++){
			messages_array[i] = [];
			index_array[i] = JSONobject[i].inventory_id;
			var regExp = /\[(.*?)\]/;
			var names = regExp.exec(JSONobject[i].inventory_name);
			if (names != null){
				var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
				var conversation_name = JSONobject[i].inventory_name.replace(/\[(.*?)\]/g, "");
				if (type!='blog')
					$("#content-area").append('<div class="message_wrapper conversation" onClick="select('+i+');"><img class="profile_photo" style="float:left"; src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><div style="padding-left:15px;float:left;width:70%;"><span>'+name+'</span> - '+ conversation_name+'<br/><br/>' + JSONobject[i].message_count +' Messages <br> '+JSONobject[i].question_count+' Response pending</div></div>');
				else 
					$("#content-area").append('<div class="blog_wrapper" onClick="select('+i+');"><header><span>'+name+'</span> </header><div class="content" style="background:url(styles/'+PROJECT_ID+'/episodes/episode_'+(i+1)+'.jpg);background-size:cover;">'+ conversation_name+'</div></div>');

			}
			else $("#content-area").append('<div class="message_wrapper conversation" onClick="select('+i+');">'+ JSONobject[i].inventory_name+' - Messages: ' + JSONobject[i].message_count +' - Question to be answer: '+ JSONobject[i].question_count +'</div>');
		}
		spinner.stop();

	}
}	
function Array_print(messages, delay){
	unfolding_interval = setInterval(function() {

		if (messages.length<=0)return "Done";
		
		index=index_array.shift();
		message=messages.shift().trim();
		
		if (message[0]== "q"){
			var questions = message.slice(2);
			questions = questions.split("||");
			var number_of_questions = question_height*questions.length;
			$('#content-area').height(84-number_of_questions+"%");
			$('#no-bounce-wrapper').height(84-number_of_questions+"%");
			$('#send-message-area').height(number_of_questions+"%");
			$("#content-area").append('<div class="message_wrapper"></div>');
			$("#send-message-area").append('<div class="answer-divider"></div>');
			for(var j= 0; j < questions.length; j++){
				var regExp = /\[(.*?)\]/;
				var names = regExp.exec(questions[j]);
				if (names != null){
					questions[j] = questions[j].replace(/\[(.*?)\]/g, "");
					$("#send-message-area").append('<button class="answer" name="'+questions[j].trim()+'" value="'+names[1]+'" onClick="SendAnswer(this);" index="'+index+'">'+questions[j].trim()+'</button>');
					$("#send-message-area").append('<div class="answer-divider"></div>');
				}
			}
		}
		else{
			switch (type){
				//GoSocial - Fakebook //		
				case 'gosocial':
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
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'</div>');
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
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'</div>');
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
								else html_message='<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>';
							}
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								audio = audio.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=audio+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div></div>';										
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
								else html_message='<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>';
							}
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							var urls = regExp_video.exec(video);
							if (urls != null){
								video = video.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								video = video.trim();
								var html_video = urls[0].replace("watch?v=", "embed/");
								html_video = html_video+"?showinfo=0&controls=0&autohide=1";
								html_message+=video+'<br><iframe style="padding-top:4px;" width="100%" src="'+html_video+'"  frameborder="0" ></iframe></div></div>';										
							}
							else html_message+=video+'</div></div>';
							$("#content-area").append(html_message);
							break;	
							
						//Comment
						case "c":
							var comment = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(comment);
							if (names != null){
								comment = comment.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name ="";

							if (messages.length>0){
								if(messages[1][1]=="p" || messages[1][1]=="i" || messages[1][1]=="v"){
									$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div>');
								}
								else{
									$("#content-area").append('<div class="gosocial_comment"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div>');
								}
							}
							else{
								$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div>');

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
							if (messages.length>0){
								if (messages[1][1]=="p" || messages[1][1]=="i" || messages[1][1]=="v"){
									$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div></div>');
								}
								else {
									$("#content-area").append('<div class="gosocial_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div></div>');
								}
							}
							else {
								$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div></div>');
							}
							break;
						}
					break;
					// MicroBlog - Tuiter //
				case "microblog":
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
							$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span><div class="tuit_content">'+tuit+'</div></div>');
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
							//var regExp = /\{(.*?)\}/;
							var images = regExp.exec(tuit);
							
							if (images != null){
								//tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+images[1]+'">'+images[1]+'</a>');
								tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><span class="screenname"> @'+name+'</span> <div class="tuit_content">'+tuit+'<br/ ><img src="'+images[1]+'"></div></div>');
							}
							else{ 
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><span class="screenname"> @'+name+'</span><div class="tuit_content">'+tuit+'</div></div>');
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
								else html_message='<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><span class="screenname"> @'+name+'</span><br/><div class="tuit_content">';
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
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var videos = regExp.exec(tuit);
							if (videos != null){

								//tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+videos[1]+'">'+videos[1]+'</a>');							
								tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								var video = videos[1].trim();
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
								video = video+"?showinfo=0&controls=0&autohide=1";							
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span> <div class="tuit_content">'+tuit+'<br/><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></div></div>');
							}
							else{ 
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span><div class="tuit_content">'+tuit+'</div></div>');
							}
							break;
						
						}
					break;

					//Msngr - WhatsUp //
				case "msngr":
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
							var message_content = '<div class="message_wrapper" ><img class="profile_photo" style="float:right;" src="';
							
							if (name ==  "You") {
								//$("#content-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:right;" src="'+PROFILE_IMAGE+'"><div class="message_right"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>'+message+' </div></div>');
								//message_content+=
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
								//$("#content-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:left;" src="images/'+names[1].toLowerCase()+'.png"><div class="message_left"><span>'+name+'</span><br/>'+message+' </div></div>');
								var POSITION = 'left';
								var IMAGE = 'styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png';
								var	NAME = name;
							}
							
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							message = message.trim();
							var videos = regExp_video.exec(message);
							if (videos != null){
								message = message.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								//file_url = file_url.replace("v.", '');							
								video = videos[0].trim();
								video = video.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/,"");
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
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
											message+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="images/nohex_file.png" style="vertical-align:middle"><a href="'+file_url+'" target="_blank"> FILE </a></div><br>';	
									}
								}
							}	
							$("#content-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:'+POSITION+';" src="'+IMAGE+'"><div class="message_'+POSITION+'"><span>'+NAME+'</span><br/>'+message+'</div></div>');

							//$("#parchment-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:'+POSITION+';" src="'+IMAGE+'"><div class="message_'+POSITION+'"><div class="top"></div><div class="middle"><span>'+NAME+'</span><br/>'+message+'</div><div class="bottom"></div></div></div>');
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
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:right;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><div class="message_right image"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>';
								}
								else html_message='<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><div class="message_left image"><span>'+name+'</span><br/>';
							}
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								image = image.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=image+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><a href="'+urls[1]+'" data-lightbox="'+urls[1]+'" data-title="'+image+'"><image src="'+urls[1]+'" style="vertical-align:middle"></a></div><br>';										
								//html_message+=image+'</div></div>';
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
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:right;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><div class="message_right audio"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>';
								}
								else html_message='<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><div class="message_left audio"><span>'+name+'</span><br/>';
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
									html_message='<div class="message_wrapper"><img class="profile_photo" style="float:right;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><div class="message_right video"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>';
								}
								else html_message='<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><div class="message_left video"><span>'+name+'</span><br/>';
							}
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							var urls = regExp_video.exec(video);
							if (urls != null){
								video = video.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								video = video.trim();
								var html_video = urls[0].replace("watch?v=", "embed/");
								html_video = html_video+"?showinfo=0&controls=0&autohide=1";
								html_message+=video+'<br><iframe style="padding-top:4px;" width="100%" src="'+html_video+'"  frameborder="0" ></iframe></div></div>';										
							}
							else html_message+=video+'</div></div>';
							$("#content-area").append(html_message);
							break;
						//Date	
						case "d":
							var date = message.slice(2);							
							$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center">'+date+'</div>');
							break;	
						}
						
					break;	
				case "blog":	
				case "mail":
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
								
								if (body[j][0]=='i' && body[j][1]=='.'){
									body[j] = body[j].slice(2);
									html_tags+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+body[j].trim()+'" style="vertical-align:middle"></div><br>';

								}
								else {
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

					//var regExp_video = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
					//var regExp_video = /^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/;
					//var regExp_video = /^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com)\/*/;
					//var regExp_video = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
					var regExp_video = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
					file_url = file_url.trim();
					var videos = regExp_video.exec(file_url);
					if (videos != null){
						file_url = file_url.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
						//file_url = file_url.replace("v.", '');							
						video = videos[0].trim();
						video = video.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/,"");
						video = video.slice(6);
						video = video.replace("watch?v=", "embed/");
						video = video+"?showinfo=0&controls=0&autohide=1";
						$("#content-area").append('<div style="position:relative">'+name+'<iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe></div>');
					}
					else{
						var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
						var urls = regExp.exec(file_url);
						var extension = urls[1].substr(urls[1].length-3, 3);
						switch (extension){
							case "jpeg":
							case "png":
							case "gif":
								$("#content-area").append('<div class="message_wrapper"><image src="'+file_url+'" style="vertical-align:middle"><span> '+name+'  </span><a href="'+file_url+'" target="_blank"> FILE </a></div>');	
							break
							case "mp3":
							case "wav":
								$("#content-area").append('<div class="message_wrapper"><span>'+names[1]+'</span><br/>'+message+'<br/><br/><audio controls><source src="'+file_url+'" type="audio/mpeg"></audio></div>');
							default:
								$("#content-area").append('<div class="message_wrapper"><image src="styles/'+PROJECT_ID+'/images/nohex_file.png" style="vertical-align:middle"><span> '+name+'  </span><a href="'+file_url+'" target="_blank"> FILE </a></div>');	

						}
					}

					break;
				}	
			}

		if(delay>0 && type!="mail" && type!="blog" ){
			$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);
			$("#no-bounce-wrapper").animate({scrollTop: $('#no-bounce-wrapper').prop("scrollHeight")}, 500);
			$(".no-bounce").animate({scrollTop: $('.no-bounce').prop("scrollHeight")}, 500);
			$(".no-bounce > div").animate({scrollTop: $('.no-bounce > div').prop("scrollHeight")}, 500);
			$(".no-bounce > div > div").animate({scrollTop: $('.no-bounce > div > div').prop("scrollHeight")}, 500);

		}
		if (messages.length==0){
			clearInterval(unfolding_interval);
			if(delay==0&&type!="mail"){
				$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);
				$("#no-bounce-wrapper").animate({scrollTop: $('#no-bounce-wrapper').prop("scrollHeight")}, 500);
				$(".no-bounce").animate({scrollTop: $('.no-bounce').prop("scrollHeight")}, 500);
				$(".no-bounce > div").animate({scrollTop: $('.no-bounce > div').prop("scrollHeight")}, 500);
				$(".no-bounce > div > div").animate({scrollTop: $('.no-bounce > div > div').prop("scrollHeight")}, 500);			
			}
		}
	},delay);			
}

function first_array_print(messages){
	//console.log('first_array_print');
	var count_index = 0;
	while (messages.length>0){
	
		count_index++;
		if (messages[0][0]== "q"){
			var questions = messages.shift().slice(2);
			questions = questions.split("||");
			var number_of_questions = question_height*questions.length;
			$('#content-area').height(84-number_of_questions+"%");
			$('#no-bounce-wrapper').height(84-number_of_questions+"%");
			$('#send-message-area').height(number_of_questions+"%");
			$("#content-area").append('<div class="message_wrapper"></div>');
			$("#send-message-area").append('<div class="answer-divider"></div>');
			for(var j= 0; j < questions.length; j++){
				var regExp = /\[(.*?)\]/;
				var names = regExp.exec(questions[j]);
				if (names != null){
					questions[j] = questions[j].replace(/\[(.*?)\]/g, "");
					$("#send-message-area").append('<button class="answer" name="'+questions[j].trim()+'" value="'+names[1]+'" onClick="SendAnswer(this);" index="'+index_array[0]+'">'+questions[j].trim()+'</button>');
					$("#send-message-area").append('<div class="answer-divider"></div>');
				}
			}
		}
		else{
			switch (type){
				//GoSocial - Fakebook //		
				case 'gosocial':
					switch (messages[0][0]){
						//Post
						case "t":
						case "p":
							var post = messages.shift().slice(2);							
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
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'</div>');
							}
							break;
						
						//Post with Image
						case "i":
							var post = messages.shift().slice(2);							
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
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'</div>');
							}
							break;
						//Post
						case "v":
							var post = messages.shift().slice(2);							
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
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'<br/ ><img src="'+images[1]+'"></div>');
							}
							else{
								$("#content-area").append('<div class="gosocial_post"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><br/>'+post+'</div>');
							}
							break;	
							
						//Comment
						case "c":
							var comment = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(comment);
							if (names != null){
								comment = comment.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name ="";

							if (messages.length>0){
								if(messages[1][1]=="p" || messages[1][1]=="i" || messages[1][1]=="v"){
									$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div>');
								}
								else{
									$("#content-area").append('<div class="gosocial_comment"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div>');
								}
							}
							else{
								$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div>');

							}
							break;
						//Reply
						case "r":
							var comment = messages.shift().slice(3);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(comment);
							if (names != null){
								comment = comment.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);

							} 
							else var name = "";
							if (messages.length>0){
								if (messages[1][1]=="p" || messages[1][1]=="i" || messages[1][1]=="v"){
									$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div></div>');
								}
								else {
									$("#content-area").append('<div class="gosocial_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div></div>');
								}
							}
							else {
								$("#content-area").append('<div class="gosocial_comment gosocial_last_comment"><div class="gosocial_reply"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span> '+comment+' </div></div>');
							}
							break;
						}
					break;
					// MicroBlog - Tuiter //
				case "microblog":
					switch (messages[0][0]){
						//Tweet only text
						case "t":
							var tuit = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(tuit);
							if (names != null){
								tuit = tuit.replace(/\[(.*?)\]/g, "");
							} 	
							$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span><div class="tuit_content">'+tuit+'</div></div>');
							break;
						//Tweet with image	
						case "i":
							var tuit = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(tuit);
							if (names != null){
								tuit = tuit.replace(/\[(.*?)\]/g, "");
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
							} 
							else var name =""; 
							
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							//var regExp = /\{(.*?)\}/;
							var images = regExp.exec(tuit);
							
							if (images != null){
								tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+images[1]+'">'+images[1]+'</a>');
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><span class="screenname"> @'+name+'</span> <div class="tuit_content">'+tuit+'<br/ ><img src="'+images[1]+'"></div></div>');
							}
							else{ 
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><span>'+name+'</span><span class="screenname"> @'+name+'</span><div class="tuit_content">'+tuit+'</div></div>');
							}
							break;
						case "v":
							var tuit = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(tuit);
							if (names != null){
								tuit = tuit.replace(/\[(.*?)\]/g, "");
							} 
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var videos = regExp.exec(tuit);
							if (videos != null){

								tuit = tuit.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+videos[1]+'">'+videos[1]+'</a>');							
								var video = videos[1].trim();
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
								video = video+"?showinfo=0&controls=0&autohide=1";							
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span> <div class="tuit_content">'+tuit+'<br/><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></div></div>');
							}
							else{ 
								$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="styles/'+PROJECT_ID+'/images/'+names[1].toLowerCase()+'.png"><span>'+names[1]+'</span><span class="screenname"> @'+names[1]+'</span><div class="tuit_content">'+tuit+'</div></div>');
							}
							break;
						}
					break;
					//Msngr - WhatsUp //
				case "msngr":
					switch (messages[0][0]){
						//Conversation
						case "t":
						case "c":
							var message = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(message);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								message = message.replace(/\[(.*?)\]/g, "");
							}
							else var name = "";	
							var message_content = '<div class="message_wrapper" ><img class="profile_photo" style="float:right;" src="';
							
							if (name ==  "You") {
								//$("#content-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:right;" src="'+PROFILE_IMAGE+'"><div class="message_right"><span>'+AUDIENCE_FIRST_NAME+'</span><br/>'+message+' </div></div>');
								//message_content+=
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
								//$("#content-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:left;" src="images/'+names[1].toLowerCase()+'.png"><div class="message_left"><span>'+name+'</span><br/>'+message+' </div></div>');
								var POSITION = 'left';
								var IMAGE = 'styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png';
								var	NAME = name;
							}
							
							var regExp_video = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
							message = message.trim();
							var videos = regExp_video.exec(message);
							if (videos != null){
								message = message.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							
								//file_url = file_url.replace("v.", '');							
								video = videos[0].trim();
								video = video.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/,"");
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
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
											message+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="images/nohex_file.png" style="vertical-align:middle"><a href="'+file_url+'" target="_blank"> FILE </a></div><br>';	
									}
								}
							}	
							$("#content-area").append('<div class="message_wrapper" ><img class="profile_photo" style="float:'+POSITION+';" src="'+IMAGE+'"><div class="message_'+POSITION+'"><span>'+NAME+'</span><br/>'+message+'</div></div>');
						break;		
						//Image	
						case "i":
							var image = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(image);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								image = image.replace(/\[(.*?)\]/g, "");
								if (name ==  "You") {
									$("#content-area").append('<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><div class="message_left image"><span>'+AUDIENCE_FIRST_NAME+'</span><br/><img style="padding-top:4px;" src="'+image+'"></div></div>');
								}
								else $("#content-area").append('<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><div class="message_left image"><span>'+name+'</span><br/><img style="padding-top:4px;" src="'+image+'"></div></div>');
							}
							break;
						//Video
						case "v":
							var video = messages.shift().slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(video);
							if (names != null){
								var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
								video = video.replace(/\[(.*?)\]/g, "");
							}	
							else var name = "";
							var regExp_video = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var videos = regExp_video.exec(video);
							if (videos != null){
								//video = video.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+videos[1]+'">'+videos[1]+'</a>');							
								video = videos[1].trim();
								video = video.slice(6);
								video = video.replace("watch?v=", "embed/");
								video = video+"?showinfo=0&controls=0&autohide=1";
							}
							if (name == "You") {
								$("#content-area").append('<div class="message_wrapper"><img class="profile_photo" style="float:right>;" src="styles/'+PROJECT_ID+'/'+PROFILE_IMAGE+'"><div class="message_right video"><span>'+AUDIENCE_FIRST_NAME+'</span><br/><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe></div></div>');
							}
							else $("#content-area").append('<div class="message_wrapper"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png"><div class="message_left video"><span>'+name+'</span><br/><iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe></div></div>');
					
							break;
						//Date	
						case "d":
							var date = messages.shift().slice(2);							
							$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center">'+date+'</div>');
							break;	
						}
					break;	
				case "blog":
					var email = messages.shift();							
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

								var html_tags = '<div class="mail" onClick="openBody(this)">';
								var display = 'style="display:block;"';
						}
						else{
							var html_tags = '<div class="mail bold" onClick="openBody(this)">';
							var display = '';
						}
							
						html_tags+='<span>'+name+'</span><br/><div style="padding-left:45px">'+body[0]+'</div><div class="mail_body" '+display+'>';
						
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
								console.log(body[j]);
								body[j] = body[j].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");
								console.log(body[j]);
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
				case "mail":
					var email = messages.shift();							
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

						if (messages.length>0){
							$open_body = true;
							for ($k=0;$k<messages.length;$k++){
								if (messages[$k][0]!='q') $open_body=false;
							}
							if($open_body){
								var html_tags = '<div class="mail" onClick="openBody(this)">';
								var display = 'style="display:block;"';
							}
						
							else {
								var html_tags = '<div class="mail bold" onClick="openBody(this)">';
								var display = '';
							}	
						}
						else{
							var html_tags = '<div class="mail" onClick="openBody(this)">';
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
								
								if (body[j][0]=='i' && body[j][1]=='.'){
									body[j] = body[j].slice(2);
									html_tags+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+body[j].trim()+'" style="vertical-align:middle"></div><br>';

								}
								else {
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
							else html_tags+=body[j]+'<br>';	
						}
						html_tags+='</div></div>';
						
						$("#content-area").prepend(html_tags);
					break;
				case "media":
					var index = index_array.shift();	 
					var file_url = messages.shift();
					var regExp = /\[(.*?)\]/;
					var names = regExp.exec(file_url);
					if (names != null){
						var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
						file_url = file_url.replace(/\[(.*?)\]/g, "");
					}	
					else var name = "";	

					//var regExp_video = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
					//var regExp_video = /^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/;
					//var regExp_video = /^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com)\/*/;
					//var regExp_video = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
					var regExp_video = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
					file_url = file_url.trim();
					var videos = regExp_video.exec(file_url);
					if (videos != null){
						file_url = file_url.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
						//file_url = file_url.replace("v.", '');							
						video = videos[0].trim();
						video = video.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/,"");
						video = video.slice(6);
						video = video.replace("watch?v=", "embed/");
						video = video+"?showinfo=0&controls=0&autohide=1";
						$("#content-area").append('<div style="position:relative">'+name+'<iframe style="padding-top:4px;" width="100%" src="'+video+'"  frameborder="0" ></iframe></div>');
					}
					else{
						var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
						var urls = regExp.exec(file_url);
						var extension = urls[1].substr(urls[1].length-3, 3);
						switch (extension){
							case "jpeg":
							case "png":
							case "gif":
								$("#content-area").append('<div class="message_wrapper"><image src="'+file_url+'" style="vertical-align:middle"><span> '+name+'  </span><a href="'+file_url+'" target="_blank"> FILE </a></div>');	
							break
							case "mp3":
							case "wav":
								$("#content-area").append('<div class="message_wrapper"><span>'+names[1]+'</span><br/>'+message+'<br/><br/><audio controls><source src="'+file_url+'" type="audio/mpeg"></audio></div>');
							default:
								$("#content-area").append('<div class="message_wrapper"><image src="styles/'+PROJECT_ID+'/images/nohex_file.png" style="vertical-align:middle"><span> '+name+'  </span><a href="'+file_url+'" target="_blank"> FILE </a></div>');	

						}
					}
					break;
				}	
			}
			index_array.shift();
		}
		if(type!="mail" && type!="blog"){
			$("#no-bounce-wrapper").animate({scrollTop: $('#no-bounce-wrapper').prop("scrollHeight")}, 500);
			$(".no-bounce").animate({scrollTop: $('.no-bounce').prop("scrollHeight")}, 500);
			$(".no-bounce > div").animate({scrollTop: $('.no-bounce > div').prop("scrollHeight")}, 500);
			$(".no-bounce > div > div").animate({scrollTop: $('.no-bounce > div > div').prop("scrollHeight")}, 500);	
			$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);

		}
	
}

function select(i){
	index=i;
	attribute_index = index_array[i];
	$('#content-area').empty();
	$('#send-message-area').empty();
	console.log(index_array[i]);
	$.ajax({
		type: "GET",
		url: "api.php",
		data: {  
			'action': 'select_inventory_items',
			'inventory_id': index_array[i],
			//'audience_id' : AUDIENCE_ID,
		},
		dataType: "json",
		success: function(data){
			console.log(data);
			inventory_items_to_array(data);
			Array_print(messages_array,0);
			//first_array_print(messages_array);

		},
		error: function(data){
			console.log("ERROR: "+JSON.stringify(data));
		}

	});		
}
function back(){
	clearInterval(unfolding_interval);
	if (index==-1 || inventory_attributes.length==1){
		window.location.href = 'desktop.php';
	}
	else{
		$("#content-area").empty();
		$("#send-message-area").empty();
		
		$.ajax({
			type: "GET",
			url: "api.php",
			data: {  
				'action': 'select_inventory_attributes',
				'type': type,
				//'audience_id' : AUDIENCE_ID,
			},
			dataType: "json",
			success: function(data){
				inventory_attributes = data;
				inventory_attribute_to_array(data);
			},
			error:function(data){
			   console.log("ERROR: " + JSON.stringify(data));
			}
		});
		index=-1;
	}
}
function openBody(mail){
	if( $(mail).find('.mail_body').is(':visible') ) {
		//alert("yeah");
		$("#mail-area").animate({scrollTop: 0}, 300);
		$(mail).find('.mail_body').hide("slow");
		$(mail).find('.minimize_icon').find('img').attr("src", "images/maximize.png");
	}
	else {
		$(".mail").find('.mail_body').hide();
		$(mail).find('.minimize_icon').find('img').attr("src", "images/minimize.png");

		$(mail).find('.mail_body').show(200, function() {
			$("#content-area").animate({scrollTop: $(mail).offset().top-57}, 200);
		});
		
		//$("#mail-area").animate({scrollTop: $(mail).offset().top-57}, 500);
		//$("#mail-area").animate({scrollTop: $(mail).offset().top - 57}, 500);
		

		var audio = $("#sound3")[0];
		audio.play();
	}
	
	$(mail).removeClass('bold');				
}
