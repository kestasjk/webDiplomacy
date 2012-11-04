<?php

defined('IN_CODE') or die('This script can not be run by itself.');

if ( isset($_REQUEST['userIDs']) )
	$userIDs=$_REQUEST['userIDs'];
	
if ( isset($_REQUEST['checkIP']) )
	$checkIP=$_REQUEST['checkIP'];

/**
 * Print a form for selecting which users to check
 */
print '<FORM method="get" action="admincp.php">';
print '<P><STRONG>User IDs (separated by ","): </STRONG><INPUT type="text" name="userIDs" value="'.(isset($userIDs)?$userIDs:'').'" length="30" /> ';
print ' - <STRONG>IP: </STRONG><INPUT type="text" name="checkIP" value="'.(isset($checkIP)?$checkIP:'').'" length="30" />';
print ' - <input type="submit" name="Submit" class="form-submit" value="Check" /></form></P>';


?>