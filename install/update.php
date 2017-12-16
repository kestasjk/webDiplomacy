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

$row = list($orderIndex) = $DB->sql_row("SELECT m.value FROM wd_misc m WHERE m.name='Version'");
$db_version = $row[0];
$code_version = VERSION;

$update_dirs = array();
$i = 0;
//find all update directories
foreach (scandir('install') as $update_dir)
{
	if(is_dir('install/'.$update_dir))
	{
		//make sure the directory is an update directory,
		//e.g. the name is composed of version.subversion-newversion.newsubversion
		//(version and newsubversion are arbitrary integers, subversion and newsubversion are two-digit integers).
		if(preg_match('/^([0-9]+).[0-9][0-9]-([0-9]+).[0-9][0-9]$/',$update_dir)==1) 
		{
			$update_dirs[$i]=$update_dir;
			$i++;
		}
	}
}

//ignore any updates already applied and execute the other updates
foreach ($update_dirs as $dir) 
{
	$strings = explode('-', $dir);
	$start_version = $strings[0];
	$target_version = $strings[1];
	if($start_version >= $db_version/100)
	{
		if($target_version <= $code_version)
		{
			print('Updating version: '.$dir.'<br>');
			$update_sql = file_get_contents('install/'.$dir.'/update.sql');
			$DB->exec($update_sql);
		}
	}
}

?>