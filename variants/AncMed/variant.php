<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Ancient Mediterranean variant for webDiplomacy

	The Ancient Mediterranean variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Ancient Mediterranean variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Rules for the the Ancient Mediterranean Variant by Don Hessong:
	http://www.variantbank.org/results/rules/a/ancient_med.htm

	This is Version: 1.7

	Changelog:
	1.0: initial version
	1.1: fixed some graphic issues on the maps
	1.5: new webdip0.97 variant code
	1.6: minor fixes
	1.6.1: Added color-function to avoid black flags and stars
	1.6.2: some adjustments to the new variant.php funtionality
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AncMedVariant extends WDVariant {
	public $id=9;
	public $mapID=9;
	public $name='AncMed';
	public $fullName='The Ancient Mediterranean';
	public $description='A variant with a map of the Ancient Mediterranean.';
	public $author='Don Hessong';
	public $adapter='Oliver Auth';
	public $version='1.6.2';
	public $homepage='http://www.variantbank.org/results/rules/a/ancient_med.htm';

	public $countries=array('Carthage','Egypt','Greece','Persia','Rome');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap'] = 'AncMed';
		$this->variantClasses['adjudicatorPreGame'] = 'AncMed';

	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return l_t("Pre-game");
		else return ( $turn % 2 ? l_t("Autumn").", " : l_t("Spring").", " ).(floor($turn/2) + 1);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return l_t("Pre-game");
			else return ( turn%2 ? l_t("Autumn")+", " : l_t("Spring")+", " )+(Math.floor(turn/2) + 1);
		};';
	}
}

?>