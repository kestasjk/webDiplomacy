<?php
/*
	Copyright (C) 2011 Milan Mach

	This file is part of the 843 variant for webDiplomacy

	The 843 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 843 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0: initial release
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class TreatyOfVerdunVariant extends WDVariant {
	public $id=58;
	public $mapID=58;
	public $name='TreatyOfVerdun';
	public $fullName='843: Treaty of Verdun';
	public $description='The Treaty of Verdun divided Carolingian Empire into 3 kingdoms but for how long?';
	public $author='Milan Mach (WebDip: Milan Mach)';
	public $version='1.0';
	public $codeVersion='1.0';
        
	public $countries=array('East Francia','Middle Francia','West Francia');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'TreatyOfVerdun';
		$this->variantClasses['adjudicatorPreGame'] = 'TreatyOfVerdun';
                
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'TreatyOfVerdun';		
		$this->variantClasses['processOrderBuilds'] = 'TreatyOfVerdun';
		$this->variantClasses['userOrderBuilds']    = 'TreatyOfVerdun';
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 843);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 843);
		};';
	}
}

?>