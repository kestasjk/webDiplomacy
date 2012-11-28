<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class AfricaVariant extends WDVariant {
	public $id         =83;
	public $mapID      =83;
	public $name       ='Africa';
	public $fullName   ='Africa';
	public $description='Africa goes to war in 2012';
	public $author     ='Tristan';
	public $adapter    ='Tristan, kaner406 & Oli';
	public $version    ='1.0.1';
	public $codeVersion='1.0.1';

	public $countries=array('DRC','Egypt','Ethiopia','Madagascar','Mali','Morocco','Nigeria','South Africa');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Africa';
		$this->variantClasses['adjudicatorPreGame'] = 'Africa';
		
		// Neutral units:
		$this->variantClasses['OrderArchiv']        = 'Africa';
		$this->variantClasses['processGame']        = 'Africa';
		$this->variantClasses['processMembers']     = 'Africa';
		
		// Allow for some coasts to convoy
		$this->variantClasses['OrderInterface']     = 'Africa';
		$this->variantClasses['userOrderDiplomacy'] = 'Africa';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Africa';		
		$this->variantClasses['processOrderBuilds'] = 'Africa';
		$this->variantClasses['userOrderBuilds']    = 'Africa';
	}

	/* Coasts that allow convoying.
	 * Sao Tome (135), St Helena (126), Comoros (124), Seychelles (123), Mauritius (125),
	 * Reunion (130), Canary Islands (122) and Tristan da Cunha (131)
	*/
	
	public $convoyCoasts = array ('122', '123', '124', '125', '126', '130', '131', '135');

	// Neutral units:
	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units') return count($this->countries)+1;
		return parent::countryID($countryName);
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2012);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2012);
		};';
	}
}

?>