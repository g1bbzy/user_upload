# user_upload

A command line php script that uploads user information from a .CSV file to a mysql database.
The script will create a database called danielgibbs

Prerequisites:

- Make sure your server has php and mysql installed.
- Make sure the mysql user has permissions to query and manipulate databases.

Command line options:

--file - The csv file location. e.g 'c:\users.csv'

--dry_run - Used to execute this script but not insert users into the database.

--create_table - This option creates the users table. If table already exists it will drop the table
and recreate it (no further action will take place).

-u - Username for database connection.

-p - Password for database connection.

-h - Host location of the database. e.g localhost.

Example:

php user_upload.php --create table -u username -p password -h localhost

php location/user_upload.php -u username -p password -h localhost --file location/users.csv
