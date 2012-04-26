<?php
/*
	Copyright (C) 2012 Gavin Atkinson / Oliver Auth

	This file is part of the Pirates variant for webDiplomacy

	The Pirates variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Pirates variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: first install
	1.0.5: many combat issues fixed...
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class PiratesVariant extends WDVariant {
	public $id         =66;
	public $mapID      =66;
	public $name       ='Pirates';
	public $fullName   ='Pirates';
	public $description='Pirates, European powers and privateers all fighting it out on the high seas of the Caribbean - what more could you want? ';
	public $author     ='Gavin Atkinson';
	public $adapter    ='Gavin Atkinson, Oliver Auth';
	public $version    ='I';
	public $codeVersion='1.0.5';	
	
	public $countries=array('Spain','England','France','Holland','Dunkirkers','Henry Morgan','Francois l Olonnais','Isaac Rochussen','The Infamous El Guapo','Daniel "The Exterminator" Montbars','Roche "The Rock" Braziliano','Bartolomeu "The Portuguese" de la Cueva','Daniel "The Terror" Johnson');	

	public function __construct() {
		parent::__construct();

		// Game setup
		$this->variantClasses['drawMap']            = 'Pirates';
		$this->variantClasses['adjudicatorPreGame'] = 'Pirates';

		// Frigates extra strength
		$this->variantClasses['adjudicatorDiplomacy'] = 'Pirates';
		$this->variantClasses['adjHeadToHeadMove']    = 'Pirates';
		$this->variantClasses['adjMove']              = 'Pirates';

		// Privateer rule
		$this->variantClasses['adjudicatorDiplomacy'] = 'Pirates';

		// Transform
		$this->variantClasses['drawMap']               = 'Pirates';
		$this->variantClasses['OrderArchiv']           = 'Pirates';
		$this->variantClasses['OrderInterface']        = 'Pirates';
		$this->variantClasses['processOrderDiplomacy'] = 'Pirates';
		$this->variantClasses['userOrderDiplomacy']    = 'Pirates';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Pirates';		
		$this->variantClasses['processOrderBuilds'] = 'Pirates';
		$this->variantClasses['userOrderBuilds']    = 'Pirates';
		
		// Custom unitnames:
		$this->variantClasses['OrderArchiv']        = 'Pirates';
		$this->variantClasses['OrderInterface']     = 'Pirates';
		
		// Custom Start:
		$this->variantClasses['adjudicatorPreGame'] = 'Pirates';
		$this->variantClasses['processGame']        = 'Pirates';
		$this->variantClasses['processOrderBuilds'] = 'Pirates';
		
		// Hurricane:
		$this->variantClasses['OrderArchiv']        = 'Pirates';
		$this->variantClasses['processMembers']     = 'Pirates';		
		$this->variantClasses['processOrderBuilds'] = 'Pirates';		
	}
	
	public function countryID($countryName)
	{
		if ($countryName == 'Hurricane')
			return count($this->countries)+1;
		
		return parent::countryID($countryName);
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1666);
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 28;
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1666);
		};';
	}
}

?>