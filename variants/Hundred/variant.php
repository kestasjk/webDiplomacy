<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Hundred variant for webDiplomacy

	The Hundred variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hundred variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Rules for the The Hundred Variant by Andy Schwarz:
	http://www.variantbank.org/results/rules/h/hundred.htm
	
	This is Version: 1.6.5
	
	Changelog:
	1.0:   initial version
	1.5:   new webdip0.97 variant code
	1.6:   minor fixes
	1.6.5: fixed a bug with the build anywhere code + small adjustments for the new variants.php code
	1.7:   added a rules.html
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HundredVariant extends WDVariant {
	public $id         = 8;
	public $mapID      = 8;
	public $name       = 'Hundred';
	public $fullName   = 'Hundred';
	public $description= 'A Diplomacy Variant for Three Players, Based on the Hundred Years War ';
	public $author     = 'Andy Schwarz';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.7';
	public $homepage   = 'http://www.variantbank.org/results/rules/h/hundred.htm';

	public $countries=array('Burgundy','England','France');
	
	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Hundred';
		$this->variantClasses['adjudicatorPreGame'] = 'Hundred';
		$this->variantClasses['userOrderBuilds']    = 'Hundred';
		$this->variantClasses['processOrderBuilds'] = 'Hundred';
		$this->variantClasses['OrderInterface']     = 'Hundred';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return "Year: "  . round( ( $turn * 5 ) + 1425 );
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( "Year: " )+(Math.floor(turn*5) + 1425);
		};';
	}

}

?>