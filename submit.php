<?php
// Start the session
session_start();
?>

<html lang="en">
<head>
  <title>Submit.php</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>

<body style=background-image:url(https://wallpaperscraft.com/image/rain_tree_streams_bad_weather_precipitation_green_despondency_inclination_62354_1920x1080.jpg)>
<div class="container-fluid">
  <h3>Your EMail ID</h3>

<?php
require 'vendor/autoload.php';
#useremail = $_POST["useremail"];
#echo $useremail;
echo $_POST['email'];
	
$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

echo '<pre>';
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
} else {
    echo "Possible file upload attack!\n";
}
echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>"

use Aws\S3\S3Client;
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

// Fixed bucket name
#$bucket = uniqid("php-sb-",false);
$bucket = 'php-sb';

# AWS PHP SDK version 3 create bucket
$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $bucket
]);

$s3->waitUntil('BucketExists', array( 'Bucket'=> $bucket));

// PHP version 3 for putting object in s3
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
    'Key' => $uploadfile,
    #'ContentType' => $_FILES['userfile']['tmp_name'],
    'SourceFile' => $uploadfile
]); 

//Object URL
$url = $result['ObjectURL'];
echo $url;

//Image Magick
print "Imagick starting<br />";
$imagemagick = new Imagick($uploadfile);

// Providing 0 forces thumbnailImage to maintain aspect ratio
$imagemagick->thumbnailImage(800,600);
#$image->setImageFormat ("png");
#$imagemagick->writeImage('images/out.png',false);
$imagemagick->writeImage($uploadfile);

//fixed bucket name
$imagickbucket = 'php-imagick-';

// create bucket for rendered images

$result = $s3->createBucket([
    'ACL' => 'public-read',
    'Bucket' => $imagickbucket
]);

// Put rendered objects in s3
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $imagickbucket,
    'Key' => $uploadfile,
    #'ContentType' => $_FILES['userfile']['tmp_name'],
    'SourceFile' => $uploadfile
]);

//finished s3 url
$imagickurl = $result['ObjectURL'];
echo $imagickurl;

//Relational Database Connection
$rds = new Aws\Rds\RdsClient([
        'version' => 'latest',
        'region'  => 'us-east-1'
]);

$result = $rds->describeDBInstances([
        'DBInstanceIdentifier' => 'mp1-sb',
]);

$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
print "============<br />" . $endpoint . "================<br />";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link));

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// Prepared statement, stage 1: prepare
if (!($stmt = $link->prepare("INSERT INTO items (id,uname,email,phone,s3rawurl,s3finishedurl,filename,status) VALUES (NULL,?,?,?,?,?,?,?)"))) 
    {
         echo "Prepare failed: (" . $link->errno . ") " . $link->error;
    }

$email = $_POST['useremail'];
$uname = $_POST['uname'];
$phone = $_POST['phone'];
$s3rawurl = $url; //  $result['ObjectURL']; from above
$filename = basename($_FILES['userfile']['name']);
$s3finishedurl = $imagickurl;
$status =0;

$stmt->bind_param("ssssssi",$uname,$email,$phone,$s3rawurl,$s3finishedurl,$filename,$status); // 6 strings & 1 integer ssssssi

print "bind statement was sucessfull<br /";

$stmt->bind_param("sssssii",$uname,$email,$phone,$s3rawurl,$s3finishedurl,$jpgfilename,$state);
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
printf("%d Row inserted.\n", $stmt->affected_rows);

// explicit close recommended
$stmt->close();
$link->real_query("SELECT * FROM items WHERE email = '$email'");
//$link->real_query("SELECT * FROM items");
$res = $link->use_result();
echo "Result set order...\n";
while ($row = $res->fetch_assoc()) {
	echo $row['id'] . "Email: " . $row['email'];
	echo "<img src =\" " . $row['s3rawurl'] . "\" /><img src =\"" .$row['s3finishedurl'] . "\"/>";
}
$link->close();

//CREATE SNS TOPIC
use Aws\Sns\SnsClient;
$sns = new Aws\Sns\SnsClient
([
	'version' => 'latest',
        'region' => 'us-east-1'
]);

$result = $sns->createTopic([
	'Name' => 'mp2web', //Required
]);

echo $result;


//DISPLAY NAME ATTRIBUTES

$ARN = $result['TopicArn'];
$result = $sns->setTopicAttributes
([
    'AttributeName' => 'DisplayName', // REQUIRED
    'AttributeValue' => 'mp2web',
    'TopicArn' => $ARN, // REQUIRED
]);

//SUBSCRIBE

$result = $sns->subscribe
([
    'Endpoint' => $phone,
    'Protocol' => 'sms', // REQUIRED
    'TopicArn' => $ARN, // REQUIRED
]);

//WAIT FOR PENDING SUBSCRIPTION - SLEEP FOR 30 SECONDS
echo "Wait 30 seconds for Pending Confirmation";
sleep(30);

//PUBLISH
$result = $sns->publish
([
    	'Message' => 'Congratulations!! You sucessfully subscribed.', // REQUIRED
	'Subject' => 'Pictured Uploaded in S3 bucket',    
	'TopicArn' => $ARN,
]);


$link->close();
header('Location: gallery.php');

?>
</div>
</body>
</html>
