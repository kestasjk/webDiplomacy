<?php
/*
	Copyright (C) 2010 Cian O Rathaille

	This file is part of the Germany Vs Russia variant for webDiplomacy

	The Germany Vs Russia variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Germany Vs Russia variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Rules for the The Germany Vs Russia Variant:
	Same as Std. Diplomacy, but only 2 Nations - based on France Vs Austria by Oliver Auth
	
	This is Version: 1.0.0
	
	Changelog:
	1.0: initial version  - derived from France Versus Austria Version 1.0.1
	1.0.1: small spelling-fix, and fixed bet to "1" d-point
	1.0.2: fixed: spelling error on smallmap
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class GermanyVsRussiaVariant extends WDVariant {
	public $id         =25;
	public $mapID      =25;
	public $name       ='GermanyVsRussia';
	public $fullName   ='Germany vs Russia';
	public $description='The standard Diplomacy map of Europe, but only Germany and Russia.';
	public $adapter    = 'Orathaic';
	public $version    = '1.0.2';

	public $countries=array('Germany','Russia');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'GermanyVsRussia';
		$this->variantClasses['adjudicatorPreGame'] = 'GermanyVsRussia';
		$this->variantClasses['processMember']      = 'GermanyVsRussia';
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