<?php
// Start the session
session_start();
?>

<html lang="en">
<head>
  <title>IntroSpection</title>
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
 <h2>Database backup successfully created and uploaded in s3 bucket</h2>
 
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

$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
$dbbackup = uniqid("dbbackup-",false);
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
#$dbbucket = uniqid("php-sb-dbbucket#",false);
$dbbucket = 'Database-Bucket';
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
?>

<h5>
<?php
//url
echo "Here is the link for database backup:<br/>\n";
?>
</h5>

<?php
$url = $result['ObjectURL'];
echo $url;
?>
<br> <br />
<form action="index.php">
   	<br /><input type="submit" value="Add more Pictures here:">
</form>

<!--<h4><a href="index.php">Add more Pictures here: </a></h4-->
<iframe width="40%" height="500px" src="index.php" name="Form WD40"></iframe>

</div>
</body>
</html>

<!--source: http://www.w3schools.com/html/html_classes.asp-->
