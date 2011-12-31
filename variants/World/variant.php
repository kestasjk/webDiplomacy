<?php
/*
	Copyright (C) 2010 Carey Jensen / Kestas J. Kuliukas / Oliver Auth

	This file is part of the World variant for webDiplomacy

	The World variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The World variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Rules for the World Variant by David Norman';
	http://www.variantbank.org/results/rules/w/worlddip9.htm
	
	This is Version: 1.1.3
	
	Changelog:
	1.0: initial release by Carey Jensen
	1.1: new webdip v.97 code by Kestas J. Kuliukas
	1.1.1: small adjustments by Oliver Auth
	1.1.2: better name-display in chatbox (Like it was on goondip)
	1.1.3: fixed a few border issues, changed some unit placing and darkened
	       the font for some countries for a better readability, Update for webDip 0.99
	1.1.5: Added Carey Jensen's wrap-around code for the drawmap
	1.1.6: Movment fix for some borders
	1.1.7: Movment fix for some borders
	1.1.7.1: Movment fix for some borders
		   
*/

class WorldVariant extends WDVariant {
	public $id         = 2;
	public $mapID      = 2;
	public $name       = 'World';
	public $fullName   = 'World Diplomacy IX';
	public $description= 'A variant with a map which has territories over the whole globe.';
	public $author     = 'David Norman';
	public $adapter    = 'Carey Jensen / Kestas J. Kuliukas / Oliver Auth';
	public $version    = 'IX';
	public $codeVersion= '1.1.7.3';
	public $homepage   = 'http://www.variantbank.org/results/rules/w/worlddip9.htm';

	public $countries=array( 'Argentina','Brazil','China','Europe','Frozen-Antarctica',
	                         'Ghana','India','Kenya','Libya','Near-East','Pacific-Russia',
							 'Quebec','Russia','South-Africa','USA','Western-Canada','Oz');

	public function __construct() {
		parent::__construct();

		// Altered to load the correct resources and colors. Also a change to color-loading to account for
		// the large number of colors in this map.
		$this->variantClasses['drawMap'] = 'World';

		// Altered to build the correct starting units
		$this->variantClasses['adjudicatorPreGame'] = 'World';

		// Altered to display the country name in the global tab
		$this->variantClasses['Chatbox'] = 'World';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 44;
	}	
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2000);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2000);
		};';
	}
}

?>