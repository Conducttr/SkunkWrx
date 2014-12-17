<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>WhatsUp</title>
    <link rel="shortcut icon" href="images/favicon.ico">
	<link rel="stylesheet" href="chatsfield.css" type="text/css" />
	<link href="images/favicon.png" rel="apple-touch-icon" />

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript" src="chat.js"></script>
	<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700|Playfair+Display:400,700,700italic,400italic" rel="stylesheet" type="text/css">
    <script type="text/javascript">

        // ask user for name with popup prompt    
        var name = prompt("Enter your chat name:", "Guest");
        
        // default name is 'Guest'
    	if (!name || name === ' ') {
    	   name = "Guest";	
    	}
    	
    	// strip tags
    	name = name.replace(/(<([^>]+)>)/ig,"");
    	
    	// display name on page
    	$("#name-area").html("You are: <span>" + name + "</span>");
    	AUDIENCE_PHONE = '<?php echo $_REQUEST['audience_phone'];?>';
		var chat =  new Chat();

		setInterval("chat.update("+AUDIENCE_PHONE+")", 1000);

    	
    	
    	// kick off chat
		
		$(document).ready(function(){
			chat.read_previous(AUDIENCE_PHONE);
			chat.getState(AUDIENCE_PHONE); 
    	
    		 // watch textarea for key presses
			$("#sendie").keydown(function(event) {    
				 console.log("keydown");					 

                 var key = event.which;  
                 //all keys including return.  
                 if (key >= 33) {
                     var maxLength = $(this).attr("maxlength");  
                     var length = this.value.length;  
                     // don't allow new content if length is maxed out
                     if (length >= maxLength) {  
                         event.preventDefault();  
                     }  
                  }  
			});
			 // watch textarea for release of key press
			 $('#sendie').keyup(function(e) {
				 console.log("keyup");					 
				  if (e.keyCode == 13) { 	  
					var text = $(this).val();
					var maxLength = $(this).attr("maxlength");  
					var length = text.length; 
					 
					// send 
					if (length <= maxLength + 1) { 
					 
						chat.send(AUDIENCE_PHONE,text, name);	
						$(this).val("");
						
					} else {
						$(this).val(text.substring(0, maxLength));	
					}	
				  }
			 });
    	});

	</script>
</head>

<body>
	<div id="chat-wrapper">
		<div id="chat-header"> 
			<img src='images/logo_chats.png' style="width: 30%;" >
		</div>
		<div id="chat-area"></div>
		<div id="send-message-area">
			<textarea id="sendie" maxlength = '140' ></textarea>
		</div>
	</div>
</body>
</html>
