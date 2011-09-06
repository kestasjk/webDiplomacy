<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the BalkanWarsVI variant for webDiplomacy

	The BalkanWarsVI variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The BalkanWarsVI variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.0.1: better rules.html (thanks Gavin)
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class BalkanWarsVIVariant extends WDVariant {
	public $id         =46;
	public $mapID      =46;
	public $name       ='BalkanWarsVI';
	public $fullName   ='Balkan Wars VI';
	public $description='A crowded map for 6 players for a very fast diplomacy experience.';
	public $author     ='Brad Wilson based on earlier designs by Fred Davis and others...';
	public $adapter    ='Oliver Auth';
	public $version    ='1.0.2';
	public $homepage   ='http://members.cox.net/boris_spider/hrules/BW6.html';
	
	public $countries=array('Albania','Bulgaria','Greece','Rumania','Serbia','Turkey');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'BalkanWarsVI';
		$this->variantClasses['adjudicatorPreGame'] = 'BalkanWarsVI';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'BalkanWarsVI';		
		$this->variantClasses['processOrderBuilds'] = 'BalkanWarsVI';
		$this->variantClasses['userOrderBuilds']    = 'BalkanWarsVI';
		
		// Start with a build phase:
		$this->variantClasses['adjudicatorPreGame'] = 'BalkanWarsVI';
		$this->variantClasses['processGame']        = 'BalkanWarsVI';
		$this->variantClasses['processOrderBuilds'] = 'BalkanWarsVI';
		
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1910);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1910);
		};';
	}
}

?>