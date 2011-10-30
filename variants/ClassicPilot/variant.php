<?php
/*
    Copyright (C) 2011 Orathaic

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Basec on the classic map, but without some balance;
 */
class ClassicPilotVariant extends WDVariant {
	public $id=60;
	public $mapID=60;
	public $name='ClassicPilot';
	public $fullName='Classic - Pilot';
	public $description='This variant is based on a play test previous to the original classic Diplomacy map of Europe. It is recreated here based on an article describing some of the improvements required. Alas I can not find the original article. This first version makes one small change, removing Heligoland Bight, however the gameplay effect on both Bermany and English openings is immense.';
	public $author='Orathaic - based on an article by Allan B Cahlamer';
	public $version='0.1';
	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'ClassicPilot';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicPilot';
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
