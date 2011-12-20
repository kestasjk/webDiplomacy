<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RatWarsVariant extends WDVariant {
	public $id         =65;
	public $mapID      =65;
	public $name       ='RatWars';
	public $fullName   ='Rat Wars';
	public $description='Rat Armies Battle it out';
	public $author     ='kaner406';
	public $adapter    ='kaner406 / Oliver Auth';
	public $version    ='1';
	public $codeVersion='1.0';	
	
	public $countries=array('Dead Rabbits','Plug Uglies','Shirt Tails','Hell-Cats');	

	public function __construct() {
		parent::__construct();

		// Basic gamesetup
		$this->variantClasses['drawMap']            = 'RatWars';
		$this->variantClasses['adjudicatorPreGame'] = 'RatWars';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'RatWars';		
		$this->variantClasses['processOrderBuilds'] = 'RatWars';
		$this->variantClasses['userOrderBuilds']    = 'RatWars';
		
		// Start with a build phase:
		$this->variantClasses['adjudicatorPreGame'] = 'RatWars';
		$this->variantClasses['processGame']        = 'RatWars';
		
		// FogOfWar
		$this->variantClasses['drawMap']              = 'RatWars';
		$this->variantClasses['drawMapXML']           = 'RatWars';
		$this->variantClasses['adjudicatorPreGame']   = 'RatWars';
		$this->variantClasses['adjudicatorDiplomacy'] = 'RatWars';
		$this->variantClasses['panelGameBoard']       = 'RatWars';
		$this->variantClasses['OrderInterface']       = 'RatWars';
		$this->variantClasses['OrderArchiv']          = 'RatWars';
		$this->variantClasses['processGame']          = 'RatWars';
		$this->variantClasses['userOrderDiplomacy']   = 'RatWars';
		$this->variantClasses['Maps']                 = 'RatWars';
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