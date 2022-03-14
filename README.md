# JSON MOVIES

## Installation
Clone repo into a folder of your choosing. Run
````
$ composer install
````

## Configuration
Inside /config/schema you will find
````
mindgeek_test.sql
````
Use this file to create the test database

In /config/app.php make sure to set the correct database credentials TEST database

## Starting
Fastest way to get the project up and running is by running

````
php -S localhost:8080
````
in the project root folder

## Testing
Run from the root folder
````
vendor/bin/phpunit
````
NOTE: DebugKit test will always fail unless debugging is turned on


ENJOY!
