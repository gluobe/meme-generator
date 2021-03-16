# Gluo Meme Generator using Docker

> Public version *without* workflow files.

## Installation

1. Clone this repository.  

2. Set environment variables to configure the app:

**Apache**  

| Variable                | Type    | Decription                | Default  |
|-------------------------|---------|---------------------------|----------|
| MYNAME                  | string  | your name                 | user     |
| CLOUD                   | string  | choose AZ, AWS or LOCAL   | LOCAL    |
| REGION                  | string  | AWS cloud region          | /        |
| BLUESITE                | boolean | theme color               | false    |
| MANAGED_FILES           | boolean | configure storage backend | false    |
| MANAGED_DB              | boolean | configure database        | false    |
| AZURE_DB_USER           | string  | AZ cosmosdb username      | /        |
| AZURE_DB_PASSWORD       | string  | AZ cosmosdb password      | /        |
| AZURE_STORAGE_ACCOUNT   | string  | AZ Storage Account name   | /        |
| AZURE_STORAGE_CONTAINER | string  | AZ Storage container      | /        |
| AZURE_STORAGE_KEY       | string  | AZ Storage key            | /        |
| MONGO_USER              | string  | MongoDB username          | student  |
| MONGO_PASSWORD          | string  | MongoDB password          | Cloud247 |
| MONGO_HOST              | string  | MongoDB hostname          | mongodb  |
| MONGO_DB                | string  | MongoDB database name     | memegen  |

**MongoDB**  

| Variable                   | Type    | Decription                      |
|----------------------------|---------|---------------------------------|
| MONGO_INITDB_DATABASE      | string  | must be equal to MONGO_DB       |
| MONGO_INITDB_ROOT_USERNAME | string  | must be equal to MONGO_USER     |
| MONGO_INITDB_ROOT_PASSWORD | string  | must be equal to MONGO_PASSWORD |

3. Build the images

```bash
# build the Apache Image
docker-compose build
```

4. Run the app
```bash

# using a local database and storage
docker-compose up -d --file docker-comose-local.yml

# using a managed database and remote storage
docker-compose up -d --file docker-comose-managed.yml

```

## Cleaning up

```bash
docker-compose down
sudo ./apache/removeGeneratedMemes.sh
```
