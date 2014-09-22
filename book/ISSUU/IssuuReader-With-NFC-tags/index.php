<!--

London Voodo Demo - An Interactive Book hosted in ISSUU unlocked by NFC tags using Conducttr

-> This codes allows  to lock or unlock different pages in the book based on NFC tags readed by an Android app


Conducttr - 2014
www.conducttr.com
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
		<script type="text/javascript" src="//e.issuu.com/embed.js" async="true"></script>
		<script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="swfobject/swfobject.js"></script>
        <link rel="stylesheet" href="voodoo.css" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
        <title>London Voodoo - Interactive book using Conducttr</title>
    </head>

    <body style="overflow:hidden">
		<div id='container'>
			<div id='issuu_reader' class="issuuembed"></div>
			<?php 
				if (!isset($_REQUEST['audience_phone'])) echo "<span id='under_book'>This book contains locked content. Use the app and the NFC tags to unlock it. Click <a href='login.html'> here </a> to unlock now</span>";
				else {
					echo "<div id='under_book'> 
						Place your phone on the NFC tag <br>
						Or text #card to YOUR-NUMBER-HERE
					</div>";
				}
			?>
		</div>
		<audio id="music-1" loop="loop" autoplay="autoplay" preload="auto" autobuffer> 
			<source src="" type='audio/mpeg'/>
			IE doesn't support anything! 
		</audio>
		<script type="text/javascript" charset="utf-8">
			
			/* Conducttr api.php file path  - Edit this if you change the path of the api.php file */ 
			var API_URL = "api.php";

			var TELL_CONDUCTTR_IM_HERE = [];	
			var ISSUU_BOOK;
			var BOOK_JOURNEY = [];
			var ISSUU_READER_HEIGHT;
			var ISSUU_READER_WIDTH;	
			var NEXT_PAGE;
			var AUDIENCE_PHONE = '<?php echo $_REQUEST['audience_phone']; ?>';
			console.log("AUDIENCE_PHONE: " + AUDIENCE_PHONE);
			
			setup();		
			
			window.onIssuuReadersLoaded = function() {
				IssuuReaders.get(ISSUU_BOOK).addEventListener("change", "viewer2ChangeHandler");
				if(AUDIENCE_PHONE!=""){ 
					conducttr_call ("reset");
				}

			};
			
			if(AUDIENCE_PHONE!=""){ 
				//Server side event - Waiting for Conducttr call (the Audience has touched a NFC tag)
				var sse = new EventSource('api.php?action=reading&audience_phone='+AUDIENCE_PHONE);
				console.log("Waiting for the server...");
				sse.onmessage = function(e) {		
					console.log(e.data);
					var data = JSON.parse(e.data);
					if (data.book_journey != 0){
						 BOOK_JOURNEY =  JSON.parse(data.book_journey);
						 if (BOOK_JOURNEY.length==0) {
							$('#under_book').text("The book is now unlocked");		
							var audio_1 = document.getElementById('music-1');
							audio_1.src= "audio/voodoo_audio.mp3";
						 }
						 console.log("New Book Journey: "+ BOOK_JOURNEY);
					 }
					if (data.nextpage != 0){
						 IssuuReaders.get(ISSUU_BOOK).setPageNumber(data.nextpage);
						 console.log("Going to page: "+ data.nextpage);
					}
				}
			}
			var lastpage = -1;
			var viewer2ChangeHandler = function() {
				var viewer = IssuuReaders.get(ISSUU_BOOK);
				var pageNumber = viewer.getPageNumber();
				
				if (AUDIENCE_PHONE!=""){
					for(var i = 0; i < TELL_CONDUCTTR_IM_HERE.length; i++) {
						if (TELL_CONDUCTTR_IM_HERE[i][0] == pageNumber){
							console.log("Calling Conducttr - Page: " + TELL_CONDUCTTR_IM_HERE[i][0] + " - matchphrase: " + TELL_CONDUCTTR_IM_HERE[i][1]);
							conducttr_call (TELL_CONDUCTTR_IM_HERE[i][1]);
						}
					}
				}
				console.log("Last page: "+lastpage + " - Current page: "+pageNumber);
				for(var i = 0; i < BOOK_JOURNEY.length; i++) {
					console.log("Checking journey: " + BOOK_JOURNEY[i][0]);
					//NextPage
					if ((pageNumber > BOOK_JOURNEY[i][0]) && (pageNumber < BOOK_JOURNEY[i][1])){
						if (pageNumber > lastpage){
							IssuuReaders.get(ISSUU_BOOK).setPageNumber(BOOK_JOURNEY[i][1]);
							lastpage=BOOK_JOURNEY[i][1];
						}
						else{
							console.log("Going from previous page : " + BOOK_JOURNEY[i][1] + " - to: " + BOOK_JOURNEY[i][0]);
							IssuuReaders.get(ISSUU_BOOK).setPageNumber(BOOK_JOURNEY[i][0]);
							lastpage=BOOK_JOURNEY[i][0];
						}
					}
				}
			};
	
			function conducttr_call(matchphrase){
				$.ajax({  
					type: "GET",  
					url: API_URL,
					data: {'action':"oauth_call", "method" : "GET", "matchphrase" : matchphrase , 'audience_phone': AUDIENCE_PHONE},
					dataType: "json"
				});  	 
			};
			function setup(){
				$.ajax({  
					type: "GET",  
					url: API_URL,
					data: {'action':"get_setup"},
					async: false,
					dataType: "json",
					success: function (result) {
						//console.log(result);
						ISSUU_BOOK = result.issuu_book;
						TELL_CONDUCTTR_IM_HERE = result.tell_conducttr_im_here;
						BOOK_JOURNEY = result.book_journey;
						ISSUU_READER_WIDTH = result.reader_width;
						ISSUU_READER_HEIGHT = result.reader_height;
						var myreader = document.getElementById('issuu_reader');
						
						myreader.setAttribute('data-configid', ISSUU_BOOK);
						document.getElementById('issuu_reader').style.width = ISSUU_READER_WIDTH+"px";
						document.getElementById('issuu_reader').style.height = ISSUU_READER_HEIGHT+"px";

					}
				}); 
			};
		</script>
    </body>
</html>
