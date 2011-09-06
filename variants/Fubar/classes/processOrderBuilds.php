<?php

class FubarVariant_processOrderBuilds extends processOrderBuilds {

	// You can set default builds here, so the game is not screwed if someone misses his build
	protected $countryUnits = array(
		'Fatflap'             => array('Squirrel'=>'Fleet','Turtle'=>'Army','Lizard'=>'Army','Zebra'=>'Army','Rat'=>'Army','Chicken'=>'Army','Monkey'=>'Army','Cow'=>'Army','Lion'=>'Army','Fish'=>'Army'),
		'Howdoileavethisgame' => array('Sheep'=>'Army','Bat'=>'Army'),	
		'timmy1999'           => array('Kangaroo'=>'Army','Snake'=>'Army'),
		'Sh1tn00b'            => array('Pig'=>'Army','Eagle'=>'Army'),
		'oMgYoUrAsLuT'        => array('Ostrich'=>'Army','Platypus'=>'Army'),
		'multi_152'           => array('Meerkat'=>'Army','Elephant'=>'Army')
	);

	public function create()
	{
		global $DB, $Game;
		if ($Game->turn == 0) {
			// Custom start
			$terrIDByName = array();
			$tabl = $DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
			while(list($id, $name) = $DB->tabl_row($tabl))
				$terrIDByName[$name]=$id;

			$UnitINSERTs = array();
			foreach($this->countryUnits as $countryName => $params)
			{
				$countryID = $Game->Variant->countryID($countryName);

				foreach($params as $terrName=>$unitType)
				{
					$terrID = $terrIDByName[$terrName];
					$unitType = "Build " . $unitType;
					$UnitINSERTs[] = "(".$Game->id.", ".$countryID.", '".$terrID."', '".$unitType."')"; // ( gameID, countryID, terrID, type )
				}
			}
			$DB->sql_put(
				"INSERT INTO wD_Orders ( gameID, countryID, toTerrID, type )
				VALUES ".implode(', ', $UnitINSERTs)
			);		
		} else {
			// Build anywhere
			$newOrders = array();
			foreach($Game->Members->ByID as $Member )
			{
				$difference = 0;
				if ( $Member->unitNo > $Member->supplyCenterNo )
				{
					$difference = $Member->unitNo - $Member->supplyCenterNo;
					$type = 'Destroy';
				}
				elseif ( $Member->unitNo < $Member->supplyCenterNo )
				{
					$difference = $Member->supplyCenterNo - $Member->unitNo;
					$type = 'Build Army';
				}
	
				for( $i=0; $i < $difference; ++$i )
				{
					$newOrders[] = "(".$Game->id.", ".$Member->countryID.", '".$type."')";
				}
			}
	
			if ( count($newOrders) )
			{
				$DB->sql_put("INSERT INTO wD_Orders
								(gameID, countryID, type)
								VALUES ".implode(', ', $newOrders));
			}
	
		}
		
	}
}