<?php
/*
	Copyright (C) 2012 Oliver Auth / sqrg

	This file is part of the NorthSeaWars variant for webDiplomacy

	The NorthSeaWars variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The NorthSeaWars variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0: initial version
	1.1: much improved map-handling
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class NorthSeaWarsVariant extends WDVariant {
	public $id         =73;
	public $mapID      =73;
	public $name       ='NorthSeaWars';
	public $fullName   ='NorthSea Wars';
	public $description='An economic conflict at the start of the first millenium';
	public $author     ='sqrg';
	public $adapter    ='sqrg';
	public $version    ='1';
	public $codeVersion='1.0.1';	
	
	public $countries=array('Briton','Roman','Frysian','Norse');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'NorthSeaWars';
		$this->variantClasses['adjudicatorPreGame'] = 'NorthSeaWars';
		$this->variantClasses['panelGameBoard']     = 'NorthSeaWars';
		$this->variantClasses['OrderInterface']     = 'NorthSeaWars';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2));
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2));
		};';
	}
}

?>
