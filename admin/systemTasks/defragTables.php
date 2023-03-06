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

/**
 * This file defragments the tables which may reach high ID levels. Once they go over
 * 2^32-1 PHP can't keep the ID in memory, automatically starts using floats, and the
 * imprecision causes lots of bizarre problems.
 * 
 * The server must not be in use when this is run, but luckily it should never have 
 * to be run except on the largest servers after many years (unless lots of DATC testing
 * is causing an artificially high number of orders etc)
 * 
 * @package Admin
 */

if ( !$User->type['Admin'] )
	die(l_t('Admins only'));
	
ini_set('memory_limit',"12M");
ini_set('max_execution_time','60');

header('Content-Type: text/plain');

if( !defined('RUNNINGFROMCLI')) ob_end_flush();

print l_t('Defragmenting')."\n"; flush();

$tableNames = array('Moves','Orders','TerrStatus','Units');


foreach($tableNames as $tableName)
{
	print l_t('Defragmenting %s',$tableName)."\n"; flush();
	
	$DB->sql_put("BEGIN");
	
	list($max) = $DB->sql_row("SELECT MAX(id) FROM wD_".$tableName);
	
	$tabl = $DB->sql_tabl("SELECT id FROM wD_".$tableName." ORDER BY id ASC");
	
	$i=1;
	while( list($id) = $DB->tabl_row($tabl) )
	{
		if ( ( $i % 100 ) == 0 )
		{
			print "\t".$id."(/".$max.") -> ".$i."\n"; flush();
		}
		
		if ( $i != $id )
		{
			$DB->sql_put("UPDATE wD_".$tableName." SET id = ".$i." WHERE id = ".$id);
			
			if ( $tableName == 'Units' )
			{
				$DB->sql_put("UPDATE wD_TerrStatus SET occupyingUnitID = ".$i." WHERE occupyingUnitID = ".$id);
				
				$DB->sql_put("UPDATE wD_TerrStatus SET retreatingUnitID = ".$i." WHERE retreatingUnitID = ".$id);
				
				$DB->sql_put("UPDATE wD_Orders SET unitID = ".$i." WHERE unitID = ".$id);
			}
		}
		
		$i++;
	}
	
	$DB->sql_put("COMMIT");
	
	$DB->sql_put("ALTER TABLE wD_".$tableName." AUTO_INCREMENT = ".$i);
	
	print $tableName.' done: '.$max.'->'.$i."\n"; flush();
}

$DB->sql_put("COMMIT");

print l_t("Done")."\n";

?>