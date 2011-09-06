<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the 1066 variant for webDiplomacy

	The 1066 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The 1066 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	0.1:   first installment
	0.9:   rules fully implemented
	1.0:   first release
	1.0.1: name change  CrownOfEngland->TenSixtySix
	1.0.2: errorfix in the javascript history
	1.0.3: errorfix javascript order generation didn't display the icons
	1.0.4: fixed a border issue
	1.0.5: better rules.html
	1.1:   Huge code update for FoW and neutral units
	1.1.1: fix for the OrderArchive-Display
	1.1.2: FoW: Hadrian's Wall + used some colors twice on the small and largemap
	1.1.3: Memberlist now sorted by alphabet and not by value



*/

defined('IN_CODE') or die('This script can not be run by itself.');

class TenSixtySixVariant extends WDVariant {
	public $id         = 55;
	public $mapID      = 55;
	public $name       = 'TenSixtySix';
	public $fullName   = '1066';
	public $description= 'The year that shaped British and world history.';
	public $author     = 'Gavin Atkinson (The Ambassador) and Emmanuele Ravaioli (Tadar Es Darden)';
	public $adapter    = 'Gavin Atkinson / Emmanuele Ravaioli / Oliver Auth';
	public $version    = '1.1.2';

	public $countries=array('English', 'Normans', 'Norwegians');

	public function __construct() {
		parent::__construct();
		
		// Setup
		$this->variantClasses['adjudicatorPreGame'] = 'TenSixtySix';
		$this->variantClasses['drawMap']            = 'TenSixtySix';

		// Each country it's own icons:
		$this->variantClasses['drawMap']            = 'TenSixtySix';
		$this->variantClasses['OrderInterface']     = 'TenSixtySix';

		// Build anywhere
		$this->variantClasses['processOrderBuilds'] = 'TenSixtySix';
		$this->variantClasses['userOrderBuilds']    = 'TenSixtySix';
		$this->variantClasses['OrderInterface']     = 'TenSixtySix';

		// Winner need to occupy his own capital and one more 
		// Winchester (England), Oslo (Norway) and Caen (Normandy)
		$this->variantClasses['processMembers']     = 'TenSixtySix';
		
		// Neutral units:
		$this->variantClasses['processMembers']     = 'TenSixtySix';
		$this->variantClasses['processGame']        = 'TenSixtySix';
		$this->variantClasses['OrderArchiv']        = 'TenSixtySix';

		// FogOfWar
		$this->variantClasses['drawMap']              = 'TenSixtySix';
		$this->variantClasses['drawMapXML']           = 'TenSixtySix';
		$this->variantClasses['adjudicatorPreGame']   = 'TenSixtySix';
		$this->variantClasses['adjudicatorDiplomacy'] = 'TenSixtySix';
		$this->variantClasses['panelGameBoard']       = 'TenSixtySix';
		$this->variantClasses['OrderInterface']       = 'TenSixtySix';
		$this->variantClasses['OrderArchiv']          = 'TenSixtySix';
		$this->variantClasses['panelMember']          = 'TenSixtySix';
		$this->variantClasses['panelMemberHome']      = 'TenSixtySix';
		$this->variantClasses['processGame']          = 'TenSixtySix';
		$this->variantClasses['panelMembers']         = 'TenSixtySix';
		$this->variantClasses['panelMembersHome']     = 'TenSixtySix';
		$this->variantClasses['userOrderDiplomacy']   = 'TenSixtySix';
		$this->variantClasses['Maps']                 = 'TenSixtySix';
	}
	
	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units')
			return count($this->countries)+1;
		
		return parent::countryID($countryName);
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1065);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1065);
		};';
	}
}

?>
