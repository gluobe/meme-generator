<?php
#####################
## Global Settings ##
#####################

$yourId = getenv('MYNAME');

# Cloud Provider: choose from AZ, AWS or LOCAL.
$cloud = getenv('CLOUD');

# if cloud provider is AWS
$awsRegion = getenv('REGION');

###################
## Site Settings ##
###################

### DATABASE ###

# Save meta-data in a local database (mongodb) or a remote database (dynamodb or cosmosdb)
$remoteData = filter_var(getenv('MANAGED_DB'), FILTER_VALIDATE_BOOLEAN); # true/false

$mongoUser = getenv('MONGO_USER');
$mongoPassword = getenv('MONGO_PASSWORD');
$mongoHost = getenv('MONGO_HOST');
$mongoDB = getenv('MONGO_DB');

## MANAGED DATABASE ##

# Azure CosmosDB
$azUsername = getenv('AZURE_DB_USER');
$azPassword = getenv('AZURE_DB_PASSWORD');

# AWS DynamoDB
#$dynamoDBTable = "lab-images-table-$yourId"; # not using cloudformation
#$dynamoDBTable = "lab-cf-images-table-$yourId"; # using cloudformation

### STORAGE ###

# Save the memes on the local filesystem or at a remote storage solution (AWS s3 or Azure Blob)
$remoteFiles = filter_var(getenv('MANAGED_FILES'), FILTER_VALIDATE_BOOLEAN); # true/false

## REMOTE STORAGE ##

# Azure Blob Storage
$saAccountName = getenv('AZURE_STORAGE_ACCOUNT');
$saAccountAccessKey = getenv('AZURE_STORAGE_KEY');
$saContainerName = getenv('AZURE_STORAGE_CONTAINER');

# AWS S3
#$s3Bucket = "lab-images-bkt-$yourId"; # not using cloudformation
#$s3Bucket = "lab-cf-images-bkt-$yourId"; # using cloudformation 

### THEMING ###

# Wether to set site color to blue or green (used to differentiate sites from ELB)
#$siteColorBlue = getenv('BLUESITE'); # Blue
$siteColorBlue = filter_var(getenv('BLUESITE'), FILTER_VALIDATE_BOOLEAN); # true/false
?>
