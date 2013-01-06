<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class EnlightenmentVariant extends WDVariant {
	public $id         =76;
	public $mapID      =76;
	public $name       ='Enlightenment';
	public $fullName   ='Enlightenment & Succession';
	public $description='Europe in 1700';
	public $author     ='airborne';
	public $adapter    ='airborne';
	public $version    ='1';
	public $codeVersion='1.0.15';	
	
	public $countries=array('Russia', 'Venice', 'Austria', 'France', 'Prussia', 'Turkey', 'England', 'Poland', 'Sweden', 'Spain');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Enlightenment';
		$this->variantClasses['adjudicatorPreGame'] = 'Enlightenment';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Enlightenment';		
		$this->variantClasses['processOrderBuilds'] = 'Enlightenment';
		$this->variantClasses['userOrderBuilds']    = 'Enlightenment';

	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 29;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1701);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1701);
		};';
	}
}

?>