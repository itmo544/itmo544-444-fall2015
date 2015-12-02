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
<body style=background-image:url(https://wallpaperscraft.com/image/rain_tree_streams_bad_weather_precipitation_green_despondency_inclination_62354_1920x1080.jpg)>

<div class="container-fluid">
 <h1>Database backup successfully created and uploaded in s3 bucket</h1>
 
<?php
require 'vendor/autoload.php';

//variable for db connection
$dbuser = 'controller';
$dbpass = 'letmein888';
$dbname = 'customerrecords';

$rds = new Aws\Rds\RdsClient([
        'version' => 'latest',
        'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances([
        'DBInstanceIdentifier' => 'mp1-sb',
]);

#print "==Here is Endpoint==";
$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
#print "\n============\n" . $endpoint . "\n================\n";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link)); 

//s3 - call variables

$uploaddir = '/tmp/DBbackup';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
$dbbackup = uniqid("php-dbbackup-",false);
$dbpath=$uploaddir.$dbbackup. '.' . 'sql';
$sqlcon="mysqldump --user=$dbuser --password=$dbpass --host=$endpoint $dbname > $dbpath";
exec($sqlcon);

#print_r($_FILES);
#print "==Successfully connected to databases, now creating S3==";

$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

# AWS PHP SDK version 3 create bucket
$dbbucket = uniqid("php-sb-dbbucket",false);
$result = $s3->createBucket
    ([
        'ACL' => 'public-read',
        'Bucket' => $dbbucket
    ]);

#print "==Successfully created S3, now putting objects in it==";

// PHP version 3 for putting object in s3
$result = $s3->putObject([
        'ACL' => 'public-read',
        'Bucket' => $dbbucket,
        'Key' => $dbpath,
        'SourceFile' => $dbpath
]); 

//url
$url = $result['ObjectURL'];
print "\nHere is the link for database backup<br />";
echo $url;
?>
	
<h5><li><a href="index.php">Click Here to go back to previus page</a></li></h5>
</div>
</body>
</html>

<!--code source http://www.w3schools.com/bootstrap/bootstrap_get_started.asp-->
