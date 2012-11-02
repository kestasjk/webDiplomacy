<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class CelticBritainVariant extends WDVariant {
   public $id         =75;
   public $mapID      =75;
   public $name       ='CelticBritain';
   public $fullName   ='Celtic Britain';
   public $author     ='amisond';
   public $adapter    ='amisond';
   public $version    ='1.2';
   public $codeVersion='1.0';
   
   public $countries=array('Brigantes', 'Iceni', 'Caledonii', 'Picts', 'Cornovii', 'Ivernia', 'Voluntii', 'Durotriges');

   public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'CelticBritain';
		$this->variantClasses['adjudicatorPreGame'] = 'CelticBritain';
	  
		// Custom icons
		$this->variantClasses['OrderInterface']     = 'CelticBritain';
	  
		// Allow for some coasts to convoy
		$this->variantClasses['OrderInterface']     = 'CelticBritain';
		$this->variantClasses['userOrderDiplomacy'] = 'CelticBritain';
	}
	
	/* CoastsIDs that allow convoying.
	*  Carini(9), Cornavii(15), Skitis(70),
	*/
	public $convoyCoasts = array ('9','15','70');
 
   public function turnAsDate($turn) {
      if ( $turn==-1 ) return "Pre-game";
      else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 40);
   }

   public function turnAsDateJS() {
      return 'function(turn) {
         if( turn==-1 ) return "Pre-game";
         else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 40);
      };';
   }
}

?>