<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class HabelyaVariant extends WDVariant {
	public $id         =68;
	public $mapID      =68;
	public $name       ='Habelya';
	public $fullName   ='Habelya';
	public $description='The lands of Habelya and its mighty empires have been struck by a great earthquake. Rise now in this new world to regain power for your nation. Command your armies in this fight to reclaim the old world for your own.';
	public $author     ='King Atom';
	public $adapters   ='King Atom / Oliver Auth';
	public $homepage   ='';
	public $version    ='1';
	public $codeVersion='1.0';	
	
	public $countries=array('Socialist Glock','Kingdom of Saltz','The Hacklers','Trylika','Ruins of Holgii','Gernavia','Old Bramia','Elenian Empire');	

	public function __construct() {
		parent::__construct();

		// Setup
		$this->variantClasses['drawMap']            = 'Habelya';
		$this->variantClasses['adjudicatorPreGame'] = 'Habelya';

		// Write the CountryName in the chatbox
		$this->variantClasses['Chatbox']            = 'Habelya';

	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 527);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 527);
		};';
	}
}

?>