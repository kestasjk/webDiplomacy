<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NorthSeaWarsVariant extends WDVariant {
	public $id         =73;
	public $mapID      =73;
	public $name       ='NorthSeaWars';
	public $fullName   ='NorthSeaWars';
	public $description='An economic conflict at the start of the first millenium';
	public $author     ='sqrg';
	public $adapter    ='sqrg';
	public $version    ='1';
	public $codeVersion='1.0';	
	
	public $countries=array('Briton','Roman','Frysian','Norse');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'NorthSeaWars';
		$this->variantClasses['adjudicatorPreGame'] = 'NorthSeaWars';
		$this->variantClasses['panelGameBoard']     = 'NorthSeaWars';
		$this->variantClasses['OrderInterface']     = 'NorthSeaWars';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2));
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2));
		};';
	}
}

?>
