<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ColdWarVariant extends WDVariant {
	public $id         = 91;
	public $mapID      = 91;
	public $name       = 'ColdWar';
	public $fullName   = 'Cold War';
	public $description= 'The best of vDips 2-player action brought to webDip!';
	public $adapter    = 'Firehawk & Safari';
	public $version    = '1';
	public $codeVersion= '1.0.6';

	public $countries=array('USSR','NATO');

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 17;
	}

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'ColdWar';
		$this->variantClasses['adjudicatorPreGame'] = 'ColdWar';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1960);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1960);
		};';
	}
}

?>