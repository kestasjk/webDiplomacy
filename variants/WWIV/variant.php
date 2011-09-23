<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the WWIV variant for webDiplomacy

	The WWIV variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The WWIV variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial release
	1.1:   big code cleanup
	1.1.1: better warparound code
	1.1.3: color-code fixed
	1.1.5: convoy-code fix
	1.1.6: removed wrong SC on smallmap names.
	1.1.7: update for the convoy-code fix

	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class WWIVVariant extends WDVariant {
	public $id         = 52;
	public $mapID      = 52;
	public $name       = 'WWIV';
	public $fullName   = 'World War IV';
	public $description= 'A variant with an enormous map for 35 players over the whole globe.';
	public $author     = 'Tom Mulledy';
	public $adapter    = 'Carey Jensen / Oliver Auth';
	public $version    = '1.1.6';
	public $homepage   = 'http://www.dipwiki.com/index.php?title=World_War_IV';
	
	public $countries=array('Amazon-Empire', 'Argentina', 'Australia', 'Brazil', 'California', 'Canada', 'Catholica', 'Central-Asia', 'Colombia', 'Congo', 'Cuba', 'Egypt', 'Germany', 'Illinois', 'Inca-Empire', 'India', 'Indonesia', 'Iran', 'Japan', 'Kenya', 'Manchuria', 'Mexico', 'Nigeria', 'Oceania', 'Philippines', 'Quebec', 'Russia', 'Sichuan-Empire', 'Song-Empire', 'South-Africa', 'Texas', 'Thailand', 'Turkey', 'United-Kingdom', 'United-States');

	public function __construct() {
		parent::__construct();

		// Move flags behind the units:
		$this->variantClasses['drawMap']            = 'WWIV';
		
		// Custom icons for each country
		$this->variantClasses['drawMap']            = 'WWIV';
		
		// Map is build from 2 images (because it's more than 256 land-territories)
		$this->variantClasses['drawMap']            = 'WWIV';

		// Map is Warparound
		$this->variantClasses['drawMap']            = 'WWIV';
		
		// Bigger message-limit because of that much players:
		$this->variantClasses['Chatbox']            = 'WWIV';
		
		// Zoom-Map
		$this->variantClasses['panelGameBoard']     = 'WWIV';
		$this->variantClasses['drawMap']            = 'WWIV';

		// Write the countryname in global chat
		$this->variantClasses['Chatbox']            = 'WWIV';

		// EarlyCD: Set players that missed the first phase as Left
		$this->variantClasses['processGame']        = 'WWIV';

		// Custom start
		$this->variantClasses['adjudicatorPreGame'] = 'WWIV';
		$this->variantClasses['processOrderBuilds'] = 'WWIV';
		$this->variantClasses['processGame']        = 'WWIV';

		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'WWIV';
		$this->variantClasses['userOrderBuilds']    = 'WWIV';
		$this->variantClasses['processOrderBuilds'] = 'WWIV';
		
		// Split Home-view after 9 countries for better readability:
		$this->variantClasses['panelMembersHome']   = 'WWIV';

		// Convoy-Fix
		$this->variantClasses['OrderInterface']     = 'WWIV';
		$this->variantClasses['userOrderDiplomacy'] = 'WWIV'; 
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 50;
	}

	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2101);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2101);
		};';
	}
}

?>