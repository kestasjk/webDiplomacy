<?php
/*

    Map Encoding Copyright (c) Figlesquidge 2010

    webDiplomay Copyright (C) 2004-2009 Kestas J. Kuliukas
    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Variant Encoding
 */
class AlacavreVariant extends wdVariant {
	public $id=31; 
	public $mapID=31;
	public $name='Alacavre';
	public $fullName='Alacavre';
	public $description='A balanced 7-player variant. Well worth a game.  If you have any questions, interests, things you want changed on the map etc, then please let us know directly rather than bringing it up on any of the forums';
	public $author='Figlesquidge (assisted by Ghostmaker). contact figlesquidge@gmail.com';

	public $countries=array('Ithsomn','Shinto','Quiom','Maroe','Oz','Namaq','Payashk');

	public function __construct() {
		parent::__construct();

		// drawMap extended for country-colors and loading the classic map images
		$this->variantClasses['drawMap'] = 'Alacavre'; // Unrequired

		/*
		 * adjudicatorPreGame extended to add fair country-balancing, replacing the
		 * default random allocation for classic map games.
		 */
		$this->variantClasses['adjudicatorPreGame'] = 'Alacavre';
		$this->variantClasses['Chatbox'] = 'Alacavre';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(100-floor($turn/2)).'BC';
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(100-Math.floor(turn/2))+"BC";
		};';
	}
}

?>