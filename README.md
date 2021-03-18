# Backend for GPS Tracker


This repository is written as a backend component for a course project. The repository provides a MVC-based backend with an external REST API which is used by an Android mobile client app.  
  
The backend module is written using PHP and the PHP framework [CodeIgniter](https://codeigniter.com/). It receives data from the client
app, then saves and retrieves the data to/from a MySQL database.

## Application requirement  
The project's requirement written by our teacher Ghodrat Moghadampour:
> - There will be two main activities in the Android app. The first activity's functionality is taking either a
pictures, a video or a voice recording, or selecting an existing file, which can be of any type and submit it to
the server with a text message; user can also submit the text message only.
> - The second activity allows user to search submitted posts based on multiple search conditions: text
phrases in text messages / file name, time of submission, GPS coordinates and device IP address. The
activity will contain for each search condition multiple input boxes or other input types (like a list of valid
GPS coordinates) to make entering criteria and using the app faster. The result will be records that satisfies
Boolean AND of all search conditions. A search condition will be excluded if its Input box is empty. Example
if the user put file name "cat", time "26th Feb", the rest of boxes are empty, then the result would be all
records on date 26th Feb that has "cat" as a sub-string in its file name.
> - The user will provide his name at app's first startup. His posts will be associate with the provided name. A
more complicated design is using username and password. There will be another register and login activity,
and logout buttons in main activities. You can implement both versions separately. One version, which will
not require signing up and logging and the other version requires signing up and logging.

## Installation  
Firstly, you will need to have a MySQL database for this application. Create tables using this [database schema](https://github.com/pqhuy98/media_chest/blob/master/database-schema.sql).

### Quick installation for VAMK's server
This section is for deloying on VAMK's cc.puv.fi server. Run the following command lines on VAMK's shell and a fresh version of the server will be installed. You will need to enter your MySQL host, username, password, and database name.
```
cd ~/public_html
rm -rf media_chest
git clone https://github.com/pqhuy98/media_chest
cd media_chest

mkdir ./uploads
chmod 777 ./uploads
chown -R www-data:www-data ./uploads

echo "# This is the configuration file in which initial settings are defined and used by the application.
# Do not change the content of this file if you are not sure what you are doing.

AUTHENTICATION=true   # whether the application performs password check or not (teacher's requirement).
ERROR_REPORTING=false # if true, PHP's error message will thrown, else otherwise. Set it to false in production.
" > ./application/.env


source ./database-config.vamk.sh

```

### Build from source
1. Clone project and put it in PHP's web folder `www` (or `public_html` for VAMK server).
2. Create "uploads" directory inside "./application/" directory and set permission:
```
mkdir ./uploads`
chmod 777 ./uploads
chown -R www-data:www-data ./uploads # `www-data` is the Apache2 PHP script's user of VAMK's server. Might be different on other systems.
```
3. Create an `.env` file inside the folder `[project_path]/application` with following content (you will need to change their values):
```
# This is the configuration file in which initial settings are defined and used by the application.
# Do not change the content of this file if you are not sure what you are doing.

PRIVATE_KEY="..."

AUTHENTICATION=true   # whether the application performs password check or not (teacher's requirement).
ERROR_REPORTING=false # if true, PHP's error message will thrown, else otherwise. Set it to false in production.
```
4. The value of `PRIVATE_KEY` must be generated with the method described in [this section](https://github.com/pqhuy98/media_chest#private_key-generation).
5. After having a valid PRIVATE_KEY, the REST server should be functioning on `localhost/media_chest/api/...`. See Swagger documentation below for API specification.

## REST API Documentation
[Documentation of REST API](https://app.swaggerhub.com/apis-docs/pqhuy98/Media-Chest/1.0.0#/Media).

## PRIVATE_KEY Generation
### On Linux server, or VAMK's cc.puv.fi
If your server runs Linux or VAMK's server, generating new PRIVATE_KEY can be done simply running [this script](https://github.com/pqhuy98/media_chest/blob/master/database-config.vamk.sh), then enter your credentials:
```
source ./database--config.vamk.sh
```
Otherwise, you have to generate it manually.


### Manual generation
To manually generate the PRIVATE_KEY string, the server admin must:
1) Pick 4 integer numbers `a`, `b`, `c` and `d` from 10 to 99. For example: 14, 16, 42 and 60.
2) Open [this file](https://github.com/pqhuy98/media_chest/blob/master/application/config/database.php#L82) in the server.
3) Navigate to line 82, uncomment the line and change the value of `host`, `username`, `password`, `db_name`, `a`, `b`, `c` and `d` to the corresponding values of his database server. Values of `a`, `b`, `c` and `d` are what the admin chose in step 1.
4) Use the web browser to visit `localhost/media_chest/api/user`. There might be some errors show up because server couldn't connect to database. This is expected since we haven't set a valid PRIVATE_KEY yet. Just ignore the errors.
5) After the previous step, a file named `private_key.txt` will be created in the project's root directory in the server.
6) Copy the content of `private_key.txt` and put it into the variable PRIVATE_KEY inside the .env file. I.e. `PRIVATE_KEY="content of private_key.txt goes here..."`
7) Confirm that the server works by visiting `localhost/media_chest/api/user` to see if list of users is retrieved successfully.
8) After confirming that the server works, comment out the line 82 in step 3 and delete the inputted value so no one can see it.

## Architecture
Model-View-Controller architecture is employed in the whole project. This backend repository implements Model and Controller component, while the frontend Android client app implements the View component.

### Model
Model Diagram:  
![Model Diagram](https://raw.githubusercontent.com/pqhuy98/media_chest/master/model-diagram.png)

The model component contains two classes: User and Media.  
- Class User is a pair of (username, password) and is used for authentication and authorization in the REST API.  
- Class Media contains the post submission, i.e. id, filename, text message, owner's username, longitude and latitude, IP address and timestamp.

Source code files to be concerned:
```
  application/models/User_model.php
  application/models/Media_model.php
```

### Controller:
The controller implements REST methods to create and retrieve User and Media. See section [REST API Documentation](https://github.com/pqhuy98/media_chest#rest-api-documentation) below for detailed end points.

Source code files to be concerned:
```
  application/controller/api/User.php
  application/controller/api/Media.php
```

## Database Credentials Retrieval
Database credentials is stored in the .env file. However, instead of storing host name, username, password and database name in plaintext,they are stored in a single symmetrically encrypted string in the .env file. To retrieve back the plaintext information, the string is then decrypted using a passphrase that is also hidden in the .env file. The symmetric encryption algorithm to be used in this project is a combination AES-256 and HMAC. This section describes the scheme to encrypt and decrypt the database credentials from the .env file.

Example of the .env file:
```
# This is the configuration file in which initial settings are defined and used by the application.
# Do not change the content of this file if you are not sure what you are doing.

PRIVATE_KEY="14164260f9816ad5c652c056f..."
```

The value of PRIVATE_KEY is the encrypted string that contains database credentials. The passphrase to decrypt PRIVATE_KEY is derived from first 8 character of PRIVATE_KEY and the .env file's first two lines (**# This is the configuration...** and **# Do not change...**).

Firstly, we split the PRIVATE_KEY string into 2 parts: the first part contains first 8 character, the second part contains the rest of the string. Using the example of .env file above, we have:

PRIVATE_KEY string is `14164260f9816ad5c652c056f...`  
First part is `14164260`  
Second part is `f9816ad5c652c056f...`  

Next, we split first part `14164260` into 4 pairs of consecutive numbers: `14`, `16`, `42` and `60`, Let's call them `a`, `b`, `c` and `d`.

Next, we take the substring of the .env file's first line from position `a` to position `c` (i.e. from postion 14 to 42):  
First line: `# This is the configuration file in which initial settings are defined and used by the application.`
Substring from position 14 to 42 : `configuration file in which `.

Next, we take the substring of the .env file's second line from position `b` to position `d` (i.e. from postion 16 to 60):  
First line: `# Do not change the content of this file if you are not sure what you are doing.`
Substring from position 16 to 60 : `the content of this file if you are not sure`.

Now we concatenate the two substring to get the passphrase: `configuration file in which the content of this file if you are not sure`. We use this phase phrase to decrypt the PRIVATE_KEY's second part `f9816ad5c652c056f...` with the encryption algorithn to retrieve the database credentials `mysql.vamk.fi:e123456:password:database_name` (the host name, username, password and database name is concatenated by character `:`).

Implementation of the scheme can be found in [this part of the source code](https://github.com/pqhuy98/media_chest/blob/master/application/config/database.php#L79).

The encryption and decryption algorithm is a combination of AES-256 and HMAC which is originated from [here](https://stackoverflow.com/a/46872528).