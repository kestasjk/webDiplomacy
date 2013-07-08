<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the Age of Pericles variant for webDiplomacy

	The Age of Pericles variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Age of Pericles variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

	---

	Changelog:
	1.0:   initial version
	1.0.1: small map-fixes...
	1.0.5: new starting date...
	1.0.6: NAmecahnge: Perikles -> Pericles
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AgeOfPericlesVariant extends WDVariant {
	public $id         = 78;
	public $mapID      = 78;
	public $name       = 'AgeOfPericles';
	public $fullName   = 'Age of Pericles';
	public $description= 'War in the age of Pericles';
	public $author     = 'Mister X & Michael Golbe';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.1';
	public $codeVersion= '1.0.6';
	public $homepage   = 'http://www.dipwiki.com/?title=Pericles';

	public $countries=array('Aetolia','Arcolia','Attica','Boeotia','Elia','Laconia','Messenia');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'AgeOfPericles';
		$this->variantClasses['adjudicatorPreGame'] = 'AgeOfPericles';
		$this->variantClasses['OrderInterface']     = 'AgeOfPericles';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 20;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		return ( $turn % 2 ? "Autumn, " : "Spring, " ).(-1*(floor($turn/2) - 480))." BC.";
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			return ( turn%2 ? "Autumn, " : "Spring, " )+(-1*(Math.floor(turn/2) - 480)) +" BC.";
		};';
	}
}

?>