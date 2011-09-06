<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the 1897 variant for webDiplomacy

	The 1897 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 1897 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial release
	1.0.1: finetunig
	1.0.2: finetunig
	1.0.3: fixed a wrong SQL-querry for fleets after the SC's got locked...
	1.0.4: fixed: wrong number of destroy orders in some circumstances
	1.0.5: fixed: missing borders in 1.0.4
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Classic1897Variant extends WDVariant {
	public $id         =28;
	public $mapID      =28;
	public $name       ='Classic1897';
	public $fullName   ='1897';
	public $description='The standard Diplomacy map of Europe but start in Winter 1897.';
	public $author     ='Mark Nelson, Josh Smith, and Rick Westerman';
	public $adapter    ='Oliver Auth / Carey Jensen';
	public $version    ='1.0.4';
	public $homepage   ='http://www.variantbank.org/results/rules/1/1897.htm';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Classic1897';
		$this->variantClasses['adjudicatorPreGame'] = 'Classic1897';
		$this->variantClasses['processGame']        = 'Classic1897';
		$this->variantClasses['processOrderBuilds'] = 'Classic1897';
		$this->variantClasses['OrderInterface']     = 'Classic1897';
		$this->variantClasses['userOrderBuilds']    = 'Classic1897';
		$this->variantClasses['panelGameBoard']     = 'Classic1897';
		
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1898);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1898);
		};';
	}
}

?>