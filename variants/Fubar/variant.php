<?php
/*
	Copyright (C) 2010 sqrg

	This file is part of the Fubar variant for webDiplomacy

	The Fubar variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Fubar variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	0.8:   first installment
	1.0:   first release
	1.0.1: style.css fix
	1.0.2: rules.html added
	1.0.3: border-issue fixed
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FubarVariant extends WDVariant {
	public $id=39;
	public $mapID=39;
	public $name='Fubar';
	public $fullName='Fubar';
	public $description='Fucked up beyond all recognition.';
	public $author='sqrg';
	public $version='1.0.3';

	public $countries=array('Fatflap','Howdoileavethisgame','timmy1999','Sh1tn00b','oMgYoUrAsLuT','multi_152');

	public function __construct() {
		parent::__construct();

		// drawMap extended for country-colors and loading the classic map images
		$this->variantClasses['drawMap']            = 'Fubar';

		// custom start
		$this->variantClasses['adjudicatorPreGame'] = 'Fubar';
		$this->variantClasses['processGame']        = 'Fubar';

		// custom start + build anywhere
		$this->variantClasses['processOrderBuilds'] = 'Fubar';

		// build anywhere
		$this->variantClasses['OrderInterface']     = 'Fubar';
		$this->variantClasses['userOrderBuilds']    = 'Fubar';

		//bet of 1
		$this->variantClasses['processMember']	    = 'Fubar';

	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( 100 - ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2)));
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( 100 - (turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2)));
		};';
	}
}

?>
