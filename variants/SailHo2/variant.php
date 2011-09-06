<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Sail Ho II variant for webDiplomacy

	The Sail Ho variant II for webDiplomacy" is free software: you can 
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Sail Ho II variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

	---
	
	Rules for the Sail Ho II Variant by Michael "Tarzan" Golbe:
	http://www.variantbank.org/results/rules/s/sailho2.htm

	Changelog:
	1.0:     initial release (all the hard work) (by Carey Jensen)
	1.1:     new webdip v.97 code (easy part) (by Oliver Auth)
	1.1.1:   fixed a color problem
	1.1.2:   missing link
	1.1.3:   small change for the new variant.php
	1.1.3.1: Borderfix
	1.1.4:   New text-overlay
	1.2:     New icons  
	1.3:   New icons in orderinterface too and map improvements
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SailHo2Variant extends WDVariant {
	public $id         =16;
	public $mapID      =16;
	public $name       ='SailHo2';
	public $fullName   ='Sail Ho II';
	public $description='Fun map with emphasis on convoy orders for 4 player';
	public $author     ='Michael "Tarzan" Golbe';
	public $adapter    ='Carey Jensen / Oliver Auth';
	public $version    ='1.3';
	public $homepage   ='http://www.variantbank.org/results/rules/s/sailho2.htm';

	public $countries=array('East','North','South','West');
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'SailHo2';
		$this->variantClasses['adjudicatorPreGame'] = 'SailHo2';
		$this->variantClasses['OrderInterface']     = 'SailHo2';

	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 9;
	}
	

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(-1*(floor($turn/2) - 400))." BC.";
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(-1*(Math.floor(turn/2) -400)) +" BC.";
		};';
	}
}

?>