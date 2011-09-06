<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Empire1on1Variant extends WDVariant {
	public $id         = 33;
	public $mapID      = 33;
	public $name       = 'Empire1on1';
	public $fullName   = 'Fall of the American Empire: Civil War!';
	public $description= '1 vs 1 version of the Fall of the American Empire variant, set up during the American Secession War';
	public $author     = 'Emmanuele Ravaioli and Andrew Newell (adapted from a map by Vincent Maus)';
	public $adapter    = 'Emmanuele Ravaioli / Oliver Auth';
	public $version    = '0.9.1';

	public $countries=array('Confederacy', 'Union');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Empire1on1';
		$this->variantClasses['processGame']        = 'Empire1on1';
		$this->variantClasses['processMember']      = 'Empire1on1';
		$this->variantClasses['processOrderBuilds'] = 'Empire1on1';
		$this->variantClasses['adjudicatorPreGame'] = 'Empire1on1';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 34;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1860);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1860);
		};';
	}
}

?>