<?php
/*
	Copyright (C) 2011 kaner406 / Oliver Auth

	This file is part of the Viking variant for webDiplomacy

	The Viking variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Viking variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: first install
	1.0.2: fixed: typo in adjucatorPregame
	1.0.3: quickfix: convoy validation failed (most of the time) disabled.
	1.0.4: improved javascript convoy-code. Works now.
	1.1:   another updated javascript-code much better and cleaner now.
	1.1.1: but does not work again... (disabled)
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class VikingVariant extends WDVariant {
	public $id         =63;
	public $mapID      =63;
	public $name       ='Viking';
	public $fullName   ='Viking Diplomacy IV';
	public $description='Europe in the Vikings age.';
	public $author     ='Joe Janbu';
	public $adapter    ='kaner406 / Oliver Auth';
	public $homepage   ='http://www.variantbank.org/results/rules/v/viking4.htm';
	public $version    ='4.0';	
	public $codeVersion='1.1.1';	
	
	public $countries=array('Arab Caliphates','Burgundy','Danmark','Eastern Roman Empire','France','Slavic Nations','Norge','Sverige');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Viking';
		$this->variantClasses['adjudicatorPreGame'] = 'Viking';
		
		// Allow for some coasts to convoy
		$this->variantClasses['OrderInterface']     = 'Viking';
		$this->variantClasses['userOrderDiplomacy'] = 'Viking';
		
		// Neutral units:
		$this->variantClasses['OrderArchiv']        = 'Viking';
		$this->variantClasses['processGame']        = 'Viking';
		$this->variantClasses['processMembers']     = 'Viking';

	}

	/* Neutral units that hold each other:
	*    Buda(31)      <=> Pest(106)
	*    Alexandria(9) <=> Jorsal(150)
	*/
	public $neutralHold = array('31'=>'106','106'=>'31','9'=>'150','150'=>'9');
	
	/* Coasts that allow convoying.
	*   Aldeigjuborg(8), Holmgard(157), Gardarike(174), Kjonugard(140), Cumaniya(41),
	*   Rostov(96), Vladimir(57), Khazar Empire(142)
	*/
	public $convoyCoasts = array ('8','157', '174', '140', '41', '96', '57', '142');
	
	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units')
			return count($this->countries)+1;
		return parent::countryID($countryName);
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 26;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 951);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 951);
		};';
	}
}

?>