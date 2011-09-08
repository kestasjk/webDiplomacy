<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class OrderArchiv {	

	public $terrIDToName=array();
	public $countryIDToName=array();

	public $orderIndex;
	public $orderHTML;

	public $types = array(
		'Diplomacy'=>array('hold', 'move','support hold','support move','convoy'),
		'Retreats'=>array('retreat','disband'),
		'Unit-placement'=>array('build army','build fleet','wait','destroy')
	);

	public function __construct()
	{
		global $DB, $Game;
		
		$tabl=$DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
		while(list($id,$name)=$DB->tabl_row($tabl))
			$this->terrIDToName[$id]=$name;

		foreach($Game->Variant->countries as $index=>$countryName)
			$this->countryIDToName[$index+1]=$countryName;
	}
	
	function BuildOrderIndex($title, $depth)
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
				$this->orderIndex .= '<ul>';
			}
		}
		elseif ( $lastDepth > $depth )
		{
			while( $lastDepth > $depth)
			{
				$lastDepth--;
				$this->orderIndex .= '</ul>';
			}
		}

		if ( $title )
		{
			$this->orderIndex .= '<li><a href="#index'.++$indexCount.'">'.$title.'</a></li>';
			return '<a name="index'.$indexCount.'"></a>';
		}
	}

	public function OutputOrderIndex()
	{
		return $this->orderIndex;
	}
	
	public function OutputOrders()
	{
		return $this->orderHTML;
	}
	
	public function OutputOrder($order)
	{
		$buffer = '<li>';

		if ($order['dislodged'] == 'Yes' || ($order['success'] == 'No' && $order['type'] != 'hold'))
			$buffer .= '<u>';       // underline failed orders

		switch($order['type'])
		{
			case 'retreat':
				$buffer .= 'The '.$order['unitType']." at ".$this->terrIDToName[$order['terrID']]." retreat to ".$this->terrIDToName[$order['toTerrID']];
				break;
			case 'disband':
				$buffer .= 'The '.$order['unitType']." at ".$this->terrIDToName[$order['terrID']]." disband";
			case 'build army':
			case 'build fleet':
				$buffer .= 'Build '.($order['type']=='build army'?'army':'fleet').' at '.$this->terrIDToName[$order['terrID']];
				break;
			case 'wait':
				$buffer .= 'Do not use build order';
				break;
			case 'destroy':
				$buffer .= 'Destroy the unit at '.$this->terrIDToName[$order['terrID']];
			default:
				$buffer .= "The ".$order['unitType']." at ".$this->terrIDToName[$order['terrID']]." ".$order['type'].
					($order['toTerrID'] ? " to ".$this->terrIDToName[$order['toTerrID']] : '' ).
					($order['fromTerrID'] ? " from ".$this->terrIDToName[$order['fromTerrID']] : '').
					($order['viaConvoy'] == 'Yes' ? " via convoy" : '');
		}

		$buffer .= '.';

		if ($order['dislodged'] == 'Yes' || ($order['success'] == 'No' && $order['type'] != 'hold'))
		{
			$buffer .= '</u>';

			if (($order['success'] == 'No') && ($order['type'] != 'hold'))
				$buffer .= ' (fail)';

			if ($order['dislodged'] == 'Yes')
				$buffer .= ' (dislodged)';
		}

		$buffer .= '</li>';

		return $buffer;

	}

	public function outputOrderLogs(array $orders)
	{

		$buffer = '<ul>';

		foreach($this->types as $phase=>$orderTypes)
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
					$buffer .= '<li>'.$this->BuildOrderIndex($phase, 3).'<strong>'.$phase."</strong></li>\n\t\t\t<ul>";
				else
				   continue;
			}

			foreach($orderTypes as $orderType)
			{
				if ( !isset($orders[$orderType]) ) continue;

				foreach($orders[$orderType] as $order)
				{
					$buffer .= $this->OutputOrder($order);
				}
			}

			$buffer .= '</ul>';
		}
		$buffer .= '</ul>';

		return $buffer;
	}
	
	function outputNewTurn($turn)
	{
		global $DB, $Game;

		$buffer = "<h4>" . $this->BuildOrderIndex($Game->datetxt($turn), 1);

		$buffer .= $Game->datetxt($turn).' <a href="map.php?gameID='.$Game->id.'&largemap=on&turn='.$turn.'">
			<img src="images/historyicons/external.png" alt="Large map"
			title="This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough."
			/></a>:</h4>';
		$buffer .= '<p>';
		
		return $buffer;
	}
	
	function buildLogs()
	{
		global $DB, $Game;

		$tabl = $DB->sql_tabl("SELECT turn, countryID, LOWER(unitType) as unitType, LOWER(type) as type, terrID, toTerrID, fromTerrID, viaConvoy, success, dislodged
				FROM wD_MovesArchive WHERE gameID = ".$Game->id."
				ORDER BY turn DESC, countryID ASC");

		$lastTurn = -1;
		$lastCountryID = -1;
		while ( $row = $DB->tabl_hash($tabl) )
		{
			if ( $row['countryID'] != $lastCountryID )
			{

				if ( isset($orderLogs) )
					$this->orderHTML .= $this->outputOrderLogs($orderLogs);

				$orderLogs = array();

				if ( $row['turn'] != $lastTurn )
				{
					if ( $lastTurn != -1 ) $this->orderHTML .= '</p><div class="hr"></div>';

					$this->orderHTML .= $this->outputNewTurn($row['turn']);

					$lastTurn = $row['turn'];
				}

				$this->orderHTML .= $this->BuildOrderIndex($this->countryIDToName[$row['countryID']], 2);

				$this->orderHTML .= '<strong><span class="country'.$row['countryID'].'">'.$this->countryIDToName[$row['countryID']]."</span>:</strong><br />";

				$lastCountryID = $row['countryID'];
			}

			if ( !isset($orderLogs[$row['type']]) )
				$orderLogs[$row['type']] = array();

			$orderLogs[$row['type']][] = $row;
			
		}

		if( isset($orderLogs))
			$this->orderHTML .= $this->outputOrderLogs($orderLogs);
		else
			$this->orderIndex .= '<p>No order logs to output</p>';

		$this->orderHTML .= $this->BuildOrderIndex('',0);

	}
	
	function OutputHTML()
	{
		$this->buildLogs();
		return $this->OutputOrderIndex() . $this->OutputOrders();
	}
	
}	

print '<h3>Order history</h3>';
print '<div class="variant'.$Game->Variant->name.'">';

$OA=$Game->Variant->OrderArchiv();
print '<table>'.$OA->OutputHTML().'</table>';
print '</div>';

?>
