<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Green' => array('Jadestadt'=>'Army','Schloss Gruenburg'=>'Fleet','Gruenheim'  =>'Army'),
		'Red'   => array('Rotheim'  =>'Army','Zinnoberburg'     =>'Fleet','Karminstadt'=>'Army'),
		'Black' => array('Westberg' =>'Army','Ostberg'          =>'Army' ,'Norterend'  =>'Army' ,'Sund'  =>'Army',
					     'Gawar'    =>'Army','Pirh'             =>'Army' ,'Abaun'      =>'Fleet','Helom'=>'Fleet')
	 );

	protected function isEnoughPlayers() {
		global $Game;
		return ( count($Game->Members->ByID) == 2 );
	}

	protected function userCountries() {
		global $Game, $DB;

		$userIDs=array();
		foreach($Game->Members->ByUserID as $userID=>$Member)
			$userIDs[] = $userID;

		shuffle($userIDs);

		$userCountries=array();
		$countryID=1;
		foreach($userIDs as $userID)
		{
			$userCountries[$userID] = $countryID;
			$countryID++;
		}
		
		$DB->sql_put("INSERT INTO wD_Members SET
			userID = 3, gameID = ".$Game->id.", orderStatus='None,Completed,Ready', bet = 0, timeLoggedIn = ".time());
		$Game->Members->load();

		$userCountries[3] = 3;

		return $userCountries;
	}
	 
}

?>
