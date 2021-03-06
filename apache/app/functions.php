<?php
# Load libraries installed with composer, like the AWS SDK and Azure SDK
require 'vendor/autoload.php';
# Use Azure Blob Rest API
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
# Include the config file
include 'config.php';
# Set errors on (/var/log/apache2/error.log)
ini_set("display_errors", "On");

#######################
## Declare functions ##
#######################

// Connect to the database, either mongodb or dynamodb
function ConnectDB(){
    global $m;
    global $awsRegion;
    global $remoteData;
    global $cloud;
    global $azUsername;
    global $azPassword;
    global $mongoUser;
    global $mongoPassword;
    global $mongoHost;
    global $mongoDB;

    if($remoteData){
	
        // DynamoDB
        if($cloud == 'AWS') {    

	    $m = Aws\DynamoDb\DynamoDbClient::factory(array(
                'region'  => (string)$awsRegion,
	        'version' => "latest"
	    ));

	} elseif($cloud == 'AZ') {
	
	// Azure CosmosDB - using the MongoDB adapter
	    $servername="$azUsername.mongo.cosmos.azure.com";
	    $serverport="10255";
	    $database="memegen";

            $m = new \MongoDB\Driver\Manager("mongodb://$azUsername:$azPassword@$servername:$serverport/$database?ssl=true&replicaSet=globaldb&retrywrites=false&maxIdleTimeMS=120000&appName=@$azUsername@");

	} else { 
		print('there must be an error somewhere');
	} 
    } else {
	    // MongoDB
	    //$username=$mongoUser;
	    //$password=$mongoPassword;
	    //$servername=$mongoHost;
	    
	    $m = new \MongoDB\Driver\Manager("mongodb://${mongoUser}:${mongoPassword}@${mongoHost}/${mongoDB}");
    }
}

// Inserts a meme name and current date into the database, either mongodb or dynamodb
function InsertMemes($imageName,$url){
    global $m;
    global $cloud;
    global $remoteData;
    global $dynamoDBTable;

    $rand = rand(1,99999999);
    $time = time();

    if($remoteData){
        // DynamoDB
	if($cloud == 'AWS') {
            //get length of db
            $iterator = $m->getIterator('Scan', array(
              'TableName' => "$dynamoDBTable"
            ));
            $id = (string)$time.(string)$rand;
            // Insert data in the images table
            $insertResult = $m->putItem(array(
                'TableName' => "$dynamoDBTable",
                'Item' => array(
                    'id'      => array('N' => (string)$id),
                    'name'    => array('S' => $imageName),
                    'date'    => array('S' => (string)$time),
                    'url'     => array('S' => $url)
                )
	    ));
	} elseif($cloud == 'AZ') { 
        
    	    // Insert into memegen db and images collection
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                            'name'  => array('S' => $imageName),
                            'date'  => array('S' => (string)$time),
                            'url'     => array('S' => $url)
                          ]);
            $m->executeBulkWrite('memegen.images', $bulk);

	} else { 
		print('there must be an error somewhere');
	} 
    } else {

        // MongoDB
	    
    	    // Insert into memegen db and images collection
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                            'name'  => array('S' => $imageName),
                            'date'  => array('S' => (string)$time),
                            'url'     => array('S' => $url)
                          ]);
            $m->executeBulkWrite('memegen.images', $bulk);
        }
}

// Gets all memes and encodes and echo's it so ajax can catch it.
function GetMemes(){
    global $m;
    global $cloud;
    global $s3Bucket;
    global $dynamoDBTable;
    global $remoteFiles;
    global $remoteData;

    // If data is stored remotely, use dynamodb, else mongodb
    if($remoteData){

        // DynamoDB
	if($cloud == 'AWS') {

        $iterator = $m->getIterator('Scan', array(
          'TableName' => "$dynamoDBTable"
        ));
        echo json_encode(iterator_to_array($iterator));

	} elseif($cloud == 'AZ') {

	// CosmosDB
        $filter = [];
        $options = [];
        $query = new MongoDB\Driver\Query($filter, $options);
        $iterator = $m->executeQuery('memegen.images', $query);

        echo json_encode(iterator_to_array($iterator));
	} else { 
		
	print('there must be an error somewhere');
	} 
    
    } else {
		
	// MongoDB
        $filter = [];
        $options = [];
        $query = new MongoDB\Driver\Query($filter, $options);
        $iterator = $m->executeQuery('memegen.images', $query);

        echo json_encode(iterator_to_array($iterator));
    }
}

// Generates a meme with the python script and either puts it locally or in an S3 bucket
function generateMeme($top, $bot, $imgname){
  global $m;
  global $s3Bucket;
  global $remoteFiles;
  global $awsRegion;
  global $cloud;
  global $saAccountName;
  global $saContainerName;
  global $saAccountAccessKey;

  # Save current dir and go into python dir
    $olddir = getcwd();
    chdir("meme-generator");

    # Create full imagenames
    $rand = rand(1,999);
    $imgnameorig = $imgname . ".jpg";
    # Remove nasty chars for meme picture
    $top = preg_replace('/[\'\"]+/', '', $top);
    $bot = preg_replace('/[\'\"]+/', '', $bot);
    # No extension variable image name
    $imgnametargetnoext = $imgname . "-" . $top . "-" . $bot . "-" . $rand;
    # Replace nasty characters for filename
    $imgnametargetnoext = preg_replace('/[^-.0-9\w]+/', '', $imgnametargetnoext);
    # With extension variable image name
    $imgnametargetwithext = $imgnametargetnoext . ".jpg";

    # Execute meme generator python command
    $command = "python3 memegen.py '$top' '$bot' '$imgnameorig' '$imgnametargetwithext' 2>&1";
    $commandoutput = exec($command, $out, $status);

    $image = fopen("/var/www/html/meme-generator/memes/".$imgnametargetwithext,'r');
    # Go back to original dir
    chdir($olddir);

    $url = "no url";

    if($remoteFiles){
	if($cloud == 'AWS') { 
        	// sync to s3
        	$sdk = new Aws\Sdk([
            		'region'   => (string)$awsRegion,
            		'version'  => 'latest',
        	]);
        	// Use an Aws\Sdk class to create the S3Client object.
	        $s3Client = $sdk->createS3();

        	// Send a PutObject request and get the result object.
        	$result = $s3Client->putObject([
		        'Bucket' => $s3Bucket,
        		'Key'    => $imgnametargetwithext,
		        'Body'   => $image
        	]);
	
        	// Get the url from the s3 stored image.
	        $url = $s3Client->getObjectUrl ( $s3Bucket, $imgnametargetwithext );

	} elseif($cloud == 'AZ') {

		// Azure Storage Account connection 
		$saConnectionString = "DefaultEndpointsProtocol=https;AccountName=$saAccountName;AccountKey=$saAccountAccessKey;EndpointSuffix=core.windows.net";

		// Create client
		$blobClient = BlobRestProxy::createBlobService($saConnectionString);

		// Upload generated Meme
		$blobClient->createBlockBlob($saContainerName, $imgnametargetwithext, $image);
		$url = "https://" . $saAccountName . ".blob.core.windows.net/" . $saContainerName . "/" . $imgnametargetwithext;

	} else {
		print "nothing happened";
	}
        	// Delete temporary file
	        unlink("/var/www/html/meme-generator/memes/".$imgnametargetwithext);
    }
    return array($imgnametargetnoext,$url);
}

?>

