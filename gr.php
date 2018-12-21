<?php
// Webdip gamedata dump
// Usage: php gr.php
// Prints out the game data in the format used by Alderian's scripts.

$zipName = "ghostRatingData.zip";
$dataName = "ghostRatingData.txt";


if (php_sapi_name() != "cli") {
 	die ("This script must only be run from the command line");
}

define("IN_CODE",1);
require_once("config.php");

// Connect to the database
$mysqli = new mysqli(Config::$database_socket, Config::$database_username, Config::$database_password,Config::$database_name);
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

// Create the file to write output in
//$filename =  tempnam(sys_get_temp_dir(),$dataName);
$filename = $dataName;
unlink($filename);
$fp = fopen($filename, 'w');
if (!$fp) {
	die("Error opening output file");
}

// Get the games and the results:
$tbl = $mysqli->query("SELECT variantID, gameID, userID,pot,gameOver,status,supplyCenterNo,potType,phaseMinutes,turn,processTime,pressType, case when wD_Users.type='banned' then 1 else 0 end as IsBanned from wD_Games,wD_Members,wD_Users  where wD_Games.id = wD_Members.gameId and wD_Members.userID = wD_Users.id AND gameOver != 'No'",MYSQLI_USE_RESULT);
if ($tbl) {
	printTable($fp,$tbl);
} else {
	printf("Error with query: %s\n", $mysqli->error);
	exit();
}

// Now get the users
$tbl = $mysqli->query("SELECT id, username, case when wD_Users.type='banned' then 1 else 0 end as IsBanned from wD_Users;",MYSQLI_USE_RESULT);
if ($tbl) {
	printTable($fp,$tbl);
} else {
	printf("Error with query: %s\n", $mysqli->error);
	exit();
}

// Now create the zipfile
/*unlink($zipName);        // remove previous file
$zip =  new ZipArchive();
if ($zip->open($zipName,ZipArchive::CREATE) != true) {
	die("Cannot create zip");
}
$zip->addFile($filename,$dataName);
$zip->close();

// Cleanup temp file
unlink($filename);*/

print "Successfully created $zipName" . PHP_EOL;
exit();



// Helper function to print the table and the headers
function printTable($fp,$tbl) 
{
	// Print the column names
	$colnames = array();
	while ($fieldinfo= $tbl->fetch_field()) {
		array_push($colnames,$fieldinfo->name);
	}
	allcsv($fp,$colnames);

	// Now print the results
	while($row = $tbl->fetch_row()) {
		allcsv($fp,$row);
	}
	$tbl->close();
}


// PHP's default CSV encoder doesn't
// enclose all values, so here's one that does.
// Slightly modified from http://stackoverflow.com/a/2515993/790070
 function allcsv($file_handle, $data_array, $field_sep=',', $enclosure='"', $record_sep=PHP_EOL)
 {
     allescape(false, $enclosure);
     $data_array=array_map('allescape',$data_array);
     return fputs($file_handle, 
         $enclosure 
         . implode($enclosure . $field_sep . $enclosure, $data_array)
         . $enclosure . $record_sep);
 }
 function allescape($in, $enclosure=false)
 {
    static $enc;
    if ($enclosure===false) {
        return str_replace($enc, '\\' . $enc, $in);
    }
    $enc=$enclosure;
 }

?>
