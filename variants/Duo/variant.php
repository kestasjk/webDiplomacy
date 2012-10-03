<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Duo variant for webDiplomacy

	The Duo variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Duo variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	0.8: initial release
	0.9: big codecleanup, doTo: neutral units movement

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant extends WDVariant {
	public $id=22;
	public $mapID=22;
	public $name='Duo';
	public $fullName='Duo';
	public $description='Duo is a Diplomacy variant for 2 players. The map is point symmetric, so that each player has same chances and the tactics/strategy are thus the only relevant items in the game.';
	public $author='Frank Hegermann';
	public $adapter='Oliver Auth';
	public $version='1.0';
	public $codeVersion='0.10';
	public $homepage='http://www.dipwiki.com/?title=Duo';

	public $countries=array('Red','Green');
	
	public function __construct() {
		parent::__construct();

		// Game Setup
		$this->variantClasses['drawMap']               = 'Duo';
		$this->variantClasses['adjudicatorPreGame']    = 'Duo';

		// Transform command
		$this->variantClasses['drawMap']               = 'Duo';
		$this->variantClasses['OrderArchiv']           = 'Duo';
		$this->variantClasses['OrderInterface']        = 'Duo';
		$this->variantClasses['processOrderDiplomacy'] = 'Duo';
		$this->variantClasses['userOrderDiplomacy']    = 'Duo';
		
		// Fixed bet at 1DPoint
		$this->variantClasses['processMember']         = 'Duo';
		
		// Neutral units:
		$this->variantClasses['OrderArchiv']           = 'Duo';
		$this->variantClasses['processGame']           = 'Duo';
		$this->variantClasses['processMembers']        = 'Duo';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 19;
	}
	
	public function countryID($countryName)
	{
		if ($countryName == 'Black')
			return count($this->countries)+1;
		
		return parent::countryID($countryName);
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