<?php
/*
	Copyright (C) 2013 Arjun Sarathy / Oliver Auth

	This file is part of the Youngstown World War II variant for webDiplomacy

	The Youngstown World War II variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Youngstown World War II variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:     initial release
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class YoungstownWWIIVariant extends WDVariant {
	public $id         = 92;
	public $mapID      = 92;
	public $name       = 'YoungstownWWII';
	public $fullName   = 'Youngstown World War II';
	public $description= 'Youngstown Diplomacy set in 1939, just before war breaks out';
	public $author     = 'Arjun Sarathy';
	public $adapter    = 'Arjun Sarathy (aka ImperialDiplomat)';
	public $version    = '1';
	public $codeVersion= '1.0.5';

	public $countries=array('British Empire','French Empire','Italian Empire','Japanese Empire','Germany','Soviet Union');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'YoungstownWWII';
		$this->variantClasses['adjudicatorPreGame'] = 'YoungstownWWII';
		$this->variantClasses['panelGameBoard']     = 'YoungstownWWII';
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