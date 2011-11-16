<?php
/*
	Copyright (C) 2010 Oliver Auth, Cian O Rathaille

	This file is part of the NoNeutrals variant for webDiplomacy

	The NoNeutrals variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The NoNeutrals variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Rules for the NoNeutrals Variant by
	Based on 1.1.1 classic variant by Oliver Auth
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicNoNeutralsVariant extends WDVariant {
	public $id         =38;
	public $mapID      =38;
	public $name       ='ClassicNoNeutrals';
	public $fullName   ='Classic - NoNeutrals';
	public $description='The classic map no neutrals';
	public $author     ='Unknown';
	public $adapter    ='Carey Jensen / Oliver Auth / Orathaic';
	public $codeVersion    ='1.0.1';
	
	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');	

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'ClassicNoNeutrals';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicNoNeutrals';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 12;
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
