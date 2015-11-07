<?php
// Start the session^M
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1',
]);

$result = $rds->createDBInstance([
    'AllocatedStorage' => 5, //MIN REQUIRED 5GB 
    'DBInstanceClass' => 'db.t1.micro', // REQUIRED
    'DBInstanceIdentifier' => 'mp1-sb', // REQUIRED
    'DBName' => 'customerrecords',
    'Engine' => 'MySQL', // REQUIRED
    'EngineVersion' => '5.5.41',
    'MasterUserPassword' => 'letmein888',
    'MasterUsername' => 'controller',
    'PubliclyAccessible' => true,
    #'StorageEncrypted' => true || false,
    #'TdeCredentialArn' => '<string>',
    #'TdeCredentialPassword' => '<string>',
    #'VpcSecurityGroupIds' => ['<string>', ...],
]);

print "Create RDS DB results: \n";
print_r($rds);

$result = $rds->waitUntil('DBInstanceAvailable',['DBInstanceIdentifier' => 'mp1-sb',
]);


// Create a table 
$result = $rds->describeDBInstances([
    'DBInstanceIdentifier' => 'mp1-sb',
]);


$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
print "============\n". $endpoint . "================";

//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords","3306") or die("Error " . mysqli_error($link)); 
echo "Here is the result: " . $link;

// check connection
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

#create table comments (renamed table name from comment to items)
$sql_table = 'CREATE TABLE items 
(
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
uname VARCHAR2(20) NOT NULL,
email VARCHAR2(20) NOT NULL,
phone VARCHAR2(20) NOT NULL,
s3rawurl VARCHAR2(255) NOT NULL,
s3finishedurl VARCHAR2(255) NOT NULL,
jpgfilename VARCHAR2(255) NOT NULL,
status TINYINT(3)CHECK(state IN(0,1,2)),
tdate DATETIME DEFAULT CURRENT_TIMESTAMP
)';

$con->query($sql_table);

?>

