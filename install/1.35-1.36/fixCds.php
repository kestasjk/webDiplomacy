<?
// Webdip gamedata dump
// Usage: php fixCds.php
// Fixes up the CD table so that mod forced CDs are listed correctly as being mod forced


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

// Get the Admin log for forced CDs
$tbl = $mysqli->query("SELECT params
	FROM wD_AdminLog WHERE name='Force a user into CD'");

if ($tbl) {
	while($row = $tbl->fetch_row()) {  
		$regex = "/a:.:{s:.:\"userID\";s:.:\"(.+)\";s:.:\"gameID\";s:.:\"(.+)\";}/";
		if(preg_match_all($regex,$row[0],$matches) ){
			// CDs by Admins have all the info we need to correct them
			$userid = $matches[1][0];
			$gameid = $matches[2][0];
			$cd = $mysqli->query("UPDATE wD_CivilDisorders SET forcedByMod=1 WHERE userID=$userid AND gameID=$gameid AND forcedByMod=0  LIMIT 1");
			if (! $cd) {
				print "[x] No rows affected for $userid in $gameid " . $row[0]."\n" ;
			} else {
                         	print "[ ] User $userid in $gameid forgiven.\n";
			}
			
		} else {
        		$regex = "/a:.:{s:.:\"userID\";s:.:\"(.+)\";}/";
			if (preg_match_all($regex,$row[0],$matches)) {
				// CDs by TDs have only which user they were, so we subtract one from each deletedCD modifier for them (while it doesn't take their CD count negative). This is a bit of a hack.
				$userid = $matches[1][0];
				$cd = $mysqli->query("UPDATE wD_Users SET deletedCDs = deletedCDs - 1 WHERE id=$userid AND (deletedCDs * -1) < cdCount");
				if (! $cd) {
					print "[x] No rows affected for $userid (no game ID) " . $row[0]."\n" ;
				} else {
					print "[ ] User $userid forgiven one unknown CD.\n";
				}
			} else {
				print "[x] No match ($row[0])\n";
			}
		}
	}
	$tbl->close();
} else {
	printf("Error with query: %s\n", $mysqli->error);
	exit();
}
?>
