<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

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
 * The default "classic" Diplomacy; Europe etc.
 */
class ClassicVariant extends WDVariant {
	public $id=1;
	public $mapID=1;
	public $name='Classic';
	public $fullName='Classic';
	public $description='The standard Diplomacy map of Europe.';
	public $author='Avalon Hill';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		// drawMap extended for country-colors and loading the classic map images
		$this->variantClasses['drawMap'] = 'Classic';

		/*
		 * adjudicatorPreGame extended to add fair country-balancing, replacing the
		 * default random allocation for classic map games.
		 */
		$this->variantClasses['adjudicatorPreGame'] = 'Classic';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return l_t("Pre-game");
		else return ( $turn % 2 ? l_t("Autumn").", " : l_t("Spring").", " ).(floor($turn/2) + 1901);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return l_t("Pre-game");
			else return ( turn%2 ? l_t("Autumn")+", " : l_t("Spring")+", " )+(Math.floor(turn/2) + 1901);
		};';
	}
}

?>