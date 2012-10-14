<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Haven variant for webDiplomacy

	The Haven variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Haven variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.1:   add countrynames in chatbox
	1.1.1: map improvements
	1.1.2: fixed: wrong sc on the map.
	1.1.3: fixed: wrong bordertype
	1.1.4: fixed: missing underworld link
	1.1.5: fixed: border issue
	1.1.6: fixed: missing underworld link
	1.1.7: fixed: missing underworld link
	1.1.11: small improvements, so the map work with the edit-tool

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HavenVariant extends WDVariant {
	public $id         = 51;
	public $mapID      = 51;
	public $name       = 'Haven';
	public $fullName   = 'Haven';
	public $description= 'Epic 19 player battle in a fantasy world.';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.1';
	public $codeVersion= '1.1.11';
	public $author     = 'Michael Sims';
	public $homepage   = 'http://www.dipwiki.com/index.php?title=Haven';

	public $countries=array('Archers','Barbarians','Centaurs','Dwarves','Elves','Faeries','Gnomes','Hobbits','Knights','Leprechauns','Magicians','Nomads','Ogres','Pirates','Rogues','Samurai','Trolls','Undead','Wizards');

	// Telmar (204), Spiral Castle (198), Dargaard Keep (47), The Julianthes (208), Myth Drannor (141), Never Never Land (144), Whoville (243), Krikkit (122), Spirit Pond (199)
	public $eternal = '"204","198","47","208","141","144","243","122","199"';

	public function __construct() {
		parent::__construct();

		// Unit placement
		$this->variantClasses['adjudicatorPreGame'] = 'Haven';
		
		// Many custom drawmap functions
		$this->variantClasses['drawMap']            = 'Haven';
		
		// Display largemap in smallmap too
		$this->variantClasses['panelGameBoard']     = 'Haven';

		// Display largemap in smallmap too
		$this->variantClasses['Chatbox']            = 'Haven';
		
		// Load the code for the eternal SCs.
		$this->variantClasses['OrderInterface']     = 'Haven';
		$this->variantClasses['processOrderBuilds'] = 'Haven';
		$this->variantClasses['userOrderBuilds']    = 'Haven';
		
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 52;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1);
		};';
	}
}

?>