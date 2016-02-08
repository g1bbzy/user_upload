<?php
//Global variables
$csv_file = "";
$create_table = false;
$dry_run = false;
$u = "";
$p = "";
$h = "";
$users = [];
$dbconnection = false;

$help = "
User Upload Help
This is script is used to upload users from a csv file to a mysql database.
Supported Commands:
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
		else if ($argv[$i] == "--help"){
			//no need to continue with the script.
			fwrite(STDOUT, $help);
			die();
		}		
	}
}
else
{
	// no arguments were provided
	fwrite(STDOUT, "Please provide appropriate arguments\n");
}
// check if credentials and db host were proveded
if($u && $p && $h){
	// Create connection
	$conn = mysqli_connect($h, $u, $p);
	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}
	echo "Connected to database";
	$dbconnection = true;
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
	//loop through users array
	var_dump($users);
}
?>