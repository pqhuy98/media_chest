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
### Build from source
1. Install [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) - a PHP dependency manager. Please install it globally so you can use the command `composer install` in step 3, see instruction in the link.
2. Clone project and put it in PHP's web folder `www` (or `public_html` for VAMK server).
3. In the project folder, run command line `composer install`.
4. Create a MySQL database. Create tables using this [database schema](https://github.com/pqhuy98/media_chest/blob/master/database-schema.sql).
5. Create an `.env` file inside the folder `[project_path]/application` with following content (you will need to change their values):
```
DB_HOST="your database host, e.g. mysql.cc.puv.fi"
DB_USERNAME="your username, e.g. admin"
DB_PASSWORD="your password, e.g. password123"
DB_DATABASE="your database name, e.g. media_chest"

AUTHENTICATION=true   # whether the application performs password check or not (teacher's requirement).
ERROR_REPORTING=false # if true, PHP's error message will thrown, else otherwise. Set it to false in production.
```
6. Create "uploads" directory inside "./application/" directory and set permission:
```
mkdir ./uploads`
chown -R www-data:www-data ./uploads # `www-data` is the Apache2 PHP script's user of VAMK's server. Might be different on other systems.
```
7. Now the REST server should be functioning on `localhost/media_chest/api/...`. See Swagger documentation below for API specification.

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

## REST API Documentation
[Documentation of REST API](https://app.swaggerhub.com/apis-docs/pqhuy98/Media-Chest/1.0.0#/Media).

