<?php
// Start the session
session_start();
?>

<html lang="en">
<head>
  <title>IntroSpection</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container-fluid">
 <h1>Welcome to IntroSpection Page</h1>
 <h4><li><a href="index.php">Form WD40</a></li></h4>

<?php
require 'vendor/autoload.php';

$rdsrr = new Aws\Rds\RdsClient
([
   	'version' => 'latest',
   	'region'  => 'us-east-1'
]);

// describe instances
$result = $rds->describeDBInstances
([
	'DBInstanceIdentifier' => 'mp1-sb',
]);


$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
#print "\n================\n" . $endpoint . "\n================\n";


//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link)); 


//s3 - call variables from submit.php

$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
$dbbucket = uniqid("php-sb-dbbucket",false);

echo '<pre>';
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
    {
        echo "File is valid, and was successfully uploaded.\n";
    } 
    else 
    {
        echo "Possible file upload attack!\n";
    }

echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>";














?>



</div>
</body>
</html>

<!--The above html code copied from http://www.w3schools.com/bootstrap/bootstrap_get_started.asp-->
