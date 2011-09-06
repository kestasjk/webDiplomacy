<?php
/*
	Copyright (C) 2010 Oliver Auth, Orathaic

	This file is part of the France Vs Germany Vs Austria variant for webDiplomacy

	The France Vs Germany Vs Austria variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France Vs Germany Vs Austria variant for webDiplomacy is distributed in the hope that it will
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

class FGAVariant extends WDVariant {
	public $id         =48;
	public $mapID      =48;
	public $name       ='FGA';
	public $fullName   ='France vs Germany vs Austria';
	public $description='The standard Diplomacy map of Europe, but only France, Germany and Austria.';
	public $adapter    = 'Orathaic';
	public $version    = '1.0';

	public $countries=array('France','Austria','German');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']               = 'FGA';
		$this->variantClasses['adjudicatorPreGame']    = 'FGA';
		$this->variantClasses['processMember']         = 'FGA';
		$this->variantClasses['processOrderDiplomacy'] = 'FGA';
		
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
