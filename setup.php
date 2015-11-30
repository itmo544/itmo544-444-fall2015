<?php

// Start the session
require 'vendor/autoload.php';
$rds = new Aws\Rds\RdsClient
([
   	'version' => 'latest',
   	'region'  => 'us-east-1'
]);

// Create a table 
$result = $rds->describeDBInstances([
	'DBInstanceIdentifier' => 'mp1-sb',
]);

$endpoint = $result['DBInstances'][0]['Endpoint']['Address'];
print "\n================\n" . $endpoint . "\n================\n";


//echo "begin database";
$link = mysqli_connect($endpoint,"controller","letmein888","customerrecords",3306) or die("Error " . mysqli_error($link)); 

#create table items
$sql = "CREATE TABLE IF NOT EXISTS items
(
	id INT NOT NULL AUTO_INCREMENT,
	uname VARCHAR(20) NOT NULL,
	email VARCHAR(30) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	s3rawurl VARCHAR(255) NOT NULL,
	s3finishedurl VARCHAR(255) NOT NULL,
	filename VARCHAR(255) NOT NULL,
	status TINYINT(3)CHECK(state IN(0,1,2)),
	cdate DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(id)
)";

$link->query($sql);
$link->close();
?>

