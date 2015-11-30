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

//variable for db connection
$dbuser = 'controller';
$dbpass = 'letmein888';
$dbname = 'customerrecords';

use Aws\Rds\RdsClient;
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


//s3 - call variables

$uploaddir = '/tmp/DBbackup';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
$dbbackup = uniqid("php-sb-dbbackup",false);
$dbpath = $uploaddir.dbbucket. '.' . sql;
$sqlcon = "mysqldump --user=$dbuser --password=$dbpass --host=$endpoint $dbname > $dbpath";
exec($sqlcon);

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

// PHP version 3 for putting object in s3
$result = $s3->putObject([
        'ACL' => 'public-read',
        'Bucket' => $dbbucket,
        'Key' => $dbpath,
        'SourceFile' => $dbpath
]); 

//url
$url = $result['ObjectURL'];
?>
</div>
</body>
</html>

<!--The above html code copied from http://www.w3schools.com/bootstrap/bootstrap_get_started.asp-->
