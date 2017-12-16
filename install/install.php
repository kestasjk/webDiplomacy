<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

if ( $Misc->Panic )
	libHTML::error('Cannot update in a panic.');

/*
 * This code may be needed for big updates
 *
 * if ( !$Misc->Maintenance )
{
	$Misc->Maintenance = 1;
	$Misc->write();
	libHTML::error('Cannot update unless in maintenance mode; maintenance mode set, wait a minute for clients to
		finish and run again.');
}

ini_set('memory_limit',"20M"); // 8M is the default
ini_set('max_execution_time','120');

$DB->get_lock('install',0); // Make sure only one person performs the update

$Misc->read(); // Check we haven't updated while waiting for the lock

if( $Misc->Version == VERSION )
	libHTML::notice('Complete','Update complete');
*/

if ( $Misc->Version == 98 || $Misc->Version == 99 )
{
	$Misc->Version = 100;
	$Misc->write();
	libHTML::notice('Updated','Updated version number, please refresh.');
}
elseif ( $Misc->Version == 104 || $Misc->Version == 130 )
{
	$Misc->Version = 131;
	$Misc->write();
	libHTML::notice('Updated','Updated version number, please refresh.');
}
else
{
	libHTML::starthtml();
	if (is_object($User) && $User->type['Admin'])
	{
		print '<div class="content-notice"><p>'."Database version ".($Misc->Version/100)." and code
				version ".(VERSION/100)." don't match. Auto-update script is called.".'</p>';
		require_once('update.php');
		print '<br>Please refresh your browser.';
	} else
	{
		print '<div class="content-notice"><p>'."Database version ".($Misc->Version/100)." and code
				version ".(VERSION/100)." don't match. Please wait until an administrator has run the update script.".'</p>';
	}
	print '</div>';
}

print '</div>';
libHTML::footer();

?>