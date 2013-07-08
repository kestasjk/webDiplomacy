<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Fantasy World variant for webDiplomacy

	The Fantasy World variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Fantasy World variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial release
	1.0.1: added a missing SC and correctd spelling mistakes
	1.0.2: added a country-overview on the smallmap
	1.0.3: added a country overview and a link to the DiplomacyWorld homepage.
	1.0.4: fixed: border issue
	1.0.5: fixed: spelling mistake
	1.0.6: fixed: border issue

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FantasyWorldVariant extends WDVariant {
	public $id         =44;
	public $mapID      =44;
	public $name       ='FantasyWorld';
	public $fullName   ='Fantasy World Diplomacy';
	public $description='A fictional world map for 12 players.';
	public $author     ='John Biehl';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.0.6';

	public $countries=array('Arafura','Hamra','Ishfahan','Jylland','Kyushu','Lugulu','Ming-tao','New Foundland','Orleans','Rajasthan','Sakhalin','Valparaiso');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'FantasyWorld';
		$this->variantClasses['adjudicatorPreGame'] = 'FantasyWorld';
		$this->variantClasses['Chatbox']            = 'FantasyWorld';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 30;
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1889);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1889);
		};';
	}
}

?>