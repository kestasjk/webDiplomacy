<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DutchRevoltVariant extends WDVariant {
	public $id         ='32';
	public $mapID      ='32';
	public $name       ='DutchRevolt';
	public $fullName   ='The Dutch Revolt';
	public $description='The Diplomacy map of the Low Countries halfway the 16th century.';
	public $author     ='sqrg (with help from Oli)';
	public $version = '2.0.1';

	public $countries=array('England', 'France', 'Spain', 'Munster', 'Holland');

	public function __construct() {
		parent::__construct();

		// Start-Options for the game:
		$this->variantClasses['adjudicatorPreGame'] = 'DutchRevolt';
		// Custom Colors and map-options:
		$this->variantClasses['drawMap']            = 'DutchRevolt';
		// Start with a build phase:
		$this->variantClasses['processGame']        = 'DutchRevolt';
		// 1st Buildphase has special unit-placing:
		$this->variantClasses['OrderInterface']     = 'DutchRevolt';
		$this->variantClasses['processOrderBuilds'] = 'DutchRevolt';
		$this->variantClasses['userOrderBuilds']    = 'DutchRevolt';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return (floor($turn*5) + 1555);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return (Math.floor(turn*5) + 1555);
		};';
	}
}

?>
