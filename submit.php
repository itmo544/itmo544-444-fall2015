<?php
// Start the session
session_start();
?>

<html lang="en">
<head>
  <title>Submit.php</title>
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
	<h2>Congratulations!!!</h2>


<?php
echo $_POST['email'];
	
$uploaddir = '/tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
 
echo '<pre>';
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
		echo "File is valid, and was successfully uploaded.\n";
	} 
	else 
	{
		echo "Possible file upload attack!\n";
	}
print "</pre>";

require 'vendor/autoload.php';
 
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

#$s3->waitUntil('BucketExists', array( 'Bucket' => $bucket));

// PHP version 3 for putting object in s3
$result = $s3->putObject([
    'ACL' => 'public-read',
    'Bucket' => $bucket,
    'Key' => $uploadfile,
    'ContentType' => $_FILES['userfile']['tmp_name'],
    'SourceFile' => $uploadfile,
	'Body' => fopen($uploadfile, 'r+')
]); 

//Object URL
$url = $result['ObjectURL'];

//Image Magick
$imagemagick = new Imagick($uploadfile);

// Providing 0 forces thumbnailImage to maintain aspect ratio
$imagemagick->thumbnailImage(200,200);
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
    'ContentType' => $_FILES['userfile']['tmp_name'],
    'SourceFile' => $uploadfile,
    'Body' => fopen($uploadfile, 'r+')
]);

//finished s3 url
$imagickurl = $result['ObjectURL'];

//Relational Database Connection
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

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link));

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// Prepared statement, stage 1: prepare
if (!($stmt = $link->prepare("INSERT INTO items (id,uname,email,phone,s3rawurl,s3finishedurl,filename,status) VALUES (NULL,?,?,?,?,?,?,?)"))) {
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

if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}

printf("%d Row sucessfully inserted into database.\n", $stmt->affected_rows);

/* explicit close recommended */
$stmt->close();
$link->real_query("SELECT * FROM items WHERE email='".$email."'");
$res = $link->use_result();
?>

<h4>
<?php
while ($row = $res->fetch_assoc()) {
	echo "<br/>\n" . "Your ID # " . $row['id'] . "<br/>\n" . "Email: " . $row['email'];
	echo "<br/>\n" . "Thumbnail: " . "<br/>\n" . "<img src =\" " . $row['s3finishedurl'] . "\"/>";
	echo "<br/>\n" . "Raw Image: " . "<br/>\n" . "<img src =\" " . $row['s3rawurl'] . "\"/>";
}
?>
</h4>

	
<?php
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
#echo "Wait 30 seconds for Pending Confirmation";
sleep(30);

//PUBLISH
$result = $sns->publish
([
	'Subject' => 'Picture uploaded in S3 bucket',
	'Message' => 'Congratulations!! You sucessfully subscribed.', // REQUIRED
	'TopicArn' => $ARN,
]);

$link->close();
#header('Location: gallery.php');
?>
<br> <br />
<form action="index.php">
   	<br /><input type="submit" value="Add more Pictures here:">
</form>

</div>
</body>
</html>
<!--source: http://www.w3schools.com/html/html_classes.asp-->
