<?php session_start(); ?>
<html>
	<head><title>Hello app</title>
	</head>

<body>
<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype="multipart/form-data" action="submit.php" method="POST">
   
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />

		<!-- Name of input element determines name in $_FILES array -->
		Add JPG File: <input name="userfile" type="file" /><br />
		
		Name: <input type="text" name="username"><br />

		Email Address: <input type="email" name="useremail"><br />

		Phone Number (1-XXX-XXX-XXXX): <input type="phone" name="userphone">
			
		<input type="submit" value="Send File" />
	</form>
	<hr />

<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype="multipart/form-data" action="gallery.php" method="POST">
    
	Enter Email of user for gallery to browse: <input type="email" name="email">
	<input type="submit" value="Load Gallery" />
	</form>
</body>
</html>


















