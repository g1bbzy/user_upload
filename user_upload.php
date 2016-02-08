<?php
//Global variables
$csv_file = "";
$create_table = false;
$creat_table_sql = "DROP TABLE IF EXISTS `users`; CREATE TABLE users (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL UNIQUE);";
$dry_run = false;
$u = "";
$p = "";
$h = "";
$db = "";
$users = [];
$dbconnection = false;
// --help output
$help = "
User Upload Help
This is script is used to upload user information from a csv file to a mysql database.
Command line options:
--file -
--dry_run -
--creat_table -
-u -
-p - 
-h -
";

// Loop through Args. Start at position 1 because position 0 is location/name of php file.
if(count($argv) > 1){
	for ($i=1; $i < count($argv); $i++) { 
		// 
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
		else if ($argv[$i] == "-db"){
			if (isset($argv[$i+1])) {
				$db = $argv[$i+1];
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
}
// check if credentials and db host were provided
if($u && $p && $h && $db){
	// Create connection
	$conn = mysqli_connect($h, $u, $p, $db);
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	echo "**** Connected to database ****\n\n";
	$dbconnection = true;
	if (mysqli_query($conn, $creat_table_sql)) {
	    fwrite(STDOUT, "users table created Successfully\n");
	} else {
	    echo "Error: Could not create table:\n".mysqli_error($conn). "\n";
	}
	if ($create_table){
		$conn->query();
	}
}
else{
	fwrite(STDOUT, "Please provide database credentials, type --help for more information\n");
	die();
}

if(!$csv_file){
	fwrite(STDOUT, "Please provide a csv file with option --file\n");
	die();
}

// check if $csv contains a file.
if ($csv_file){
	// surround opening CSV file in a try catch incase an error occurs.
	try{
		$file = fopen($csv_file, 'r');
		if ($file){
			while (($line = fgetcsv($file)) !== FALSE) {
				array_push ($users, $line);
			}
			fclose($file);
		}
	}
	catch(Exception $e){
		print("An error occured opening the csv file. Please the check file or the file location.");
	}
	
}
// Check if users contains any data
if($users){
	//Create db connection
	$conn = mysqli_connect($h, $u, $p, $db);
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
			if($u && $p && $h && $db){				
				$sql = "INSERT INTO users (name, surname, email) VALUES('".$users[$i][0]."','".$users[$i][1]."','".$users[$i][2]."')";
				if (mysqli_query($conn, $sql)) {
				    echo "Line ". $i ." in csv file: Successfully inserted\n";
				} else {
				    echo "Error: " . $sql . "\n" . mysqli_error($conn) . "\n";
				}
			}
		} else {
			echo "Line ".$i." in csv file: email is not valid\n";
		  //fwrite(STDOUT, "line ".(string)$i+1." of csv file: not a valid email");
		}
	}
}

?>
