<?php

/*
Changelog:
0.1: edited links between territories; edited maps
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class OctopusVariant extends WDVariant {
	public $id=40;
	public $mapID=40;
	public $name='Octopus';
	public $fullName='Octopus';
	public $description='Description of Octopus';
	public $author='Emmanuele Ravaioli (Tadar Es Darden)';
	public $adapter ='Emmanuele Ravaioli / Oliver Auth';
	public $version ='1.0.1';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Octopus';
		$this->variantClasses['adjudicatorPreGame'] = 'Octopus';

		// Fix a convoy-display error:
		$this->variantClasses['OrderInterface']     = 'Octopus';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1901);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1901);
		};';
	}
}

?>