<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Youngstown - Redux variant for webDiplomacy

	The Youngstown - Redux variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Youngstown - Redux variant for webDiplomacy is distributed in the hope that it will be
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

class YoungstownReduxVariant extends WDVariant {
	public $id         =59;
	public $mapID      =59;
	public $name       ='YoungstownRedux';
	public $fullName   ='Youngstown - Redux';
	public $description='Youngstown with some twists!';
	public $author     ='airborne';
	public $adapter    ='airborne';
	public $version    ='I';
	public $codeVersion='1.0';	
	
	public $countries=array('India','Japan','Austria','Italy','China','Britain','France','Germany','Turkey','Russia');	

	public function __construct() {
		parent::__construct();

		// Basic setup
		$this->variantClasses['drawMap']            = 'YoungstownRedux';
		$this->variantClasses['adjudicatorPreGame'] = 'YoungstownRedux';
		
		// Zoom-Map
		$this->variantClasses['panelGameBoard']     = 'YoungstownRedux';
		$this->variantClasses['drawMap']            = 'YoungstownRedux';

	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 28;
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