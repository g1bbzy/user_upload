<?php
//Global variables
$csv_file = "";
$create_table = false;
$dry_run = false;
$u = "";
$p = "";
$h = "";
$users = [];

// --help output
$help = "
****  User Upload Help  ****

This is a php script that uploads user's information from a csv file to a mysql database.
When using this script, make sure your database user has sufficient permissions.

Command line options:

--file - The csv file location. e.g 'c:\users.csv'

--dry_run - Used to execute this script but not insert users into the database.

--create_table - This option is creates the users table. If table already exists it will drop the table
and recreate it (no further action will take place).

-u - Username for database connection.

-p - Password for database connection.

-h - Host location of the database. e.g localhost.
";

// Loop through Args. Start at position 1 because position 0 is location/name of php file.
if(count($argv) > 1){
	for ($i=1; $i < count($argv); $i++) { 
		if ($argv[$i] == "--file"){
			if (isset($argv[$i+1])) {
			    $csv_file = $argv[$i+1];
			}
		}
		else if ($argv[$i] == "--create_table"){
			$create_table = true;
		}
		else if ($argv[$i] == "--dry_run"){
			    $dry_run = true;
		}
		else if ($argv[$i] == "-u"){
			if (isset($argv[$i+1])) {
				$u = $argv[$i+1];
			}
		}
		else if ($argv[$i] == "-p"){
			if (isset($argv[$i+1])) {
				$p = $argv[$i+1];
			}
		}
		else if ($argv[$i] == "-h"){
			if (isset($argv[$i+1])) {
				$h = $argv[$i+1];
			}
		}
		else if ($argv[$i] == "--help"){
			//no need to continue with the script. Output help then die.
			fwrite(STDOUT, $help);
			die();
		}		
	}
}
else
{
	// no arguments were provided
	fwrite(STDOUT, "Please provide appropriate command line options\n");
	die();
}
// check if credentials and db host were provided
if($u && $p && $h){
	$conn = mysqli_connect($h, $u, $p);
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	if (mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS danielgibbs;")) {
	    fwrite(STDOUT, "database exists or has been created.\n");
	} else {
	    echo "Error: database not created:\n".mysqli_error($conn). "\n";
	    die();
	}

	// Create connection
	$conn = mysqli_connect($h, $u, $p, 'danielgibbs');
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	echo "**** Connected to database ****\n\n";
	//check if create table option was provided
	if ($create_table){
		// Check if users table already exists
		$val = mysqli_query($conn,"select 1 from `users` LIMIT 1");
		// if table exists
		if($val !== FALSE)
		{
			// Query to create table
			$create_table_sql = "CREATE TABLE users (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL UNIQUE);";
			//Drop table as it already exists
			if (mysqli_query($conn, "DROP TABLE IF EXISTS danielgibbs.users;")) {
			    fwrite(STDOUT, "users table has been dropped\n");
			} else {
			    echo "Error: Could not drop table:\n".mysqli_error($conn). "\n";
			    die();
			}
			// create table again
			if (mysqli_query($conn, $create_table_sql)) {
			    fwrite(STDOUT, "users table created successfully\n");
			    die();
			} else {
			    echo "Error: Could not create table:\n".mysqli_error($conn). "\n";
			}
		}
		else
		{
			// table does not exists so create it
		    $create_table_sql = "CREATE TABLE users (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL UNIQUE);";
			if (mysqli_query($conn, $create_table_sql)) {
			    fwrite(STDOUT, "users table created Successfully\n");
			    die();
			} else {
			    echo "Error: Could not create table:\n".mysqli_error($conn). "\n";
			    die();
			}
		}
	}
}
else{
	// not all database information was provided
	fwrite(STDOUT, "Please provide database credentials, type --help for more information\n");
	die();
}

// if csv file was not given as an option print error then stop script.
if(!$csv_file){
	fwrite(STDOUT, "Please provide a csv file with option --file\n");
	die();
}

// check if $csv file given is of type csv.
if (strpos($csv_file, '.csv') !== false){
	// surround opening CSV file in a try catch incase an error occurs.
	try{
		$file = fopen($csv_file, 'r');
		// if $file was opened successfully
		if ($file){
			while (($line = fgetcsv($file)) !== FALSE) {
				//push each line of csv file into $users array
				array_push ($users, $line);
			}
			fclose($file);
		}
	}
	catch(Exception $e){
		// display error then stop script.
		print("An error occured opening the csv file. Please the check file or the file location.");
		die();
	}
	
}
else
{
	fwrite(STDOUT, "Please provide a file that is of the type csv\n");	
}
// Check if dry run was stated.
if ($dry_run){
	fwrite(STDOUT, "Performing dry run\n");
	die();
}
else{
	//else carry on with script. check if $users contains data.
	if($users){
		//Create db connection
		$conn = mysqli_connect($h, $u, $p, 'danielgibbs');
		// Check connection
		if (!$conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		//loop through users array. Start at position 1 as 0 is column names.
		for ($i=1; $i < count($users); $i++) {
			// Convert first and last name to uppercase. 
			$users[$i][0] = mysqli_real_escape_string($conn, strtoupper($users[$i][0]));
			$users[$i][1] = mysqli_real_escape_string($conn, strtoupper($users[$i][1]));
			// sanitise email address and convert to lowercase.
			$users[$i][2] = filter_var(mb_strtolower($users[$i][2]), FILTER_SANITIZE_EMAIL);
			if (!filter_var($users[$i][2], FILTER_VALIDATE_EMAIL) === false) {
				//escape email address after filter but before inserting
				$users[$i][2] = mysqli_real_escape_string($conn, $users[$i][2]);
				// check for database credentials.
				if($u && $p && $h){	
					// insert current csv line into users table			
					$sql = "INSERT INTO users (name, surname, email) VALUES('".$users[$i][0]."','".$users[$i][1]."','".$users[$i][2]."')";
					if (mysqli_query($conn, $sql)) {
						// if insert was successfull
					    echo "Line ". $i ." in csv file: Successfully inserted\n";
					} else {
						// if an error occured
					    echo "Line ". $i ." in csv file: " . $sql . "\n" . mysqli_error($conn) . "\n";
					}
				}
			} else {
				// email address is not valid
				echo "Line ".$i." in csv file: email is not valid\n";
			}
		}
	}
}
?>
