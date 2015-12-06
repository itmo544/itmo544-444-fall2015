<?php 
session_start(); 
#html code source: http://www.w3schools.com/bootstrap/bootstrap_get_started.asp
?>

<html lang="en">
<head>
  <title>ITMO544-444 Final Project</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
div.custom {
    background-color:black;
    color:skyblue;
    margin:20px;
    padding:20px;
}	
</style>

</head>

<body>
<div class="custom">
  <h1>Form WD40</h1>
  <p>All Fields are Required</p>

<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype="multipart/form-data" action="submit.php" method="POST">
   
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />

		<!-- Name of input element determines name in $_FILES array -->
		Name: <br /><input type="text" name="uname"><br />		
		Email Address:<br /><input type="email" name="useremail"><br />
		Phone Number (16309995555): <br /><input type="phone" name="phone"><br />
		Browse File: <br /><input name="userfile" type="file" /><br />
		<input type="submit" value="Send File" />
	</form>
	<hr />

<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype="multipart/form-data" action="gallery.php" method="POST">
    
	Enter Email of user for gallery to browse: <br /><input type="email" name="email"><br />
	<input type="submit" value="Load Gallery" />
	</form>

	<form action="introspection.php">
    		<input type="submit" value="Backup Database into S3 Bucket">
	</form><br />

<!--<h4><a href="introspection.php">Click Here to Backup entire database in S3 Bucket</a></h4-->

</div>
</body>
</html>















