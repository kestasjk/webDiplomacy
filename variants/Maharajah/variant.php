<?php
/*
	Copyright (C) 2012 kaner406 / Oliver Auth

	This file is part of the Maharajah variant for webDiplomacy

	The Maharajah variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Maharajah variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	---

	Changelog:
	1.0: first install

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MaharajahVariant extends WDVariant {
	public $id         =74;
	public $mapID      =74;
	public $name       ='Maharajah';
	public $fullName   ='Maharajah';
	public $description='India battles it out in AD 1501';
	public $author     ='David Cohen';
	public $adapter    ='Ken Gordon (aka kaner406) & Oliver Auth';
	public $homepage   ='http://diplomiscellany.tripod.com/id4.html';
	public $version    ='2';
	public $codeVersion='1.0';	
	
	public $countries=array('Bahmana', 'Delhi', 'Gondwana', 'Mughalistan', 'Persia', 'Rajputana', 'Vijayanagar');	

	/* Coasts that allow convoying.
	*  Kabul(52), Sind(30) Peshawar(53), Lahore(11), Multan(54),
	*   Bikaner(55), Jaisalmer(56), Orissa(17), Sambalpur(70),
	*   Benares(16), Agra(71), Awadh(72), Muzaffarpur(73), 
	*   Bengal(18), Assam(19), Ava(20), Pegu(21)
	*/
	public $convoyCoasts = array (
		'11','16','17','18','19','20','21','30','52','53','54','55','56','70','71','72','73'
	);	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Maharajah';
		$this->variantClasses['adjudicatorPreGame'] = 'Maharajah';

		// Allow for some coasts to convoy
		$this->variantClasses['OrderInterface']     = 'Maharajah';
		$this->variantClasses['userOrderDiplomacy'] = 'Maharajah';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Maharajah';		
		$this->variantClasses['processOrderBuilds'] = 'Maharajah';
		$this->variantClasses['userOrderBuilds']    = 'Maharajah';
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 19;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1501);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1501);
		};';
	}
}

?>