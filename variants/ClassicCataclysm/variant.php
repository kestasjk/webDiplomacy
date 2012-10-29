<?php
/*
    Copyright (C) 2004-2009 Kestas J. Kuliukas

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
 * The default "classic" Diplomacy; Europe etc. Apocalypse variant www.diplomail.ru
 */
class ClassicCataclysmVariant extends WDVariant {
	public $id         =84;
	public $mapID      =84;
	public $name       ='ClassicCataclysm';
	public $fullName   ='Classic - Cataclysm';
	public $description='The standard Diplomacy map of Europe Cataclysm variant.';
	public $author     ='CSKA';
	public $adapter    ='Flame';
	public $version    ='1.0.0';
	public $homepage   ='http://www.diplomail.ru';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public $seaTerrs = array(
		'Barents Sea', 'Norwegian Sea', 'North Sea', 'Skagerrack', 'Heligoland Bight', 'Baltic Sea',
		'Gulf of Bothnia', 'North Atlantic Ocean', 'Irish Sea', 'English Channel', 'Mid-Atlantic Ocean',
		'Western Mediterranean', 'Gulf of Lyons', 'Tyrrhenian Sea', 'Ionian Sea', 'Adriatic Sea',
		'Aegean Sea', 'Eastern Mediterranean', 'Black Sea' );
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap'] = 'ClassicCataclysm';

		$this->variantClasses['adjudicatorPreGame'] = 'ClassicCataclysm';
		
		$this->variantClasses['OrderInterface']     = 'ClassicCataclysm';

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