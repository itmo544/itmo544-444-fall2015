<html lang="en">
<head>
  <title>Gallery</title>
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
	<h1>Welcome to the Gallery</h1>

<?php
session_start();
require 'vendor/autoload.php';

use Aws\Rds\RdsClient;
$rds = new Aws\Rds\RdsClient([
        'version' => 'latest',
        'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances([
        'DBInstanceIdentifier' => 'mp1-sb',
]);
$endpoint = "";
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
#print "\n============\n" . $endpoint . "\n================\n";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords") or die("Error " . mysqli_error($link));

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

//below line is unsafe - $email is not checked for SQL injection -- don't do this in real life or use an ORM instead
if (!empty($_POST['email'])){
$email = $_POST["email"];
$link->real_query("SELECT * FROM items WHERE email='".$email."'");
}else{
$link->real_query("SELECT * FROM items");
}
$res = $link->use_result();
while ($row = $res->fetch_assoc()) {
	echo "<br/>\n" . "Your ID # " . $row['id'] . "<br/>\n" . "Email: " . $row['email'];
	echo "<br/>\n" . "Thumbnail: " . "<br/>\n" . "<img src =\" " . $row['s3finishedurl'] . "\"/>";		
	echo "<br/>\n" . "Raw Image: " . "<br/>\n" . "<img src =\" " . $row['s3rawurl'] . "\"/>";
	
}

$link->close();
?>                      

</div>
</body>
</html>

<!--source: http://www.w3schools.com/html/html_classes.asp-->

