<?php
//Global variables
$csv_file = "";
$create_table = false;
$dry_run = false;
$u = "";
$p = "";
$h = "";

// Loop through Args. Start at position 1 because position 0 is location/name of php file.
if(count($argv) > 1){
	for ($i=1; $i < count($argv); $i++) { 

		if ($argv[$i] == "--file"){
			if (isset($argv[$i+1])) {
			    $csvFile = $argv[$i+1];
			}
		}
		else if ($argv[$i] == "--create_table"){
			$createTable = true;
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
	}
}
else
{
	fwrite(STDOUT, "Please provide appropriate arguments\n");
}
echo $u . ' ' . $p . ' ' . $h;
?>
