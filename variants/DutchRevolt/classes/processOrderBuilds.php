<?php

class DutchRevoltVariant_processOrderBuilds extends processOrderBuilds {

	protected $countryUnits = array(
		'England'=> array('North English Coast'=>'Fleet','South English Coast'=>'Fleet'),
		'France' => array('Arras'              =>'Army' ,'Valery sur Somme'   =>'Army' ),
		'Spain'  => array('Luxembourg'         =>'Army' ,'Trier'              =>'Army' ),
		'Munster'=> array('Bocholt'            =>'Army' ,'Lingen'             =>'Fleet'),
		'Holland'=> array('Amsterdam'          =>'Army' ,'Utrecht'            =>'Army' )
	);

	public function create()
	{
		global $DB, $Game;
		if ($Game->turn == 0) {

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
			parent::create();
		}
		
	}
}