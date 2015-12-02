<?php 
session_start(); 
#html code source: http://www.w3schools.com/bootstrap/bootstrap_get_started.asp
?>

<html lang="en">
<head>
  <title>ITMO544-444 Final Project</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</head>

<body style=background-image:url(https://wallpaperscraft.com/image/rain_tree_streams_bad_weather_precipitation_green_despondency_inclination_62354_1920x1080.jpg)>
<div class="container-fluid">
  <h1>Form WD40</h1>
  <p>All Fields Required</p>

<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype="multipart/form-data" action="submit.php" method="POST">
   
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />

		<!-- Name of input element determines name in $_FILES array -->
		Name: <input type="text" name="uname"><br />		
		Email Address: <input type="email" name="useremail"><br />
		Phone Number (16309995555): <input type="phone" name="phone"><br />
		Browse File: <input name="userfile" type="file" /><br />
		<input type="submit" value="Send File" />
	</form>
	<hr />

<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype="multipart/form-data" action="gallery.php" method="POST">
    
	Enter Email of user for gallery to browse: <input type="email" name="email">
	<input type="submit" value="Load Gallery" />
	</form>

	<h4><li><a href="introspection.php">Click Here to Backup entire database in S3 Bucket</a></li></h4>

</div>
</body>
</html>















