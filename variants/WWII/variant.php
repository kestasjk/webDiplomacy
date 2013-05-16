<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class WWIIVariant extends WDVariant {
	public $id         = 87;
	public $mapID      = 87;
	public $name       ='WWII';
	public $fullName   = 'World War II';
	public $description= 'World War II variant by Synapse';
	public $adapter    = 'Synapse';
	public $version    = '1';
	public $codeVersion= '1.0.2';

	public $countries=array('France','Germany', 'Britain', 'Italy', 'Soviet Russia');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'WWII';
		$this->variantClasses['adjudicatorPreGame'] = 'WWII';
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 45;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1938);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1938);
		};';
	}
}

?>