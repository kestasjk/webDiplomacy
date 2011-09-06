<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Migraine variant for webDiplomacy

	The Migraine variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Migraine variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
	Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Rules for the Migraine Variant by Stephen D. Koehler
	http://www.variantbank.org/results/rules/m/migraine.htm
	
	Changelog:
	1.0:   initial release by Carey Jensen
	1.5:   new webdip v.98 code by Oliver Auth
	1.5.1: small adjustments for the new variant.php code
	1.5.2: color fixed + wraparount code
	1.5.3: fixed a problem with the terrstatus not changing on new turn
	1.5.4: color fix
	1.5.5: display the custom icons in the orderinterface
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MigraineVariant extends WDVariant {
	public $id         = 21;
	public $mapID      = 21;
	public $name       ='Migraine';
	public $fullName   ='Migraine';
	public $description='8 Player Diplomacy on a symetrical map';
	public $author     ='Stephen D. Koehler';
	public $adapter    ='Carey Jensen / Oliver Auth';
	public $version    ='1.5.5';
	public $homepage   ='http://www.variantbank.org/results/rules/m/migraine.htm';

	public $countries=array('Beta', 'Delta', 'Gamma', 'Kappa', 'Lambda', 'Sigma', 'Theta', 'Zeta');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Migraine';
		$this->variantClasses['adjudicatorPreGame'] = 'Migraine';
		$this->variantClasses['processGame']        = 'Migraine';
		$this->variantClasses['OrderInterface']     = 'Migraine';		
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 3499);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 3499);
		};';
	}
}

?>