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

require_once(l_r('objects/game.php'));

/**
 * Recalculates and resets the chances each user has of being each country,
 * based on their past countries. Should only need to be used after updating
 * from 0.8x to 0.9x
 *
 * @package Admin
 */

print l_t("Country-balance chance generation script, to be run on 0.82-0.90 update.").
	l_t("Creating table")."<br />";flush();

$DB->sql_put("CREATE TABLE IF NOT EXISTS `Chances` (
	`id` mediumint(8) unsigned NOT NULL default '0',
	`ChanceEngland` float NOT NULL,
	`ChanceFrance` float NOT NULL,
	`ChanceItaly` float NOT NULL,
	`ChanceGermany` float NOT NULL,
	`ChanceAustria` float NOT NULL,
	`ChanceTurkey` float NOT NULL,
	`ChanceRussia` float NOT NULL
) ENGINE=MyISAM");

$DB->sql_put("DELETE FROM Chances");

function balanceChances(array $chances)
{
	$sum = 0.0;

	foreach($chances as $countryID=>$chance)
	{
		if ( $chance < 0.01 )
			$chance = ($chances[$countryID] = 0.01);

		$sum += $chance;
	}

	foreach($chances as $countryID=>$chance)
		$chances[$countryID] *= 1.0/$sum;

	return $chances;
}

function countryChances($userID)
{
	global $DB, $Game;

	$chances=array();
	for($countryID=1; $countryID<=count($Game->Variant->countries); $countryID++)
		$chances[$countryID]=1.0/count($Game->Variant->countries);

	$tabl = $DB->sql_tabl(
		"SELECT countryID FROM wD_Members
		WHERE userID = ".$userID." AND countryID > 0
		ORDER BY id ASC"
	);
	$i=0;
	while(list($curCountryID) = $DB->tabl_row($tabl))
	{
		$i++;

		$chances[$curCountryID] /= 2.0;

		$chances = balanceChances($chances);
	}

	if($i==0)
		return false;
	else
		return $chances;
}

print "Inserting data";flush();

$DB->sql_put("BEGIN");

$startINSERT = "INSERT INTO Chances (id,Chance";
$startINSERT .= implode(", Chance",$Game->Variant->countries);
$startINSERT .= ")\n\tVALUES\n\t";

$sqlBuf = $startINSERT;
$i=0;
$tabl = $DB->sql_tabl("SELECT id FROM wD_Users");
$lastLine="";
while(strlen($lastLine) || ( list($userID) = $DB->tabl_row($tabl)) )
{
	// If its the first lastLine is empty, if its the last userID is empty
	if(strlen($lastLine))
	{
		$sqlBuf .= $lastLine;
		$lastLine="";

		// 100th row, or the last user
		if((++$i%100)==0 || !(isset($userID) && $userID ) )
		{
			$DB->sql_put($sqlBuf);
			$sqlBuf = $startINSERT;
			print ".";flush();
		}
		else
			$sqlBuf .= ",";
	}

	if( isset($userID) && $userID )
	{
		if(!($chances = countryChances($userID))) continue;

		$sql=array($userID);
		foreach($chances as $countryID=>$chance)
		{
			$sql[] = number_format(round($chance,3),3);
		}

		$lastLine="(".implode(",",$sql).")";

		unset($userID);
	}
}
print "<br />";flush();

print l_t("Indexing")."<br />";flush();
$DB->sql_put("ALTER TABLE `Chances` ADD INDEX ( `id` )");

print l_t("Putting chances table data into users table")."<br />";flush();

$sqlBuf= "UPDATE wD_Users u INNER JOIN Chances c SET ";
$first=true;
foreach($Game->Variant->countries as $c)
{
	if($first) $first=false;
	else $sqlBuf.=", ";

	$sqlBuf.="u.Chance".$c." = c.Chance".$c;
}
$sqlBuf.= " WHERE u.id = c.id";
$DB->sql_put($sqlBuf);

print l_t("Deleting chances table");flush();
$DB->sql_put("DROP TABLE Chances");

$DB->sql_put("COMMIT");

print l_t("Done");flush();

?>