<?php
/*
	Copyright (C) 2012 Mikalis Kamaritis / Oliver Auth

	This file is part of the Europe 1939 variant for webDiplomacy

	The Europe 1939 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Europe 1939 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: first install
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Europe1939Variant extends WDVariant {
	public $id         =72;
	public $mapID      =72;
	public $name       ='Europe1939';
	public $fullName   ='Europe 1939';
	public $author     ='Mikalis Kamaritis';
	public $adapter    ='Mikalis Kamaritis';
	public $codeVersion='1.0';	
		
	public $countries=array('Britain', 'France', 'Germany', 'Spain', 'Italy', 'Poland', 'USSR', 'Turkey');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Europe1939';
		$this->variantClasses['adjudicatorPreGame'] = 'Europe1939';

		// Neutral units:
		$this->variantClasses['OrderArchiv']        = 'Europe1939';
		$this->variantClasses['processGame']        = 'Europe1939';
		$this->variantClasses['processMembers']     = 'Europe1939';

	}
	
	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units')
			return count($this->countries)+1;
		
		return parent::countryID($countryName);
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1939);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1939);
		};';
	}
}

?>
