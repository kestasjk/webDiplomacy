<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli and Oliver Auth

	This file is part of the Battle of Lepanto variant for webDiplomacy

	The Battle of Lepanto variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Battle of Lepanto variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	0.1:   first installment
	0.2:   modified drawMap.php for changing the size and position of the flags behind the units (Oli)
	0.3:   modified again drawMap.php for positioning better the flags behind the units (Emmanuele)
	0.4:   modified again drawMap.php with a function which works both for small and large army; modified map and smallmap to include title of the map; modified army.png (Emmanuele 02/12/2010)
	0.5:   completed links for fleets; modified smallfleet.png and fleet.png; modified colors in drawMap.php (Emmanuele 10/12/2010)
	0.6:   completed rules (Emmanuele 06/01/2011)
	0.8:   coding complete (Oli 09/01/2011)
	0.9:   rules updated; turns displayed as hours in the variant.php file (Emmanuele 10/01/2011)
	0.9.1: turn display updated
	1.0:   release

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class LepantoVariant extends WDVariant {
	public $id         = 41;
	public $mapID      = 41;
	public $name       = 'Lepanto';
	public $fullName   = 'Lepanto';
	public $description= 'Simple 1vs1 challenge based on the famous sea battle between the Holy League and the Ottoman Empire';
	public $author     = 'Emmanuele Ravaioli (Tadar Es Darden)';
	public $adapter    = 'Emmanuele Ravaioli / Oliver Auth';
	public $version    = '1.0';
	public $disabled   = true;

	public $countries=array('Holy League', 'Ottoman Empire');

	public function __construct() {
		parent::__construct();

		// Game setup
		$this->variantClasses['adjudicatorPreGame'] = 'Lepanto';

		// New country-flag, color definitions:
		$this->variantClasses['drawMap']            = 'Lepanto';
		
		// Block movement for the 4 flagships
		$this->variantClasses['userOrderDiplomacy'] = 'Lepanto';
		
		// Build anywhere
		$this->variantClasses['processOrderBuilds'] = 'Lepanto';
		$this->variantClasses['userOrderBuilds']    = 'Lepanto';
		
		// alternate win condition: opponent disband his flagship
		$this->variantClasses['processMembers']     = 'Lepanto';

		// Only winner takes all and fixed bet 1:
		$this->variantClasses['processMember']      = 'Lepanto';
		
		// Load the javascript changes
		$this->variantClasses['OrderInterface']     = 'Lepanto';

	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 0;
	}

	public function turnAsDate($turn) {
		if ( $turn ==-1 ) return "Pre-game";
		if ( $turn == 0 ) return "7 October 1571, the start of the battle";
		if ( $turn == 1 ) return "7 October 1571, half an hour after the start of the battle";
		$hour = ( $turn < 4 ? " hour " : " hours ");
		$half = ( $turn % 2 ? "and a half " : "");
		return ( "7 October 1571, " ).(floor($turn/2)).$hour.$half."after the start of the battle";
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn ==-1 ) return "Pre-game";
			if( turn == 0 ) return "7 October 1571, the start of the battle";
			if( turn == 1 ) return "7 October 1571, half an hour after the start of the battle";
			hour = ( turn < 4 ? " hour " : " hours ");
			half = ( turn % 2 ? "and a half " : "");
			return ( "7 October 1571, " )+(Math.floor(turn/2))+hour+half+"after the start of the battle";
		};';
	}
}

?>