<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Imperium variant for webDiplomacy

	The Imperium variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Imperium variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Rules for the The Imperium Variant by Andy Schwarz:
	http://www.me-asal.de/imperium/index_eng.htm
	
	Changelog:
	1.0:   initial version
	1.5:   new webdip0.97 variant code
	1.5.1: landbridges allow fleet movement
	1.6:   fixed: forgot to add the build-anywhere code
	1.7:   fixed: corrected buggy build-anywhere code
	1.7.5: small adjustments for the new variant.php
	1.8:   new icons
	1.9:   river-rule implemented (but counts for fleets too)
	1.10:  code cleanup, fixed some adjucation issues
	2.0:   code improvments ruver rule now fully implemented.
	2.0.1: fixed: missing standoffs


*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ImperiumVariant extends WDVariant {
	public $id         =13;
	public $mapID      =13;
	public $name       ='Imperium';
	public $fullName   ='Imperium Diplomacy';
	public $description='The German Tribes invade the Roman Empire';
	public $author     ='Martin Asal';
	public $adapter    ='Oliver Auth / Carey Jensen';
	public $version    ='2.0.1';
	public $homepage   ='http://www.me-asal.de/imperium/index_eng.htm';

	public $countries=array('Alamanni', 'Franci', 'Gothi', 'Hunni', 'Saxones', 'Vandali');

	public function __construct() {
		parent::__construct();

		// The usual variantcode
		$this->variantClasses['adjudicatorPreGame']   = 'Imperium';
		$this->variantClasses['drawMap']              = 'Imperium';
		// Don't crash without home SC's
		$this->variantClasses['panelMembers']         = 'Imperium';
		// Load changed Javascript code for Build anywhere, Custom Icons and Movement changes 
		$this->variantClasses['OrderInterface']       = 'Imperium';
		// Build anywhere
		$this->variantClasses['userOrderBuilds']      = 'Imperium';
		$this->variantClasses['processOrderBuilds']   = 'Imperium';
		// Check for river-support or -retreat
		$this->variantClasses['userOrderDiplomacy']   = 'Imperium';
		$this->variantClasses['userOrderRetreats']    = 'Imperium';
		// New Adjucator that sets a flag for the river-moves
		$this->variantClasses['adjudicatorDiplomacy'] = 'Imperium';
		// Attack over a river has -1 attack
		$this->variantClasses['adjMove']              = 'Imperium';
		// Attack over a river does not break support
		$this->variantClasses['adjSupportHold']       = 'Imperium';
		$this->variantClasses['adjSupportMove']       = 'Imperium';		
		
	}

	// Save the river-moves in a public available array, so the adjucators can access them.
	public $river_moves=array();

	// Both sides of the river:
	public $river_left  = array(1,3,4,5,10,12,16,18,19,30,50,51);
	public $river_right = array(9,42,44,66,75,82,83,86,91);
	
	// Checks if a move $from->$to crosses a river:	
	public function river_move($from,$to) {
		if ((in_array($to, $this->river_left) && in_array($from, $this->river_right)) || (in_array($to, $this->river_right) && in_array($from, $this->river_left)))
			return true;
		else
			return false;
	}
	
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 401);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 401);
		};';
	}	
}

?>