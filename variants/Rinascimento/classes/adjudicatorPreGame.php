<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_adjudicatorPreGame extends adjudicatorPreGame {

	// Set the staring units:
	protected $countryUnits = array(
		'Ferrara'=> array('Ferrara'=>'Army' ),
		'Pisa'   => array('Pisa'   =>'Fleet'),
		'Siena'  => array('Siena'  =>'Army' ),
		'Firenze'=> array('Firenze'=>'Army' ,'Arezzo'     =>'Army'),
		'French' => array('Annecy' =>'Army' ,'Domodossola'=>'Army'),
		'Genova' => array('Genova' =>'Fleet','Ajaccio'    =>'Fleet'),
		'Savoia' => array('Torino' =>'Army' ,'Chambery'   =>'Army'),
		'Milano' => array('Milano' =>'Army' ,'Pavia'      =>'Army','Piacenza'=>'Army'),
		'Napoli' => array('Napoli' =>'Army' ,'Amalfi' =>'Fleet', 'Palermo'=>'Fleet','Brindisi'   =>'Fleet'),
		'Turkish'=> array('East Gateway'  =>'Fleet' ,'Outer Ionian Sea' =>'Fleet','Eastern Mediterranean Sea' =>'Fleet','Arcipelago di Spalato' =>'Fleet'),
		'Venezia'=> array('Venezia'       =>'Fleet' ,'Spalato'          =>'Fleet','Verona'                    =>'Army' ,'Brescia'                =>'Army'),
		'Stato della Chiesa' => array('ROMA'=>'Army','Bologna'          =>'Army','Benevento'                  =>'Army' ,'Perugia'                =>'Army'),
		'Impartial'=> array('Geneve'=>'Army','Trieste'=>'Army','Trento'=>'Army')
	);
	
	// remove the "neutral" player bevore the check
	protected function isEnoughPlayers() {
		global $Game;
		
		$a=array_pop($Game->Variant->countries);
		$ret=parent::isEnoughPlayers();
		array_push($Game->Variant->countries,$a);
		
		return $ret;
	}

	// add 1 player (USerID=3 => Civil disorder system account) as "neutral" forces player
	protected function userCountries() {
		global $Game, $DB;
		$ret=parent::userCountries();
		$DB->sql_put("INSERT INTO wD_Members SET
			userID = 3, gameID = ".$Game->id.", orderStatus='None,Completed,Ready', bet = 0, timeLoggedIn = ".time());
		$Game->Members->load();
		$ret[3] = count($Game->Variant->countries);
		return $ret;
	}

	// Save the UnitID from the Unit in Benevento to prevent "move" commands
	function adjudicate() {
		global $DB, $Game;
		parent::adjudicate();
		$DB->sql_put(
			"INSERT INTO wD_Notices (toUserID,fromID,text,linkName) 
				SELECT 3,GameID,occupyingUnitID,'Variant-Data'
				FROM wD_TerrStatus WHERE terrID=83 AND GameID=".$Game->id);
	}	
}

?>