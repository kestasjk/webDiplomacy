<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Claccic-Fog-of-War variant for webDiplomacy

	The Claccic-Fog-of-War variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General Public
	License as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Claccic-Fog-of-War variant for webDiplomacy is distributed in the hope that 
	it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:    initial release
	1.0.1:  fog-sql-code update
	1.0.2:  bugfix visible builds/destroys
	1.0.3:  code cleanup drawmap.php
	1.0.4:  countries in CD no linger show their "worth"
	1.0.5:  Bet is now hidden too
	1.0.7:  Fixed a really nasty bug that crasehd the game on wrong support-hold orders.
	1.0.8:  Fixed: Error in the OrderArchiv code
	1.0.9:  Fix killed other variants...
	1.0.10: New fix...
	1.0.11: copy orders.php to board/info
	1.0.12: countries no longer sorted by value...
	1.0.13: do not hide the county-value for CDed countries, so new players can join...

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFogVariant extends WDVariant {
	public $id         = 30;
	public $mapID      = 30;
	public $name       = 'ClassicFog';
	public $fullName   = 'Classic - Fog of War';
	public $description= 'This is the classic map, but players can only see a limited part of the map';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.0';
	public $codeVersion= '1.0.14';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');
	
	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']              = 'ClassicFog';
		$this->variantClasses['drawMapXML']           = 'ClassicFog';
		$this->variantClasses['adjudicatorPreGame']   = 'ClassicFog';
		$this->variantClasses['adjudicatorDiplomacy'] = 'ClassicFog';
		$this->variantClasses['panelGameBoard']       = 'ClassicFog';
		$this->variantClasses['OrderInterface']       = 'ClassicFog';
		$this->variantClasses['OrderArchiv']          = 'ClassicFog';
		$this->variantClasses['panelMember']          = 'ClassicFog';
		$this->variantClasses['panelMemberHome']      = 'ClassicFog';
		$this->variantClasses['processGame']          = 'ClassicFog';
		$this->variantClasses['panelMembers']         = 'ClassicFog';
		$this->variantClasses['panelMembersHome']     = 'ClassicFog';
		$this->variantClasses['userOrderDiplomacy']   = 'ClassicFog';
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