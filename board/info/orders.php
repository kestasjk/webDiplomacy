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
 * Output the textual orders for the current game
 *
 * @package Board
 */

global $terrIDToName,$countryIDToName;
$terrIDToName=array();
$tabl=$DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
while(list($id,$name)=$DB->tabl_row($tabl))
	$terrIDToName[$id]=$name;

$countryIDToName=array();
foreach($Game->Variant->countries as $index=>$countryName)
	$countryIDToName[$index+1]=$countryName;


function orderIndex($title, $depth)
{
	static $indexCount, $lastDepth;

	if ( !isset($indexCount) )
	{
		$indexCount = 0;
		$lastDepth = 0;
	}

	if ( $lastDepth < $depth )
	{
		while( $lastDepth < $depth)
		{
			$lastDepth++;
			print '<ul>';
		}
	}
	elseif ( $lastDepth > $depth )
	{
		while( $lastDepth > $depth)
		{
			$lastDepth--;
			print '</ul>';
		}
	}

	if ( $title )
	{
		print '<li><a href="#index'.++$indexCount.'">'.$title.'</a></li>';

		return '<a name="index'.$indexCount.'"></a>';
	}
}

// People have got invalid territory IDs into the moves archive, this wil prevent it crashing the moves page
function tryGetTerritoryName($terrID) {
	global $terrIDToName;

	if( key_exists($terrID,$terrIDToName) )
		return $terrIDToName[$terrID];
	else
		return "???";
}
//TODO: Merge this code with the normal order output code
function outputOrderLogs(array $orders)
{
	global $terrIDToName,$countryIDToName;

	static $types;

	if ( !isset($types) )
	{
		$types = array(
				'Diplomacy'=>array('hold', 'move','support hold','support move','convoy'),
				'Retreats'=>array('retreat','disband'),
				'Unit-placement'=>array('build army','build fleet','wait','destroy')
			);
	}

	$buffer = '<ul>';

	foreach($types as $phase=>$orderTypes)
	{
               if ($phase == 'Diplomacy' ) {
                       $buffer .= '<li><strong>'.l_t($phase)."</strong></li>\n\t\t\t<ul>";
               } else {
                       $orderFound=0;
                       foreach($orderTypes as $t) {
                               if (array_key_exists($t, $orders)) {
                                       $orderFound=1;
                                       break;
                               }
                       }
                       if ($orderFound)
                               $buffer .= '<li>'.orderIndex(l_t($phase), 3).'<strong>'.l_t($phase)."</strong></li>\n\t\t\t<ul>";
                       else
                               continue;
               }

		foreach($orderTypes as $orderType)
		{
			if ( !isset($orders[$orderType]) ) continue;

			foreach($orders[$orderType] as $order)
			{
				$buffer .= '<li>';

                               if ($order['dislodged'] == 'Yes' || ($order['success'] == 'No' && $order['type'] != 'hold'))
                                       $buffer .= '<u>';       // underline failed orders

				if ( $phase == 'Retreats' )
				{
					switch($order['type'])
					{
						case 'retreat':
							$buffer .= l_t('The %s at %s retreat to %s',l_t($order['unitType']),l_t(tryGetTerritoryName($order['terrID'])),l_t(tryGetTerritoryName($order['toTerrID'])));
							break;
						case 'disband':
							$buffer .= l_t('The %s at %s disband',l_t($order['unitType']),l_t(tryGetTerritoryName($order['terrID'])));
					}
				}
				elseif ( $phase == 'Unit-placement' )
				{
					switch($order['type'])
					{
						case 'build army':
						case 'build fleet':
							$buffer .= l_t('Build %s at %s',($order['type']=='build army'?l_t('army'):l_t('fleet')),l_t(tryGetTerritoryName($order['terrID'])));
							break;
						case 'wait':
							$buffer .= l_t('Do not use build order');
							break;
						case 'destroy':
							$buffer .= l_t('Destroy the unit at %s',l_t(tryGetTerritoryName($order['terrID'])));
					}
				}
				else
				{
					$buffer .= l_t("The %s at %s %s",l_t($order['unitType']),l_t(tryGetTerritoryName($order['terrID'])),l_t($order['type'])).
						($order['toTerrID'] ? l_t(" to %s",l_t(tryGetTerritoryName($order['toTerrID']))) : '' ).
						($order['fromTerrID'] ? l_t(" from %s",l_t(tryGetTerritoryName($order['fromTerrID']))) : '').
						($order['viaConvoy'] == 'Yes' ? l_t(" via convoy") : '');
				}

                               $buffer .= '.';

                               if ($order['dislodged'] == 'Yes' || ($order['success'] == 'No' && $order['type'] != 'hold')) {
                                       $buffer .= '</u>';

                                       if ($order['success'] == 'No') {
                                               /*
                                               if ($order['type'] == 'move')   // not sure its good idea to say 'bounce'
                                                       $buffer .= ' (bounce)';   // when you don't really know cause of failure
                                               else if ($order['type'] == 'retreat')
                                                       $buffer .= ' (fail)';
                                               else if ($order['type'] != 'hold')// supports and convoy
                                                       $buffer .= ' (cut)';
                                               */
                                               if ($order['type'] != 'hold')
                                                       $buffer .= ' ('.l_t('fail').')';
                                       }

                                       if ($order['dislodged'] == 'Yes')
                                               $buffer .= ' ('.l_t('dislodged').')';
                               }

                               $buffer .= '</li>';
			}
		}

		$buffer .= '</ul>';
	}
	$buffer .= '</ul>';

	return $buffer;
}

print '<h3>'.l_t('Order history').'</h3>';
print '<div class="variant'.$Game->Variant->name.'">';

$tabl = $DB->sql_tabl("SELECT turn, countryID, LOWER(unitType) as unitType, LOWER(type) as type, terrID, toTerrID, fromTerrID, viaConvoy, success, dislodged
		FROM wD_MovesArchive WHERE gameID = ".$Game->id."
		ORDER BY turn DESC, countryID ASC");

$lastTurn = -1;
$lastCountryID = -1;
$buffer = '';
while ( $row = $DB->tabl_hash($tabl) )
{
	if ( $row['countryID'] != $lastCountryID )
	{
		if ( isset($orderLogs) )
			$buffer .= outputOrderLogs($orderLogs);

		$orderLogs = array();

		if ( $row['turn'] != $lastTurn )
		{
			if ( $lastTurn != -1 ) $buffer .= '</p><div class="hr"></div>';

			$buffer .= "<h4>";

			$buffer .= orderIndex($Game->datetxt($row['turn']), 1);

			$buffer .= $Game->datetxt($row['turn']).' <a href="map.php?gameID='.$Game->id.'&largemap=on&turn='.$row['turn'].'">
					<img src="'.l_s('images/historyicons/external.png').'" alt="'.l_t('Large map').'"
						title="'.l_t('This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough.').
						'" /></a>:</h4>';
			$buffer .= '<p>';

			$lastTurn = $row['turn'];
		}

		$buffer .= orderIndex(l_t($countryIDToName[$row['countryID']]), 2);

               $buffer .= '<strong><span class="country'.$row['countryID'].'">'.l_t($countryIDToName[$row['countryID']])."</span>:</strong><br />";
		$lastCountryID = $row['countryID'];
	}

	if ( !isset($orderLogs[$row['type']]) )
		$orderLogs[$row['type']] = array();

	$orderLogs[$row['type']][] = $row;
}

if( isset($orderLogs))
	$buffer .= outputOrderLogs($orderLogs);
else
	print '<p>'.l_t('No order logs to output').'</p>';

orderIndex('',0);

print $buffer;

print '</p></div>';

?>
