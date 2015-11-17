<?php
// Start the session
require 'vendor/autoload.php';

$rds = new Aws\Rds\RdsClient
([
   	'version' => 'latest',
   	'region'  => 'us-east-1',
]);

$s3 = new Aws\S3\S3Client
([
   	'version' => 'latest',
   	'region'  => 'us-east-1'
]);

//Create Database Instance
$result = $rds->createDBInstance
([
    'AllocatedStorage' => 5, //MIN REQUIRED 5GB 
    'DBInstanceClass' => 'db.t1.micro', // REQUIRED
    'DBInstanceIdentifier' => 'mp1-sb', // REQUIRED
    'DBName' => 'customerrecords',
    'Engine' => 'MySQL', // REQUIRED
    'EngineVersion' => '5.6.23', // Version 5.6 and up is Required for Read Replics
    'MasterUserPassword' => 'letmein888',
    'MasterUsername' => 'controller',
    'PubliclyAccessible' => true,
    #'StorageEncrypted' => true || false,
    #'TdeCredentialArn' => '<string>',
    #'TdeCredentialPassword' => '<string>',
    #'VpcSecurityGroupIds' => 'sg-e30e4b84' #['<string>', ...],
]);

#Create Read Replica - Golden Copy
$rrresult = $rds->createDBInstanceReadReplica
([
	'DBInstanceIdentifier' => 'mp1-sb-rr', //Unique Name to identify RR DB Instance
	'SourceDBInstanceIdentifier' => 'mp1-sb', //DB instance name that will act as source 
	'PubliclyAccessible' => true, //true specifies an Internet-facing instance with a publicly resolvable DNS name
]);	

#Wait untill Database is created
#$result = $rds->waitUntil('DBInstanceAvailable',['DBInstanceIdentifier' => 'mp1-sb',]);
#print "RDS DB Successfully Created: \n";
#print_r($rds);


// Create a table 
$result = $rds->describeDBInstances
	([
    	'DBInstanceIdentifier' => 'mp1-sb',
	]);


$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
	print "============". $endpoint . "================";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords") or die("Error " . mysqli_error($link)); 

# Need print statement here to make sure to get the result.
# echo "Here is the result: " . $link;

// check connection
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

#create table comments
$sql = "CREATE TABLE items
(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	uname VARCHAR2(20) NOT NULL,
	email VARCHAR2(30) NOT NULL,
	phone VARCHAR2(20) NOT NULL,
	s3rawurl VARCHAR2(255) NOT NULL,
	s3finishedurl VARCHAR2(255) NOT NULL,
	filename VARCHAR2(255) NOT NULL,
	status TINYINT(3)CHECK(state IN(0,1,2)),
	date DATETIME DEFAULT CURRENT_TIMESTAMP
)";

$con->query($sql);

	
	
?>

