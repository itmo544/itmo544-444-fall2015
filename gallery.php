<html lang="en">
<head>
  <title>Gallery</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container-fluid">
 <h1>Welcome to the Gallery</h1>

<?php
session_start();
$email = $_POST["email"];
echo $email;
require 'vendor/autoload.php';

use Aws\Rds\RdsClient;
$client = RdsClient::factory(array(
	'version' => 'latest',
	'region' => 'us-east-1'
));

$result = $client->describeDBInstances(array(
    'DBInstanceIdentifier' => 'mp1-sb',
));

$endpoint = "";

#foreach ($result->getPath('DBInstances/*/Endpoint/Address') as $ep) {
#    // Do something with the message
#    echo "============". $ep . "================";
#    $endpoint = $ep;
#}   

$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link));

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

//below line is unsafe - $email is not checked for SQL injection -- don't do this in real life or use an ORM instead
$link->real_query("SELECT * FROM items WHERE email = '$email'");
//$link->real_query("SELECT * FROM items");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
	echo $row['id'] . "Email: " . $row['email'];
	echo "<img src =\" " . $row['s3rawurl'] . "\" /><img src =\"" .$row['s3finishedurl'] . "\"/>";
}
$link->close();
?>

</div>
</body>
</html>

<!--The above html code borrowed from http://www.w3schools.com/bootstrap/bootstrap_get_started.asp-->

