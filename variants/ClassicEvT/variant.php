<?php
/*
	Copyright (C) 2011 Orathaic

	This file is part of the England* vs Turkey variant for webDiplomacy

	The England* vs Turkey variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The England* vs Turkey variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial version
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicEvTVariant extends WDVariant {
	public $id         =62;
	public $mapID      =62;
	public $name       ='ClassicEvT';
	public $fullName   ='Classic - England* Vs Turkey';
	public $description='The standard Diplomacy map of Europe, but with England and Turkey as the only playable countries. To offset the imbalance of Englands start, his fleets now start in open seas';
	public $adapter    = 'Orathaic';
	public $version    = '1.0';

	public $countries=array('England','Turkey');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']               = 'ClassicEvT';
		$this->variantClasses['adjudicatorPreGame']    = 'ClassicEvT';
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
