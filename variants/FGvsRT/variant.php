<?php
/*
	Copyright (C) 2010 Cian O Rathaille

	This file is part of the France-Germany vs Russia-Turkey variant for webDiplomacy

	The France-Germany vs Russia-Turkey variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France-Germany vs Russia-Turkey variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Rules for the The France-Germany vs Russia-Turkey Variant:
	Same as Std. Diplomacy, but only 2 players playing 4 Nations - based on France Vs Austria by Oliver Auth

	Changelog:
	1.0: initial version  - derived from France Versus Austria Version 1.0.1
	1.0.1: small adjustments, maxbet = 1
	1.0.2: fixed: spelling error on smallmap
	1.0.3: fixed: wrong sc on smallmap
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FGvsRTVariant extends WDVariant {
	public $id         =26;
	public $mapID      =26;
	public $name       ='FGvsRT';
	public $fullName   ='Frankland Vs Juggernaut';
	public $description='The standard Diplomacy map of Europe, but each player take two countries France and Germany against Russia and Turkey.';
	public $adapter    = 'Orathaic';
	public $version    = '1.0.3';

	public $countries=array('Frankland','Juggernaut');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'FGvsRT';
		$this->variantClasses['adjudicatorPreGame'] = 'FGvsRT';
		$this->variantClasses['processMember']      = 'FGvsRT';		
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