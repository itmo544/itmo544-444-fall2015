<html lang="en">
<head>
  <title>Gallery</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body style=background-image:url(https://wallpaperscraft.com/image/rain_tree_streams_bad_weather_precipitation_green_despondency_inclination_62354_1920x1080.jpg)>

<div class="container-fluid">
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
?>
<h4>
<?php
while ($row = $res->fetch_assoc()) {
        echo "<br/>\n" . "Your ID # " . $row['id'] . "<br/>\n" . "Email: " . $row['email'];
        echo "<br/>\n" . "Your Pictures: " . "<br/>\n" . "<img src =\" " . $row['s3rawurl'] . "<br/>\n" . "\" /><img src =\"" .$row['s3finishedurl'] . "\"/>" . "<br/>\n";
}

$link->close();
?>                      
</h4>

/*
// old code
$link->real_query("SELECT * FROM items WHERE email = '$email'");
//$link->real_query("SELECT * FROM items");
$res = $link->use_result();
#echo "Result set order...\n";

while ($row = $res->fetch_assoc()) {
	echo "Your ID # " . $row['id'] . "<br/>\n" . "Email: " . $row['email'];
	echo "Your Pictures: " . "<br/>\n" . "<img src =\" " . $row['s3rawurl'] . "<br/>\n" . "\" /><img src =\"" .$row['s3finishedurl'] . "\"/>";
}
$link->close();
?>
*/
	
</div>
</body>
</html>

<!--The above html code borrowed from http://www.w3schools.com/bootstrap/bootstrap_get_started.asp-->

