<?php

// put this in board/info

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Output the textual orders for the current game
 *
 * @package Board
 */

class OrderArchiv {	

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

	//TODO: Merge this code with the normal order output code
	public function outputOrderLogs(array $orders)
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
			   $buffer .= '<li><strong>'.$phase."</strong></li>\n\t\t\t<ul>";
			} else {
				$orderFound=0;
				foreach($orderTypes as $t) {
					if (array_key_exists($t, $orders)) {
						$orderFound=1;
						break;
					}
				}
				if ($orderFound)
					$buffer .= '<li>'.$this->orderIndex($phase, 3).'<strong>'.$phase."</strong></li>\n\t\t\t<ul>";
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
								$buffer .= 'The '.$order['unitType']." at ".$terrIDToName[$order['terrID']]." retreat to ".$terrIDToName[$order['toTerrID']];
								break;
							case 'disband':
								$buffer .= 'The '.$order['unitType']." at ".$terrIDToName[$order['terrID']]." disband";
						}
					}
					elseif ( $phase == 'Unit-placement' )
					{
						switch($order['type'])
						{
							case 'build army':
							case 'build fleet':
								$buffer .= 'Build '.($order['type']=='build army'?'army':'fleet').' at '.$terrIDToName[$order['terrID']];
								break;
							case 'wait':
								$buffer .= 'Do not use build order';
								break;
							case 'destroy':
								$buffer .= 'Destroy the unit at '.$terrIDToName[$order['terrID']];
						}
					}
					else
					{
						$buffer .= "The ".$order['unitType']." at ".$terrIDToName[$order['terrID']]." ".$order['type'].
							($order['toTerrID'] ? " to ".$terrIDToName[$order['toTerrID']] : '' ).
							($order['fromTerrID'] ? " from ".$terrIDToName[$order['fromTerrID']] : '').
							($order['viaConvoy'] == 'Yes' ? " via convoy" : '');
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
														   $buffer .= ' (fail)';
										   }

										   if ($order['dislodged'] == 'Yes')
												   $buffer .= ' (dislodged)';
								   }

								   $buffer .= '</li>';
				}
			}

			$buffer .= '</ul>';
		}
		$buffer .= '</ul>';

		return $buffer;
	}

}	


global $terrIDToName,$countryIDToName;

$terrIDToName=array();
$tabl=$DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
while(list($id,$name)=$DB->tabl_row($tabl))
	$terrIDToName[$id]=$name;

$countryIDToName=array();
foreach($Game->Variant->countries as $index=>$countryName)
	$countryIDToName[$index+1]=$countryName;

$ViewArchiv=$Game->Variant->OrderArchiv();

print '<h3>Order history</h3>';
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
			$buffer .= $ViewArchiv->outputOrderLogs($orderLogs);

		$orderLogs = array();

		if ( $row['turn'] != $lastTurn )
		{
			if ( $lastTurn != -1 ) $buffer .= '</p><div class="hr"></div>';

			$buffer .= "<h4>";

			$buffer .= $ViewArchiv->orderIndex($Game->datetxt($row['turn']), 1);

			$buffer .= $Game->datetxt($row['turn']).' <a href="map.php?gameID='.$Game->id.'&largemap=on&turn='.$row['turn'].'">
					<img src="images/historyicons/external.png" alt="Large map"
						title="This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough."
					/></a>:</h4>';
			$buffer .= '<p>';

			$lastTurn = $row['turn'];
		}

		$buffer .= $ViewArchiv->orderIndex($countryIDToName[$row['countryID']], 2);

		$buffer .= '<strong><span class="country'.$row['countryID'].'">'.$countryIDToName[$row['countryID']]."</span>:</strong><br />";

		$lastCountryID = $row['countryID'];
	}

	if ( !isset($orderLogs[$row['type']]) )
		$orderLogs[$row['type']] = array();

	$orderLogs[$row['type']][] = $row;
}

if( isset($orderLogs))
	$buffer .= $ViewArchiv->outputOrderLogs($orderLogs);
else
	print '<p>No order logs to output</p>';

$ViewArchiv->orderIndex('',0);

print $buffer;

print '</p></div>';

?>
