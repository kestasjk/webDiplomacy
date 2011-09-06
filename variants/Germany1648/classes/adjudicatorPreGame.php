<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Germany1648Variant_adjudicatorPreGame extends adjudicatorPreGame {

	// Set the staring units:
	protected $countryUnits = array(
		'Austrian Habsburg'		=> array('Archduchy of Austria'  =>'Army','Kingdom of Bohemia'  =>'Army','Breisgau'  =>'Army'),
		'Spanish Habsburg'		=> array('Franche-Comte'  =>'Army','Far Spanish Netherlands'   =>'Army','Duchy of Luxemburg'   =>'Army'),
		'Wettin'			=> array('Electorate of Saxony'  =>'Army','Duchy of Saxony'  =>'Army','Lisatia'  =>'Army'),
		'Bavarian Wittelsbach'		=> array('Electorate of Bavaria'  =>'Army','Bishopric of Regensburg'  =>'Army','Memmingen'  =>'Army'),
		'Palatinate Wittelsbach'	=> array('Upper Electoral Palatinate'  =>'Army','Principality of Neuburg'  =>'Army','Duchy of Julich'  =>'Army','Western Electoral Palatinate'  =>'Army'),
		'Hohenzollern'			=> array('Electorate of Brandenburg'  =>'Army','County of Mark'  =>'Army','Margraviate of Ansbach'  =>'Army','Hohenzollern'  =>'Army'),
		'Ecclesiastic Lands'		=> array('Archbishopric of Trier'  =>'Army','Archbishopric of Mainz'  =>'Army','Archbishopric of Salzburg'  =>'Army','Bishopric of Munster'  =>'Army'),
		'Free Imperial Cities'		=> array('Lubeck #1'  =>'Army','Hamburg #2'  =>'Army','Bremen #3'  =>'Army','Muhlhausen #4'  =>'Army','Cologne #5'  =>'Army','Aachen #6'  =>'Army','Frankfurt am Main #7'  =>'Army','Worms #8'  =>'Army','Speyer #9'  =>'Army','Strassburg #10'  =>'Army','Ravensburg #11'  =>'Army','Ulm #12'  =>'Army','Augsburg #13'  =>'Army','Nuremberg #14'  =>'Army','Regensburg #15'  =>'Army')
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
}

?>
