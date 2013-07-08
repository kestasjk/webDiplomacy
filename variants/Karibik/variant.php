<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Karibik variant for webDiplomacy

	The Karibik variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Karibik variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.0.1: fixed a missing borderlink
	1.0.2: fixed: issue with failed transforms
	1.0.3: fixed: typo
	1.1:   new transform code
	1.1.1: fixed: small bug in drawmap
	1.1.6: fixed: new transform bugs
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class KaribikVariant extends WDVariant {
	public $id         =45;
	public $mapID      =45;
	public $name       ='Karibik';
	public $fullName   ='Karibik';
	public $description='The Carribean in the beginning of our century, it is played between Brasil, Columbia, Cuba, Mexico, Paraguay, Peru, USA, Venezuela ';
	public $author     ='Andreas Keller and the variant team at DEAC';
	public $adapter    ='Oliver Auth';
	public $version    ='1.1.6';
	public $homepage   ='http://www.dipwiki.com/index.php?title=Karibik';

	public $countries=array('Brasil','Columbia','Cuba','Mexico','Paraguay','Peru','USA','Venezuela');
	
	public function __construct() {
		parent::__construct();
		
		// Setup the game
		$this->variantClasses['drawMap']               = 'Karibik';
		$this->variantClasses['adjudicatorPreGame']    = 'Karibik';
		
		// Transform command
		$this->variantClasses['drawMap']               = 'Karibik';
		$this->variantClasses['OrderArchiv']           = 'Karibik';
		$this->variantClasses['OrderInterface']        = 'Karibik';
		$this->variantClasses['processOrderDiplomacy'] = 'Karibik';
		$this->variantClasses['userOrderDiplomacy']    = 'Karibik';
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
