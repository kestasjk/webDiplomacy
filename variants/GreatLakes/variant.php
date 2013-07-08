<?php
/*
	Copyright (C) 2012 Oliver Auth / Scordatura

	This file is part of the Indians of the Great Lakes variant for webDiplomacy

	The Indians of the Great Lakes variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Indians of the Great Lakes variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class GreatLakesVariant extends WDVariant {
	public $id         =77;
	public $mapID      =77;
	public $name       ='GreatLakes';
	public $fullName   ='Indians of the Great Lakes';
	public $description='American Indian Tribes of the Great Lakes, in A.D. 1501';
	public $author     ='Chris Carde';
	public $adapter    ='Scordatura';
	public $homepage   ='http://www.variantbank.org/results/rules/i/indiansogl.htm';
	public $version    ='1';
	public $codeVersion='1.0';	
	
	public $countries=array('Algonquin', 'Erie', 'Huron', 'Iroquois', 'Kaskasia', 'Mississauga', 'Ojibwe', 'Otawatomi', 'Ottawa');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'GreatLakes';
		$this->variantClasses['adjudicatorPreGame'] = 'GreatLakes';
		$this->variantClasses['OrderInterface']     = 'GreatLakes';

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