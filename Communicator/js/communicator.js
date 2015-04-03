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
var target;	

var selected=false;
var still_printing = false;
var CHARACTER_NAME;
var count_messages_same_feed  = 0;
var count_messages_same_type = 0;
var count_messages_other_types = 0;
var QUESTION="";

var progressbar_Interval;
var MESSAGE_NAME;
var MESSAGE_PROFILE_IMAGE;
var MESSAGE_POSITION;
var waitingForAnswer;

$(document).ready(function(){
	var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
	
	if( iOS ){
		question_height=8;
		document.addEventListener('touchmove', function(e) { e.preventDefault(); }, false);
		var scroller = document.getElementById('content-area');
		preventOverScroll(scroller);
	}
	else question_height=8;
	
	/* Push Notifications */
	/*
	setInterval(function(){
		console.log("still_printing_step_1 "+still_printing);
		if(!still_printing){
			$.ajax({
				type: "GET",
				url: "api.php",
				data: {  
				'action': 'count_messages',
				'type': type,
				'message_feed_id':message_feed_id,
				},
				dataType: "json",
				success: function(data){
					if(!still_printing && selected){
						if (data.count_messages_same_feed>count_messages_same_feed){
							console.log("New Messages in the feed");
							$.ajax({
								type: "GET",
								url: "api.php",
								data: {  
									'action': 'check_push_notifications',
									'message_feed_id': message_feed_id
								},
								dataType: "json",
								success: function(data){
									console.log("still_printing_step_3 "+still_printing);

									if(!still_printing && selected){
										still_printing=true;
										if(data!=null && type =='mail'){
											$(".mail").find('.mail_body').hide();
										}	
										console.log(data);
										messages_to_array(data);
										Array_print(messages_array,DELAY);
									}
								}
							});
						}
						
						if (data.count_messages_same_type>count_messages_same_type){
							console.log("New Messages in the channel");

							$('#back').prepend("<div class='dot'></div>");
							var notification = $('#notification')[0]; 
							notification.play();    
							navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
							if (navigator.vibrate) {
								navigator.vibrate(1000);
							}
						}
						
						
						if (data.count_messages_other_types > count_messages_other_types){
							
							console.log("New Messages in the app");
							$('#home').prepend("<div class='dot'></div>");
							var notification = $('#notification')[0]; 
							notification.play();    
							navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
							if (navigator.vibrate) {
								navigator.vibrate(1000);
							}
						}				
						count_messages_same_feed=data.count_messages_same_feed;
						count_messages_same_type=data.count_messages_same_type;
						count_messages_other_types=data.count_messages_other_types;
						
						//Debug
						console.log("same_feed: "+count_messages_same_feed);
						console.log("same_type: "+count_messages_same_type);
						console.log("other types: "+count_messages_other_types);
					}
									
					else if(!still_printing && !selected){
						if (data.count_messages_same_type>count_messages_same_type){
							console.log("New Messages in the channel");

							$.ajax({
								type: "GET",
								url: "api.php",
								data: {  
									'action': 'select_message_feeds',
									'type': type,
								},
								dataType: "json",
								success: function(data){
									console.log(data);
									if (typeof data !== 'undefined' && data.length > 0) {
										$('#content-area').empty();
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
							
						}
						if (data.count_messages_other_types > count_messages_other_types){
							
							console.log("New Messages in the app");
							$('#home').prepend("<div class='dot'></div>");
							var notification = $('#notification')[0]; 
							notification.play();    
							navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
							if (navigator.vibrate) {
								navigator.vibrate(1000);
							}
						}				
						count_messages_same_feed=data.count_messages_same_feed;
						count_messages_same_type=data.count_messages_same_type;
						count_messages_other_types=data.count_messages_other_types;
						
						console.log("same_feed: "+count_messages_same_feed);
						console.log("same_type: "+count_messages_same_type);
						console.log("other types: "+count_messages_other_types);
					}
				},
				error:function(data){
				   console.log("ERROR: " + JSON.stringify(data));
				}
			})
		}
	}, 10000);
	*/
	
	target = document.getElementById('spinner');
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
			console.log(data);
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
	$.ajax({
		type: "GET",
		url: "api.php",
		data: {  
		'action': 'count_messages',
		'type': type,
		'message_feed_id':message_feed_id,
		},
		dataType: "json",
		success: function(data){
			count_messages_same_feed=data.count_messages_same_feed;
			count_messages_same_type=data.count_messages_same_type;
			count_messages_other_types=data.count_messages_other_types;
			
			/* Debug */
			/*
			console.log("First Count");
			console.log("same_feed: "+count_messages_same_feed);
			console.log("same_type: "+count_messages_same_type);
			console.log("other types: "+count_messages_other_types);
			*/
		},
		error:function(data){
		   console.log("ERROR: " + JSON.stringify(data));
		}
	})
});	
		
function SendAnswer(matchphrase,index){
	$("#send-message-area").removeClass("send-message-area-displayed");
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
			'index' : index,
			'message_feed_id':message_feed_id

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
					//console.log(JSON.stringify(data));
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
						'message_feed_id':message_feed_id,
						},
						dataType: "json",
						success: function(data){

							if (data.count_messages_same_type>count_messages_same_type){
								$('#back').prepend("<div class='dot'></div>");
								var notification = $('#notification')[0]; 
								notification.play();    
								navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
								if (navigator.vibrate) {
									navigator.vibrate(1000);
								} 
							}
							if (data.count_messages_other_types>count_messages_other_types){
								$('#home').prepend("<div class='dot'></div>");
								var notification = $('#notification')[0]; 
								notification.play();    
								navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
								if (navigator.vibrate) {
									navigator.vibrate(1000);
								}
							}				
							count_messages_same_feed=data.count_messages_same_feed;
							count_messages_same_type=data.count_messages_same_type;
							count_messages_other_types=data.count_messages_other_types;
							/* Debug */
							/*
							console.log("Update counters by refresh");
							console.log("same_feed: "+count_messages_same_feed);
							console.log("same_type: "+count_messages_same_type);
							console.log("other types: "+count_messages_other_types);
							*/

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
function SendOpenText(matchphrase,index){
	$("#send-message-area").removeClass("send-message-area-displayed");
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
			'action': 'send_open_text',
			'matchphrase' : matchphrase,
			'question': QUESTION,
			'character' : CHARACTER_NAME,
			'type' : type,
			'index' : index,
			'message_feed_id':message_feed_id

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
					//console.log(JSON.stringify(data));
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
						'message_feed_id':message_feed_id,
						},
						dataType: "json",
						success: function(data){

							if (data.count_messages_same_type>count_messages_same_type){
								$('#back').prepend("<div class='dot'></div>");
								var notification = $('#notification')[0]; 
								notification.play();    
								navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
								if (navigator.vibrate) {
									navigator.vibrate(1000);
								} 
							}
							if (data.count_messages_other_types>count_messages_other_types){
								$('#home').prepend("<div class='dot'></div>");
								var notification = $('#notification')[0]; 
								notification.play();    
								navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
								if (navigator.vibrate) {
									navigator.vibrate(1000);
								}
							}				
							count_messages_same_feed=data.count_messages_same_feed;
							count_messages_same_type=data.count_messages_same_type;
							count_messages_other_types=data.count_messages_other_types;
							/* Debug */
							/*
							console.log("Update counters by refresh");
							console.log("same_feed: "+count_messages_same_feed);
							console.log("same_type: "+count_messages_same_type);
							console.log("other types: "+count_messages_other_types);
							*/

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
						if(last_body[myIndex].trim()[0] != "" && last_body[myIndex].trim()[0] != "d" && last_body[myIndex].trim()[0] != "o" && last_body[myIndex].trim()[0] != "q" ){
							found=true;
						}
					}
				}
				else found=true;
			}		
			if(myIndex<last_body.length)  var tagline = last_body[myIndex].trim().slice(2).replace(/\[(.*?)\]/g, "");
			else if(type=="Blog" || type=="Mail") var tagline = "";
			else var tagline = "" ;
			
			tagline=tagline.slice(0,40);
			tagline+="...";
			if(type=='Blog' && PROJECT_ID=='2993'){
				$("#content-area").append('<div class="blog_wrapper" onClick="select('+JSONobject[i].message_feed_id+','+i+');"><header><span>'+character_name+'</span> </header><div class="content" style="background:url(styles/'+PROJECT_ID+'/episodes/episode_'+(i+1)+'.jpg);background-size:cover;">'+ character_name +'</div></div>');
			}
			else if(type=="Blog" || type=="Mail"){
				//$("#content-area").append('<div class="blog_wrapper" onClick="select('+JSONobject[i].message_feed_id+','+i+');"><header><span>'+character_name+'</span> </header><div class="content" style="background:url(styles/'+PROJECT_ID+'/episodes/episode_'+(i+1)+'.jpg);background-size:cover;"><div></div>');
				$("#content-area").append('<div class="feed" onClick="select('+JSONobject[i].message_feed_id+','+i+');"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+character_name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="feed_name" >'+character_name+'</div><div class="feed_tagline"></div><div class="feed_notifications">' + JSONobject[i].message_count +' Messages <br>' + JSONobject[i].new_message_count +' New messages <br> '+JSONobject[i].question_count+' Response pending</div></div></div>');
			}

			else {
				$("#content-area").append('<div class="feed" onClick="select('+JSONobject[i].message_feed_id+','+i+');"><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+character_name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="feed_name" >'+character_name+'</div><div class="feed_tagline">'+ tagline+'</div><div class="feed_notifications">' + JSONobject[i].message_count +' Messages <br>' + JSONobject[i].new_message_count +' New messages <br> '+JSONobject[i].question_count+' Response pending</div></div></div>');
			}
		}
		spinner.stop();
	}
}
function messages_to_array(JSONobject){
	var message_count;
	var question_count;
	messages_array = [];
	index_array = [];
	//console.log("MAIL: ");
	//console.log(JSONobject);

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
						var stripped_stream = stream.replace(/\<br\s*[\/]?\>/gi, "<br\>\n");
						stripped_stream = stripped_stream.replace(/\<\/span\>/gi, "\n");
						stripped_stream = stripped_stream.replace(/\n+/gi, "\n");
						stream_splitted = stripped_stream.split("\n");
						
						body_without_questions= [];
						for(var k = 0; k < stream_splitted.length; k++){
							stripped_message = stream_splitted[k].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");

							if (stripped_message[0]=="q" && stripped_message[1]=="."){
								email_questions.push(stripped_message);
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
			gate = [];
			//console.log(JSONobject[i].body);
			for (var i=0; i<JSONobject.length;i++){
				if($.inArray(JSONobject[i].id, messages_id_array)==-1){
					messages_id_array.push(JSONobject[i].id);
					var unlocked_items =  JSONobject[i].body;
					var stream =  unlocked_items.replace(/\t/g, "");
					var regExp = /g\./;
					var gates = regExp.exec(stream);
					stream = stream.split("\n");
					for(var k = 0; k < stream.length; k++){
						if(stream[k].length>0 ){
							messages_array.push(stream[k]);
							index_array.push(JSONobject[i].id);
							question_array.push(JSONobject[i].question && JSONobject[i].already_read);
							if (gates != null){
								gate.push(stream[k]);
								gate_index=JSONobject[i].id;
							}

						}
					}
				}
				else console.log("Repeated message: "+JSONobject[i].id);
			}
	}
}
	
function Array_print(messages, delay){
	var count_index = 0;
	still_printing=true;
	unfolding_interval = setInterval(function() {
		
		if (messages.length<=0){
			clearInterval(unfolding_interval);
			still_printing=false;
			if(delay==0&&type!="Mail"&& type!="Blog" && type!="Microblog"){
				$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);
			}
			$('#send-message-area').show();
			return "Done";
		}
		if (selected!=true){
			clearInterval(unfolding_interval);
			still_printing=false;
			return "Done";
		}
		count_index++;
		index=index_array.shift();
		message=messages.shift().trim();
		question=question_array.shift().trim();
		
	
		if ((message[0]== "q" || message[0]== "o" || message[0]=="x" || message[0]=="b" || message[0]=="r"  || message[0]=="e")&& message[1]== "." && question!=true){	
			$('#send-message-area').empty();
			clearInterval(progressbar_Interval);
			waitingForAnswer=false;
		}
		//Questions - Fixed answers
		if (message[0]== "q" && message[1]== "." && question!=true){	
			var questions = message.slice(2);
			questions = questions.split("||");
			var number_of_questions = question_height*questions.length;
			$('#content-area').height(84-number_of_questions+"%");
			$('#send-message-area').height(number_of_questions+"%");
			//$("#content-area").append('<div class="message_wrapper"></div>');
			$("#send-message-area").append('<div class="answer-divider"></div>');
			$('#send-message-area').hide();
			var height_question=86/(questions.length);
			for(var j= 0; j < questions.length; j++){
				var regExp = /\[(.*?)\]/;
				var names = regExp.exec(questions[j]);
				if (names != null){
					questions[j] = questions[j].replace(/\[(.*?)\]/g, "");
					$("#send-message-area").append('<button class="answer" name="'+questions[j].trim()+'" style="height:'+height_question+'%"  value="'+names[1]+'" onClick="SendAnswer(\''+names[1].trim()+'\',\''+index+'\');" index="'+index+'">'+questions[j].trim()+'</button>');
					$("#send-message-area").append('<div class="answer-divider"></div>');
				}
			}
		}
		//Questions - Open Input
		else if (message[0]== "o" && message[1]== "." && question!=true){
			var openText = message.slice(2);	
		
			var regExp = /\[(.*?)\]/;
			var names = regExp.exec(openText);
			var prompt = "";
			if (names != null){
				QUESTION = names[1].split(",")[0].trim();
				prompt = names[1].split(",")[1].trim();

			}
			$("#send-message-area").addClass("send-message-area-displayed");
			//$("#send-message-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center;height:88%;padding-bottom:0px;padding-top: 4px;" ><input id="openTextInput" type="text" style="width:85%;height:70%;float:left;padding: 2px;" placeholder="." value="'+names[1]+' " autofocus  onfocus="this.value = this.value;"><button id="openTextButton" index="'+index+'" style="width:10%;height:75%;float:right;"></button></div>');
			$("#send-message-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center;height:88%;padding-bottom:0px;padding-top: 4px;" ><form id="opentextform"><input id="openTextInput" type="text" placeholder="'+prompt+' " value="" autofocus  onfocus="this.value = this.value;"><button id="openTextButton" index="'+index+'" >></button></form></div>');
			
			var form = document.getElementById("opentextform");
			form.onsubmit = function(){
				$("#openTextInput").click();
				return false;
			};
			$( "#openTextButton" ).click(function() {
				var matchphrase = $( "#openTextInput" ).val();
				//matchphrase=matchphrase.replace(names[1],"");
				//matchphrase="opentext:"+names[1]+" "+matchphrase;
				var index_from_the_input = $("#openTextButton").attr("index");
				SendOpenText(matchphrase,index_from_the_input);
			});
			input_height=7;
			$('#content-area').height(84-input_height+"%");
			$('#send-message-area').height(input_height+"%");
		}
		
		/* Unlocking pattern */
		else if (message[0]== "x" && message[1]== "." && question!=true){
			var pattern = message.slice(2);						
			var options = pattern.split(",");
			var patternMatchphrase = options[0];
			var patternValue = options[1];
			var seconds_to_fade = options[2];
			var seconds_to_solve = options[3];
			var margin = 15;
			var radius = 10;
			var matrix_rows = 3;
			var matrix_columns = 3;

			var pattern_height_absolute = ((radius*2)+(margin*2))*matrix_rows;
			var wrapper_height = $('#msngr-wrapper').height() - $('#msngr-header').height() - $('#buttons').height();
			var pattern_height =  35;
			
			$("#send-message-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center;background: #3382c0;height:100%;padding-top:0;padding-bottom:0;" ><div id="patternContainer" style="margin:auto" value="123" index="'+index+'" ></div></div>');
			$("#send-message-area").append('<div class="message_wrapper></div>');

			$('#content-area').height(81-pattern_height+"%");
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
				if(waitingForAnswer==true){
					SendAnswer(patternMatchphrase+" 0000",index);
				}
				else {
				}
			}, seconds_to_solve);
			
			lock.checkForPattern(parseInt(patternValue),function(){
				waitingForAnswer=false;
				SendAnswer(patternMatchphrase+" "+lock.getPattern(),index);
			},function(){
				waitingForAnswer=false;
				SendAnswer(patternMatchphrase+" "+lock.getPattern(),index);

			});  
		}
		
		// Progress Bar -  Time  Question	
		else if (message[0]== "b" && message[1]== "." && question!=true){
			
			var questions = message.slice(2);
			questions = questions.split("||");
			
			var number_of_questions = question_height*questions.length;
			
			var regExp = /\[(.*?)\]/;
			
			//var parameters = regExp.exec(questions[0])[1].split(",");
			var parameters = questions[0].split(",");
			console.log(parameters);
			var timeout_matchphrase = parameters[0];
			
			var time = parameters[1].split(":");
			var seconds = 0;
			var multiplier= 1;
			for (var t=time.length-1; t>=0;t--){
				seconds+=time[t]*multiplier;
				multiplier*=60;
			}
			

			$('#content-area').height(84-number_of_questions+"%");
			$('#send-message-area').height(number_of_questions+"%");
			//$("#content-area").append('<div class="message_wrapper"></div>');
			$("#send-message-area").append('<div class="answer-divider"></div>');
			//$('#send-message-area').hide();
			for(var j= 1; j < questions.length; j++){
				var names = regExp.exec(questions[j]);
				if (names != null){
					questions[j] = questions[j].replace(/\[(.*?)\]/g, "");
					$("#send-message-area").append('<button class="answer" name="'+questions[j].trim()+'" value="'+names[1]+'" onClick="clearInterval(progressbar_Interval);SendAnswer(\''+names[1].trim()+'\',\''+index+'\');" index="'+index+'">'+questions[j].trim()+'</button>');
					$("#send-message-area").append('<div class="answer-divider"></div>');
					
				}
			}
			$("#send-message-area").append('<div id="progressbar"><div id="progressbar_text"></div><div id="bar"></div></div>');
			var count=0;
			var bar_width=0;
			progressbar_Interval = setInterval(function() {
				bar_width = 100 -((count*100)/seconds);
				console.log(bar_width);
				$("#bar").width(bar_width+"%");
				
				//document.getElementById("progressbar_text").innerHTML = seconds-count;

				var seconds_remaining = seconds-count; 
				var hours = Math.floor(seconds_remaining / (60 * 60));
				var divisor_for_minutes = seconds_remaining % (60 * 60);
				var minutes = Math.floor(divisor_for_minutes / 60);
				var divisor_for_seconds = divisor_for_minutes % 60;
				var secs = Math.ceil(divisor_for_seconds);
				if (hours < 10) {hours = "0"+hours;}
				if (minutes < 10) {minutes = "0"+minutes;}
				if (secs < 10) {secs = "0"+secs;}
				document.getElementById("progressbar_text").innerHTML = hours+":"+minutes+":"+secs;	
				if(count>=seconds || bar_width<=0){
					console.log("Finished");
					clearInterval(progressbar_Interval);
					SendAnswer(timeout_matchphrase,index);
				}	
				else count++;
			},1000);

		}
		// Voting with expiration time
		else if (message[0]== "r" && message[1]== "." && question!=true){
			var parameters = message.slice(2).split("||");
			console.log(parameters);

			var starting_time = parameters[0];
			
			var regExp = /\[(.*?)\]/;
			var starting_time = regExp.exec(starting_time);
			if (starting_time != null){
				starting_time_seconds = parseInt(starting_time[1]);
			}
		
			var ending_time = parameters[1];
			//var ending_time = regExp.exec(ending_time);
			if (ending_time != null){
				//timeout_matchphrase = ending_time[1].split(",")[0];
				timeout_matchphrase = ending_time.split(",")[0];
				
				//var time = ending_time[1].split(",")[1].split(":");
				var time = ending_time.split(",")[1].split(":");
				var ending_time_seconds = 0;
				var multiplier= 1;
				for (var t=time.length-1; t>=0;t--){
					ending_time_seconds+=time[t]*multiplier;
					multiplier*=60;
				}
				console.log("Seconds: "+ ending_time_seconds);
				ending_time_seconds = ending_time_seconds+starting_time_seconds;
				
				console.log("Starting Time: "+ starting_time_seconds);
				
				console.log("Ending Time: "+ ending_time_seconds);

			}
			voting_period = ending_time_seconds-starting_time_seconds;
			console.log("Voting Time: "+ voting_period);

			var right_now  = Math.round(+new Date()/1000);
			var count  = right_now-starting_time_seconds;
			var remaining =ending_time_seconds-right_now;

			
			//if(true){
			if((remaining>0)&&(count<ending_time_seconds)){
				//Questions
				var number_of_questions = question_height*(parameters.length-1);
				$('#content-area').height(84-number_of_questions+"%");
				$('#send-message-area').height(number_of_questions+"%");
				$("#send-message-area").append('<div class="answer-divider"></div>');
				for(var j= 2; j < parameters.length; j++){
					var regExp = /\[(.*?)\]/;
					var names = regExp.exec(parameters[j]);
					if (names != null){
						parameters[j] = parameters[j].replace(/\[(.*?)\]/g, "");
						$("#send-message-area").append('<button class="answer" name="'+parameters[j].trim()+'" value="'+names[1]+'" onClick="clearInterval(progressbar_Interval);SendAnswer(\''+names[1].trim()+'\',\''+index+'\');" index="'+index+'">'+parameters[j].trim()+'</button>');
						$("#send-message-area").append('<div class="answer-divider"></div>');
					}
				}
				$("#send-message-area").append('<div id="progressbar"><div id="progressbar_text"></div><div id="bar"></div></div>');
				progressbar_Interval = setInterval(function() {			
					bar_width = (count*100)/voting_period;
					$("#bar").width(bar_width+"%");
					var seconds_remaining = voting_period-count; 
					var hours = Math.floor(seconds_remaining / (60 * 60));
					var divisor_for_minutes = seconds_remaining % (60 * 60);
					var minutes = Math.floor(divisor_for_minutes / 60);
					var divisor_for_seconds = divisor_for_minutes % 60;
					var secs = Math.ceil(divisor_for_seconds);
					if (hours < 10) {hours = "0"+hours;}
					if (minutes < 10) {minutes = "0"+minutes;}
					if (secs < 10) {secs = "0"+secs;}
					document.getElementById("progressbar_text").innerHTML = hours+":"+minutes+":"+secs;	
					if(count>=ending_time_seconds || bar_width>=100){
						clearInterval(progressbar_Interval);
						SendAnswer(timeout_matchphrase,index);
					}	
					else count++;
				},1000);
			}
			else{
				SendAnswer("",index);
				
			}
		}
		// Question with expiration date
		else if (message[0]== "e" && message[1]== "." && question!=true){
			
			var parameters = message.slice(2).split("||");
			var starting_time = parameters[0];
			
			var regExp = /\[(.*?)\]/;
			var starting_time = regExp.exec(starting_time);
			if (starting_time != null){
				starting_time_seconds = starting_time[1];
			}
			console.log(starting_time_seconds);

			var ending_time = parameters[1];
			//var ending_time = regExp.exec(ending_time);
			if (ending_time != null){
				//timeout_matchphrase = ending_time[1].split(",")[0];
				timeout_matchphrase = ending_time.split(",")[0];
				//ending_time_seconds = ending_time[1].split(",")[1];
				ending_time_seconds = ending_time.split(",")[1];
				
				console.log(ending_time_seconds.trim());
				console.log(Date.parse(ending_time_seconds.trim()));
				console.log(Date.parse(ending_time_seconds.trim()).getTime());
				ending_time_seconds = Date.parse(ending_time_seconds.trim()).getTime()/1000;
				

			}
			
			console.log(starting_time_seconds);
			console.log(ending_time_seconds);
			voting_period = ending_time_seconds-starting_time_seconds;
			var right_now  = Math.round(+new Date()/1000);
			var count  = right_now-starting_time_seconds;
			var remaining =ending_time_seconds-right_now;


			if((remaining>0)&&(count<ending_time_seconds)){
				//Questions
				var number_of_questions = question_height*(parameters.length-1);
				$('#content-area').height(84-number_of_questions+"%");
				$('#send-message-area').height(number_of_questions+"%");
				$("#send-message-area").append('<div class="answer-divider"></div>');
				for(var j= 2; j < parameters.length; j++){
					var regExp = /\[(.*?)\]/;
					var names = regExp.exec(parameters[j]);
					if (names != null){
						parameters[j] = parameters[j].replace(/\[(.*?)\]/g, "");
						$("#send-message-area").append('<button class="answer" name="'+parameters[j].trim()+'" value="'+names[1]+'" onClick="clearInterval(progressbar_Interval);SendAnswer(\''+names[1].trim()+'\',\''+index+'\');" index="'+index+'">'+parameters[j].trim()+'</button>');
						$("#send-message-area").append('<div class="answer-divider"></div>');
					}
				}
				$("#send-message-area").append('<div id="progressbar"><div id="progressbar_text"></div><div id="bar"></div></div>');
				progressbar_Interval = setInterval(function() {			
					bar_width = (count*100)/voting_period;
					$("#bar").width(bar_width+"%");
					var seconds_remaining = voting_period-count; 
					var hours = Math.floor(seconds_remaining / (60 * 60));
					var divisor_for_minutes = seconds_remaining % (60 * 60);
					var minutes = Math.floor(divisor_for_minutes / 60);
					var divisor_for_seconds = divisor_for_minutes % 60;
					var secs = Math.ceil(divisor_for_seconds);
					if (hours < 10) {hours = "0"+hours;}
					if (minutes < 10) {minutes = "0"+minutes;}
					if (secs < 10) {secs = "0"+secs;}
					document.getElementById("progressbar_text").innerHTML = hours+":"+minutes+":"+secs;	
					if(count>=ending_time_seconds || bar_width>=100){
						clearInterval(progressbar_Interval);
						SendAnswer(timeout_matchphrase,index);
					}	
					else count++;
				},1000);
			}
			else{
				//SendAnswer("",index);
			}
		}
		else{
			
			set_name(message); 
			//message = message.replace(/\[(.*?)\]/g, "");
			message = message.replace(/\[(.*?)\]/, "");
			
			var message_type = message[0];
			if(type!="Blog" || type!="Mail" || type !="media") message = message.slice(2);
			switch (type){
				//GoSocial - Fakebook //		
				case 'GoSocial':
					switch (message_type){
						//Post
						case "t":
						case "p":
						case "i":
							var html_message='<div class="gosocial_post"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><br/>';
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=message+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><a href="'+urls[1]+'" data-lightbox="'+urls[1]+'" data-title="'+message+'"><image src="'+urls[1]+'" style="vertical-align:middle"></a></div><br>';										
							}
							else html_message+=message+'</div></div>';
							$("#content-area").append(html_message);
							
							break;
						//Audio	
						case "a":
							var html_message='<div class="gosocial_post"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><br/>';
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=message+'<br><br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div></div>';										
							}
							else html_message+=message+'</div></div>';
							$("#content-area").append(html_message);
							break;	
						//Video
						case "v":							
							var html_message='<div class="gosocial_post"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><br/>';
							html_message+=video_container(message);
							html_message+='</div></div>';
							$("#content-area").append(html_message);
							break;	
							
						//Comment
						case "c":
							var comment = message.slice(2);							
							var regExp = /\[(.*?)\]/;
							var names = regExp.exec(comment);
							var name = "";
							if (names != null){
								comment =comment.replace(/\[(.*?)\]/g, "");
								if (names[1] ==  "You") {
									if (AUDIENCE_FIRST_NAME =='')
										var	name = 'You';
									else
										var	name = AUDIENCE_FIRST_NAME;
									IMAGE_SRC=PROFILE_IMAGE;
								}
								else{
									name=names[1];
								}
							}
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
					switch (message_type){
						//Tweet only text
						case "p":
						case "t":
						case "c":
							$("#content-area").prepend('<div class="tuit"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><span class="screenname"> @'+MESSAGE_NAME+'</span><div class="tuit_content">'+message+'</div></div>');
							break;
						//Tweet with image	
						case "i":
							var html_message = '<div class="tuit"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><span class="screenname"> @'+MESSAGE_NAME+'</span><div class="tuit_content">';
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
							var images = regExp.exec(message);
							if (images != null){
								message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');
								html_message+=message+'<br/><img src="'+images[1]+'"></div></div>';
							}
							else{ 
								html_message+=message+'</div></div>';
							}
							$("#content-area").prepend(html_message);

							break;
						//Audio	
						case "a":
							var html_message = '<div class="tuit"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><span class="screenname"> @'+MESSAGE_NAME+'</span><div class="tuit_content">';
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=message+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div></div>';										
							}
							else html_message+=message+'</div></div>';
							$("#content-area").prepend(html_message);
							break;	
						case "v":
							var html_message = '<div class="tuit"><img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><span>'+MESSAGE_NAME+'</span><span class="screenname"> @'+MESSAGE_NAME+'</span><div class="tuit_content">';
							html_message+=video_container(message);
							html_message+='</div></div>';
							$("#content-area").prepend(html_message);
							break;
						}
					break;

				//Msngr - WhatsUp //
				case "Msngr":
					switch (message_type){
						//Conversation
						case "p":
						case "t":
						case "c":							
							$("#content-area").append('<div class="message_wrapper" ><div class="message_content  '+MESSAGE_POSITION+'" ><img class="profile_photo" style="float:'+MESSAGE_POSITION+';" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><div class="message_'+MESSAGE_POSITION+'"><span>'+MESSAGE_NAME+'</span><br/>'+message+'</div></div></div>');
							break;		
						//Image	
						case "i":
							var html_message='<div class="message_wrapper"><div class="message_content  '+MESSAGE_POSITION+'" ><img class="profile_photo" style="float:'+MESSAGE_POSITION+';" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><div class="message_'+MESSAGE_POSITION+' image"><span>'+MESSAGE_NAME+'</span><br/>';
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=message+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><a href="'+urls[1]+'" data-lightbox="'+urls[1]+'" data-title="'+message+'"><image src="'+urls[1]+'" style="vertical-align:middle"></a></div><br>';										
							}
							else html_message+=message+'</div></div>';
							$("#content-area").append(html_message);
							break;
						//Audio	
						case "a":
							var html_message='<div class="message_wrapper"><div class="message_content '+MESSAGE_POSITION+'" ><img class="profile_photo" style="float:'+MESSAGE_POSITION+';" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><div class="message_'+MESSAGE_POSITION+' audio"><span>'+MESSAGE_NAME+'</span><br/>';
							var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
							var urls = regExp.exec(message);
							if (urls != null){
								message = message.replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '');							
								html_message+=message+'<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><audio controls style="max-width: 100%;"><source src="'+urls[1]+'"></audio></div><br>';										
							}
							else html_message+=audio+'</div></div>';
							$("#content-area").append(html_message);
							break;	
						//Video
						case "v":
							var html_message='<div class="message_wrapper"><div class="message_content '+MESSAGE_POSITION+'" ><img class="profile_photo" style="float:'+MESSAGE_POSITION+';" src="'+MESSAGE_PROFILE_IMAGE+'" onerror="if (this.src != \''+MESSAGE_DEFAULT_IMAGE+'\') this.src = \''+MESSAGE_DEFAULT_IMAGE+'\';"><div class="message_'+MESSAGE_POSITION+' video"><span>'+MESSAGE_NAME+'</span><br/>';
							html_message+=video_container(message);
							html_message+='</div></div>';
							$("#content-area").append(html_message);
							break;
						//Date	
						case "d":	
							var m_names = new Array("January", "February", "March", 
							"April", "May", "June", "July", "August", "September", 
							"October", "November", "December");
							var d = new Date();
							var curr_date = d.getDate();
							var curr_month = d.getMonth();
							var curr_year = d.getFullYear();
							today = curr_date + " " + m_names[curr_month] + " " + curr_year;
							message = message.replace("|today|", today);							

							$("#content-area").append('<div class="message_wrapper" style="font-weight:bold;text-align:center"><div class="date">'+message+'</div></div>');
							break;	
						}
						
					break;	
				case "Blog":
					var regExp = /\[(.*?)\]/;
					
						
					body = message.replace(/\<br\s*[\/]?\>/gi, "<br\>\n");
					body = body.replace(/\<\/span\>/gi, "\n");

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
							
					//html_tags+='<span>'+name+'</span><br/><div style="padding-left:45px">'+body[0]+'</div><div class="blog_body" '+display+'>';
					html_tags+='<span>'+MESSAGE_NAME+'</span><br/><div>'+body[0]+'</div><div class="blog_body" '+display+'>';
					var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
	
					for ( var j = 1; j<body.length; j++){
						var urls = regExp.exec(body[j].trim());

						var stripped_message = body[j].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");
						if (stripped_message[0]=='v' && stripped_message[1]=='.'){
							html_tags+=video_container(stripped_message.slice(2))+"<br>";					
						}
						else if(stripped_message[0]=='a' && stripped_message[1]=='.'){ 
							html_tags+='<br><div class="sound"><audio controls style="max-width: 100%;"><source src="'+urls[1].trim()+'" type="audio/mpeg"></audio></div>';
						}
						else if(stripped_message[0]=='i' && stripped_message[1]=='.'){ 
							html_tags+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+urls[1].trim()+'" style="vertical-align:middle"></div><br>';
						}
						else if( stripped_message[0]=='f' && stripped_message[1]=='.'){ 

							if (urls!=null){
								var extension = urls[1].substr(urls[1].length-3, 3);
								body[j] = body[j].replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+urls[1]+'">"'+urls[1]+'"</a>');							

									/*
									
									body[j] = body[j].replace("i.", '');
									//console.log(extension);
									
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
									*/
								
							}	
						}
						else html_tags+=body[j]+'<br>';	
					}
					html_tags+='</div></div>';
					$("#content-area").prepend(html_tags);
				
					break;				
				case "Mail":
					var regExp = /\[(.*?)\]/;		
					body = message.replace(/\<br\s*[\/]?\>/gi, "<br\>\n");
					body = body.replace(/\<\/span\>/gi, "\n");
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
					img.src = MESSAGE_PROFILE_IMAGE;
					//console.log(profile_image_path);
					//console.log(img.height);

					if (img.height != 0) html_tags+='<img class="profile_photo" src="'+MESSAGE_PROFILE_IMAGE+'"><span>'+MESSAGE_NAME+'</span><br><span class="screenname"> \<'+MESSAGE_NAME.toLowerCase()+'@mail.com\></span> <br/><div style="padding-left:45px">'+body[0]+'</div><div class="mail_body" '+display+'>';
					else{
						for (var i = 0, hash = 0; i < MESSAGE_NAME.length; hash = MESSAGE_NAME.charCodeAt(i++) + ((hash << 5) - hash));
						for (var i = 0, colour = "#"; i < 3; colour += ("00" + ((hash >> i++ * 8) & 0xFF).toString(16)).slice(-2));
						html_tags+='<div class="profile_photo" style="font-family:Roboto;position:relative;background-color:'+colour+'";><div style="color:white;position:absolute;left:0;right:0;top:0;bottom:0;margin:auto;width: 80%;height: 80%;font-size: 25px;text-align: center;font-weight:normal;">'+MESSAGE_NAME[0]+'</div></div><span>'+MESSAGE_NAME+'</span><br/><div style="padding-left:45px">'+MESSAGE_NAME+'</div><div class="mail_body" '+display+'>';
					}
					var regExp = /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g;
	
					for ( var j = 1; j<body.length; j++){
						var urls = regExp.exec(body[j].trim());

						var stripped_message = body[j].replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/g,"");
						if (stripped_message[0]=='v' && stripped_message[1]=='.'){
							html_tags+=video_container(stripped_message.slice(2))+"<br>";					
						}
						else if(stripped_message[0]=='a' && stripped_message[1]=='.'){ 
							html_tags+='<br><div class="sound"><audio controls style="max-width: 100%;"><source src="'+urls[1].trim()+'" type="audio/mpeg"></audio></div>';
						}
						else if(stripped_message[0]=='i' && stripped_message[1]=='.'){ 
							html_tags+='<br><div style="font-weight:bold;text-align:center;margin-top:10px;position:relative;"><image src="'+urls[1].trim()+'" style="vertical-align:middle"></div><br>';
						}
						else if( stripped_message[0]=='f' && stripped_message[1]=='.'){ 

							if (urls!=null){
								var extension = urls[1].substr(urls[1].length-3, 3);
								body[j] = body[j].replace(/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/g, '<a href="'+urls[1]+'">"'+urls[1]+'"</a>');							

									/*
									
									body[j] = body[j].replace("i.", '');
									//console.log(extension);
									
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
									*/
								
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
						video = video+"?showinfo=0&controls=0&autohide=1&rel=0&modestbranding=0";
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
		/*
		if (messages.length==0){
			clearInterval(unfolding_interval);
			still_printing=false;
			if(delay==0&&type!="Mail"&& type!="Blog" && type!="Microblog"){
				$("#content-area").animate({scrollTop: $('#content-area').prop("scrollHeight")}, 500);
			}
			$('#send-message-area').show();
		}
		*/
	},delay);	
}
function select(id,index){
	message_feed_id = id;
	selected = true;

	$('#content-area').empty();
	$('#send-message-area').empty();
	spinner.spin(target);	

	$.ajax({
		type: "GET",
		url: "api.php",
		data: {  
			'action': 'select_messages',
			'message_feed_id': message_feed_id
		},
		dataType: "json",
		success: function(data){
			console.log(data);
			messages_to_array(data);
			if(feeds_array[index]!==undefined){
				$("#content-area").append('<div id="selected_feed" ><img class="profile_photo" style="float:left;" src="styles/'+PROJECT_ID+'/images/'+feeds_array[index].character_name.toLowerCase()+'.png" onerror="if (this.src != \'styles/'+PROJECT_ID+'/images/default.png\') this.src = \'styles/'+PROJECT_ID+'/images/default.png\';"><div class="feed_name" >'+feeds_array[index].character_name+'</div><div class="feed_tagline">Available</div></div></div>');
				CHARACTER_NAME=feeds_array[index].character_name.toLowerCase();
			}
			else CHARACTER_NAME="";
			Array_print(messages_array,0);
			spinner.stop();
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
	waitingForAnswer=false;
	still_printing=false;
	$("#content-area").empty();
	$("#send-message-area").empty();
	$("#content-area").height('84%');
	$("#send-message-area").height('1%');
	spinner.spin(target);	
	
	if (index==-1 || feeds_array.length==1){
		spinner.stop();
		window.location.href = 'desktop.php';
	}
	else{
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
				spinner.stop();
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
			$("#mail-area").animate({scrollTop: 0}, 300);
			$(element).find('.mail_body').hide("slow");
			$(element).find('.minimize_icon').find('img').attr("src", "images/maximize.png");
		}
		else {
			$(".mail").find('.mail_body').hide();
			$(".mail").find('.minimize_icon').find('img').attr("src", "images/maximize.png");

			$(element).find('.minimize_icon').find('img').attr("src", "images/minimize.png");

			$(element).find('.mail_body').show(200, function() {
				$("#content-area").animate({scrollTop: $(element).offset().top-57}, 200);
			});
			var audio = $("#sound3")[0];
			audio.play();
		}
		
		$(element).removeClass('bold');	
	}
	else if (type=="Blog"){
		if( $(element).find('.blog_body').is(':visible') ) {
			$("#blog-area").animate({scrollTop: 0}, 300);
			$(element).find('.blog_body').hide("slow");
			$(element).find('.minimize_icon').find('img').attr("src", "images/maximize.png");
		}
		else {
			$(".blog").find('.blog_body').hide();
			$(".blog").find('.minimize_icon').find('img').attr("src", "images/maximize.png");

			$(element).find('.minimize_icon').find('img').attr("src", "images/minimize.png");

			$(element).find('.blog_body').show(200, function() {
				$("#content-area").animate({scrollTop: $(element).offset().top-57}, 200);
			});
			var audio = $("#sound3")[0];
			audio.play();
		}
		
		$(element).removeClass('bold');	
	
	}
}
function video_container(url){
	var regExp_Youtubevideo = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g;
	var youtube_videos = regExp_Youtubevideo.exec(url);
	var regExp_VimeoVideo = /(?:https?:\/\/)?(?:www\.)?vimeo.com\/(\d+)($|\/)/;
	var vimeo_videos = regExp_VimeoVideo.exec(url);

	if (youtube_videos != null){
		var message = url.replace(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/g, '');							

		var html_video = "https://www.youtube.com/embed/"+youtube_videos[1];
		html_video = html_video+"?showinfo=0&controls=0&autohide=1&rel=0&modestbranding=0";
		
		if(type!="GoSocial") return message+'<br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+html_video+'"  frameborder="0" ></iframe></div>';										
		else return message+'<br><br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+html_video+'"  frameborder="0" ></iframe></div>';										

	}
	else if(vimeo_videos!=null){
		var message = url.replace(/(?:https?:\/\/)?(?:www\.)?vimeo.com\/(\d+)($|\/)/,'');
		var html_video = "https://player.vimeo.com/video/"+vimeo_videos[1];
		if(type!="GoSocial") return message+'<br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+html_video+'"  frameborder="0" ></iframe></div>';										
		else return message+'<br><br><div class="video_container"><iframe style="padding-top:4px;" width="100%" height="100%" src="'+html_video+'"  frameborder="0" ></iframe></div>';										
	}
	else return url;
}

function set_name(message){
	var regExp = /\[(.*?)\]/;
	var names = regExp.exec(message);
	if (names != null){
		var name = names[1].charAt(0).toUpperCase() + names[1].slice(1);
	}
	else var name = "";	
	
	if (name ==  "You") {
		MESSAGE_POSITION = 'right';
		MESSAGE_PROFILE_IMAGE = 'styles/'+PROJECT_ID+'/'+PROFILE_IMAGE;
		MESSAGE_DEFAULT_IMAGE = 'styles/'+PROJECT_ID+'/profiles/default.png';

		if (AUDIENCE_FIRST_NAME =='')
			MESSAGE_NAME = 'You';
		else
			MESSAGE_NAME = AUDIENCE_FIRST_NAME;
	}
	else if (name ==  "") {
		MESSAGE_POSITION = 'left';
		MESSAGE_PROFILE_IMAGE = '';
		MESSAGE_DEFAULT_IMAGE = 'styles/'+PROJECT_ID+'/images/default.png';

		MESSAGE_NAME = name;
	}
	else{
		MESSAGE_POSITION = 'left';
		MESSAGE_PROFILE_IMAGE = 'styles/'+PROJECT_ID+'/images/'+name.toLowerCase()+'.png';
		MESSAGE_DEFAULT_IMAGE = 'styles/'+PROJECT_ID+'/images/default.png';
		
		MESSAGE_NAME = name;
	}
}
