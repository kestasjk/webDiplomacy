<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant extends WDVariant {
	public $id=22;
	public $mapID=22;
	public $name='Duo';
	public $fullName='Duo';
	public $description='2 player map';
	public $author='Frank Hegermann';
	public $adapter='Oliver Auth';
	public $version='0.8';
	public $homepage='http://www.dipwiki.com/?title=Duo';

	public $countries=array('Red','Green','Black');
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['Chatbox']              = 'Duo';
		$this->variantClasses['drawMap']              = 'Duo';
		$this->variantClasses['processGame']          = 'Duo';
		$this->variantClasses['processMember']        = 'Duo';
		$this->variantClasses['OrderInterface']       = 'Duo';
		$this->variantClasses['adjudicatorPreGame']   = 'Duo';
		$this->variantClasses['adjudicatorDiplomacy'] = 'Duo';
		$this->variantClasses['panelMembers']         = 'Duo';
		$this->variantClasses['panelMembersHome']     = 'Duo';
		$this->variantClasses['panelGameBoard']       = 'Duo';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 19;
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