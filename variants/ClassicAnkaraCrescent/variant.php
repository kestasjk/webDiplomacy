<?php

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * The default "classic" Diplomacy; Europe etc.
 */
class ClassicAnkaraCrescentVariant extends WDVariant {
	public $id=90;
	public $mapID=90;
	public $name='ClassicAnkaraCrescent';
	public $fullName='Classic - Ankara Crescent';
	public $description='A rule-change variant which uses the Classic map, designed to encapsulate the chaos of the legendary forum game of Ankara Crescent.';
	public $author='Captainmeme';
	public $adapter    = 'Captainmeme';
	public $version    = '1';
	public $codeVersion= '1.0';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'ClassicAnkaraCrescent';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicAnkaraCrescent';
		
		// Convoy-Fix
		$this->variantClasses['OrderInterface']     = 'ClassicAnkaraCrescent';
		$this->variantClasses['userOrderDiplomacy'] = 'ClassicAnkaraCrescent'; 
		
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