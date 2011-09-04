<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Colonial variant for webDiplomacy

	The Colonial variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Colonial variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Rules for the The Colonial Variant by Peter Hawes:
	http://www.dipwiki.com/index.php?title=Colonial

	There are 2 unimplementet rules:
	 - the suez canal -> Egyp is just a canal (like constantinople in the original game)
	 - the transsibirian railroad
	If you have some time and programming knowledge feel free to contact me.

	This is Version: 1.7.5

	Changelog:
	1.0: initial release
	1.1: corrected some spelling mistake
	1.2: added support for "neutral" support-centers, recolored starting map and territories
	1.5: new webdip v.97 code
	1.5.2: missing Borders added, special-rule HongKong added
	1.6: minor tweaks
	1.6.1: missing Borders added, land bridges allow movement for fleets too
	1.6.2: missing Borders added, improved routine to avoid black country-flags
	1.7: no more flag drawing for "neutral" support-centers with colored territories
	     if they get occupied by their own country
	1.7.1: improved algorithm for flag placing
	1.7.2: New places for some units on the smallmap to improve readability
	1.7.3: New places for some units on the smallmap to improve readability
	1.7.5: Adjustments for the new variant.php code

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ColonialVariant extends WDVariant {
	public $id=12;
	public $mapID=12;
	public $name='Colonial';
	public $fullName='Colonial Diplomacy';
	public $description='Diplomacy with the colonial countries sparring over the lands and riches of the Far East.';
	public $author='Peter Hawes';
	public $adapter='Oliver Auth';
	public $version='1.7.5';
	public $homepage='http://www.dipwiki.com/index.php?title=Colonial';
	public $supplyCenterTarget=30;

	public $countries=array('Britain','China','France','Holland','Japan','Russia','Turkey');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap'] = 'Colonial';
		$this->variantClasses['adjudicatorPreGame'] = 'Colonial';
		$this->variantClasses['processMembers'] = 'Colonial';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 30;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1870);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1870);
		};';
	}
}

?>
