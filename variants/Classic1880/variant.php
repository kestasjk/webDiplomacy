<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Classic1880Variant extends WDVariant {
	public $id=34;
	public $mapID=34;
	public $name='Classic1880';
	public $fullName='1880';
	public $description='The standard Diplomacy map of Europe, expanded to play area for a different game.';
	public $author='airborne';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		// drawMap extended for country-colors and loading the classic map images
		$this->variantClasses['drawMap'] = 'Classic1880';

		/*
		 * adjudicatorPreGame extended to add fair country-balancing, replacing the
		 * default random allocation for classic map games.
		 */
		$this->variantClasses['adjudicatorPreGame'] = 'Classic1880';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1880);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1880);
		};';
	}
}

?>