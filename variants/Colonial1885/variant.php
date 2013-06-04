<?php
/*
	Copyright (C) 2013 Firehawk

	This file is part of the Coperial variant for webDiplomacy

	The Coperial variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Coperial variant for webDiplomacy is distributed in the hope that it will be
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

class Colonial1885Variant extends WDVariant {
	public $id         = 71;
	public $mapID      = 71;
	public $name       ='Colonial1885';
	public $fullName   = 'Colonial 1885';
	public $description= 'The powers of the Colonial age battle it out in the late 19th century';
	public $adapter    = 'Firehawk';
	public $version    = '1';
	public $codeVersion= '1.0';

	public $countries=array('Britain','France','Germany','Italy','Austria','Holland','Russia','Turkey','China','Japan',);

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Colonial1885';
		$this->variantClasses['adjudicatorPreGame'] = 'Colonial1885';
		$this->variantClasses['panelGameBoard']     = 'Colonial1885';
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 50;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1885);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1885);
		};';
	}
}

?>