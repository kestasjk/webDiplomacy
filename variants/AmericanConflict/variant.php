<?php
/*
	Copyright (C) 2012 Gavin Atkinson

	This file is part of the American Conflict variant for webDiplomacy

	The American Conflict variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The American Conflict variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

	---

	Changelog:
	1.0:   initial version
	1.0.2: Borderfix; Only 29 SC's needed to win
	1.0.3: new rules.html

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AmericanConflictVariant extends WDVariant {
	public $id         = 69;
	public $mapID      = 69;
	public $name       = 'AmericanConflict';
	public $fullName   = 'American Conflict';
	public $description= 'The American Civil War... with European powers entering the fighting!';
	public $author     = 'Gavin Atkinson';
	public $adapter    = 'Gavin Atkinson';
	public $version    = '1';
	public $codeVersion= '1.0.9';

	public $countries=array('Russia', 'Confederate States', 'United States', 'England', 'France', 'Spain');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'AmericanConflict';
		$this->variantClasses['adjudicatorPreGame'] = 'AmericanConflict';

		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'AmericanConflict';		
		$this->variantClasses['processOrderBuilds'] = 'AmericanConflict';
		$this->variantClasses['userOrderBuilds']    = 'AmericanConflict';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 29;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1862);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1862);
		};';
	}
}

?>