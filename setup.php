<?php
// Start the session^M
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

$result = $rds->createDBInstance([
    'AllocatedStorage' => 5,
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
# print_r($rds);

$result = $rds->waitUntil('DBInstanceAvailable',['DBInstanceIdentifier' => 'mp1-sb',
]);


// Create a table 
$result = $rds->describeDBInstances([
    'DBInstanceIdentifier' => 'mp1-sb',
]);


$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];


$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords") or die("Error " . mysqli_error($link)); 

echo "Here is the result: " . $link;

#create table comments (rename comment to mp1-table)
$sql = "CREATE TABLE mp1-table 
(
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
uname VARCHAR(20) NOT NULL,
email VARCHAR(20) NOT NULL,
phone VARCHAR(20)NOT NULL,
raws3url VARCHAR(256) NOT NULL,
finisheds3url VARCHAR(256) NOT NULL,
jpgfilename VARCHAR(256) NOT NULL,
status INT NOT NULL,
timestamp
)";

$con->query($sql);

?>

