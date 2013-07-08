<?php
/*
	Copyright (C) 2011 by kaner406 & Oliver Auth

	This file is part of the War in 2020 variant for webDiplomacy

	The War in 2020 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The War in 2020 variant for webDiplomacy is distributed in the hope
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

class War2020Variant extends WDVariant {
	public $id         =61;
	public $mapID      =61;
	public $name       ='War2020';
	public $fullName   ='War in 2020';
	public $description='In 2020 a world war breaks out';
	public $author     ='Jason B.';
	public $adapter    ='kaner406 & Oliver Auth';
	public $codeVersion='1.0.1';

	public $countries=array('Australia', 'USA', 'OAS', 'EU', 'South Africa', 'India', 'OPEC', 'China', 'Russia', 'Japan');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'War2020';
		$this->variantClasses['adjudicatorPreGame'] = 'War2020';

		// Start with a build phase:
		$this->variantClasses['processGame']        = 'War2020';
		
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