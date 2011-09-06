<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the France Vs Austria variant for webDiplomacy

	The France Vs Austria variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France Vs Austria variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial version
	1.1:   small adjustments for the new variant code
	1.1.1: small error fixed
	1.1.5: bet fixed to "1" D-point to prevent abuse
	1.1.6: fixed: spelling error on smallmap
	1.1.7: fixed: sc error on smallmap
	1.1.8: sorted order of commands for diplomacy phase
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FranceVsAustriaVariant extends WDVariant {
	public $id         =15;
	public $mapID      =15;
	public $name       ='FranceVsAustria';
	public $fullName   ='France vs Austria';
	public $description='The standard Diplomacy map of Europe, but only France and Austria.';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.1.8';

	public $countries=array('France','Austria');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']               = 'FranceVsAustria';
		$this->variantClasses['adjudicatorPreGame']    = 'FranceVsAustria';
		$this->variantClasses['processMember']         = 'FranceVsAustria';
		$this->variantClasses['processOrderDiplomacy'] = 'FranceVsAustria';
		
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