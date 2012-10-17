<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Mars variant for webDiplomacy

	The Mars variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Mars variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MarsVariant extends WDVariant {
	public $id         =80;
	public $mapID      =80;
	public $name       ='Mars';
	public $fullName   ='Mars';
	public $description='Martian Warfare';
	public $author     ='kaner406';
	public $adapter    ='kaner406 & Oliver Auth';
	public $version    ='1.0';
	public $codeVersion='1.0';

	public $countries=array('Amazonia','Mareotia','Noachtia','Cydonia','Arkadia','Alborian');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Mars';
		$this->variantClasses['adjudicatorPreGame'] = 'Mars';

// Start with a build phase:
		$this->variantClasses['adjudicatorPreGame'] = 'Mars';
		$this->variantClasses['processGame']        = 'Mars';

// Allow for some coasts to convoy
		$this->variantClasses['OrderInterface']     = 'Mars';
		$this->variantClasses['userOrderDiplomacy'] = 'Mars';

// Map is Warparound
		$this->variantClasses['drawMap']            = 'Mars';
				
// Zoom-Map
		$this->variantClasses['panelGameBoard']     = 'Mars';
		$this->variantClasses['drawMap']            = 'Mars';

// Transform command
		$this->variantClasses['drawMap']            = 'Mars';
		$this->variantClasses['OrderArchiv']        = 'Mars';
		$this->variantClasses['OrderInterface']     = 'Mars';
		$this->variantClasses['processOrderDiplomacy'] = 'Mars';
		$this->variantClasses['userOrderDiplomacy'] = 'Mars';
		
// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Mars';
		$this->variantClasses['processOrderBuilds'] = 'Mars';
		$this->variantClasses['userOrderBuilds']    = 'Mars';

	}

	/* Coasts that allow convoying.
	*  North Lyot Coast(10), South Lyot Coast(13), Cape Phlegra(126), Mt. Elysium(122), Mt. Albor(75), Chryse(37), Lunae Planum(38), North Chaos(41), North Tyrrhena Plateau(112), South Tyrrhena Plateau(113), North Hamarkis(108), Mils Island(22), Orcus Islands(24), Elysium Massiv(125), Mt. Elysium (East Coast)(123), Mt. Elysium (West Coast)(124), Cape Phlegra (North Coast)(127), Cape Phlegra (South Coast)(128)
	*/
	public $convoyCoasts = array ('10','13','126','122','75','37','38','41','112','113','108','22','24','125','123','124','127','128');



	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 21;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2150);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2150);
		};';
	}
}

?>