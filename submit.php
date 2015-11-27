
<?php
// Start the session
session_start();
$useremail = $_POST["useremail"];
echo $useremail;

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

echo 'Here is some more debugging info:';
print_r($_FILES);
print "</pre>";

require 'vendor/autoload.php';
#use Aws\S3\S3Client;
$s3 = new Aws\S3\S3Client
    ([
        'version' => 'latest',
        'region'  => 'us-east-1'
    ]);


// Fixed bucket name and id for now
#$bucket = uniqid("php-sb-",false);
$bucket = 'php-sb';

# AWS PHP SDK version 3 create bucket
$result = $s3->createBucket
    ([
        'ACL' => 'public-read',
        'Bucket' => $bucket
    ]);

#$s3->waitUntil('BucketExists', array( 'Bucket' => $bucket));

// PHP version 3 for putting object in s3
$result = $s3->putObject([
        'ACL' => 'public-read',
        'Bucket' => $bucket,
        'Key' => $uploadfile,
        #'ContentType' => $_FILES['userfile']['tmp_name'],
        'SourceFile' => $uploadfile
]);  


$url = $result['ObjectURL'];
echo $url;

$rds = new Aws\Rds\RdsClient([
        'version' => 'latest',
        'region'  => 'us-east-1'
]);


$result = $rds->describeDBInstances([
        'DBInstanceIdentifier' => 'mp1-sb',
]);

$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
print "\n============\n" . $endpoint . "\n================\n";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link));

// check connection
if (mysqli_connect_errno()) 
    {
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
$s3finishedurl = "none";
$status =0;

$stmt->bind_param("ssssssi",$uname,$email,$phone,$s3rawurl,$s3finishedurl,$filename,$status); // 6 strings & 1 integer ssssssi

if (!$stmt->execute()) 
    {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

printf("%d Row inserted.\n", $stmt->affected_rows);

// explicit close recommended
$stmt->close();

$link->real_query("SELECT * FROM items");
$res = $link->use_result();

echo "Result set order...\n";

while ($row = $res->fetch_assoc()) 
    {
        echo $row['id'] . " " . $row['email']. " " . $row['phone'];
    }


//CREATE SNS TOPIC
use Aws\Sns\SnsClient;
$sns = new Aws\Sns\SnsClient
([
	'version' => 'latest',
        'region' => 'us-east-1'
]);

#$result = $client->createTopic([
#    'Name' => '<string>', // REQUIRED
#]);

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
    'Subject' => 'Pictured Uploaded in S3 bucket',
    'Message' => 'Congratulations!! You sucessfully subscribed.', // REQUIRED
    'TopicArn' => $ARN,
]);

$link->close();
header('Location: gallery.php');
#header('Location: gallery.php', true, 303);

?>
