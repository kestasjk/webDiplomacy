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
 * A graph of the turn by turn share of supply centers.
 *
 * @package Board
 */

print '<h3>Graph</h3>';

$scCountsByTurn=array();
for($i=1;$i<$Game->turn;$i++)
{
	$tabl=$DB->sql_tabl("SELECT ts.countryID, COUNT(ts.countryID) FROM wD_TerrStatusArchive ts INNER JOIN wD_Territories t ON ( t.id=ts.terrID AND t.supply='Yes' AND t.coastParentID=t.id AND t.mapID=".$Variant->mapID." ) WHERE ts.gameID=".$Game->id." AND ts.turn=".$i." GROUP BY ts.countryID");
	$scCountsByCountryID=array();
	while(list($countryID,$scCount)=$DB->tabl_row($tabl))
		$scCountsByCountryID[$countryID]=$scCount;

	$scCountsByTurn[$i]=$scCountsByCountryID;
}

foreach( $scCountsByTurn as $turn=>$scCountsByCountryID) {
	$turnSCTotal=0;
	foreach($scCountsByCountryID as $countryID=>$scCount)
	{
		//if($countryID<) continue;
		$turnSCTotal+=$scCount;
	}

	if( $turnSCTotal==0 )
	{
		unset($scCountsByTurn[$turn]);
		break;
	}

	$percentLeft=100;
	foreach($scCountsByCountryID as $countryID=>$scCount)
	{
		$percent=floor(100.0*($scCount/$turnSCTotal));

		//$taken=$percentLeft*($percent/100.0)

		if( $percent==0 ) {
			if( $percentLeft>0 ) {
				$percentLeft--;
				$percent=1;
				continue;
			}
			else
				break;
		}

		$percentLeft-=$percent;

		$scCountsByTurn[$turn][$countryID] = $percent;
	}
}

$scRatiosByTurn=$scCountsByTurn;
unset($scCountsByTurn);

if( count($scRatiosByTurn)<3 ) {
	print 'Game too new to graph.';
	return;
}

print '<div class="variant'.$Variant->name.' boardGraph" style="width:auto">';
foreach( $scRatiosByTurn as $turn=>$scRatiosByCountryID)
{
	print '<div class="boardGraphTurn" style="width:auto">';//500px">';
	foreach($scRatiosByCountryID as $countryID=>$scRatio)
	{
		if( $scRatio<1 ) continue;

		print '<div class="boardGraphTurnCountry occupationBar'.$countryID.'" '.
			'style="text-align:center; font-size:10pt; font-weight:bold; overflow:hidden;'.
			'float:left;width:'.$scRatio.'%">'.$scRatio.'%</div>';

	}
	print '<div style="clear:both"></div>';
	print '</div>';
}

print '</div>';


?>