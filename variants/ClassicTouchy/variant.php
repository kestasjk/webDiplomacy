<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicTouchyVariant extends WDVariant {
	public $id         =64;
	public $mapID      =64;
	public $name       ='ClassicTouchy';
	public $fullName   ='Classic Touchy';
	public $author     ='airborne';
	public $adapter    ='airborne';
	public $codeVersion='1.0.2';	
	
	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'ClassicTouchy';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicTouchy';

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