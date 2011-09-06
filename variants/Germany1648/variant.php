<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli

	This file is part of the Germany1648 variant for webDiplomacy

	The Germany1648 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Germany1648 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0: initial version
	1.0.1: border fixes
	1.0.2: border fixes
	1.0.3: border fixes
	1.0.4: border fixes
	1.0.5: border fixes
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Germany1648Variant extends WDVariant {
	public $id         =36;
	public $mapID      =36;
	public $name       ='Germany1648';
	public $fullName   ='Germany 1648';
	public $description='Seven powerful noble Families fight for the conquer of the Free Imperial Cities and the Holy German Empire.';
	public $author     ='Emmanuele Ravaioli (Tadar Es Darden)';
	public $adapter    ='Emmanuele Ravaioli / Oliver Auth';
	public $version    ='1.0.5';

	public $countries=array(
		'Austrian Habsburg','Spanish Habsburg','Wettin','Bavarian Wittelsbach','Palatinate Wittelsbach','Hohenzollern','Ecclesiastic Lands','Free Imperial Cities');

	public function __construct() {
		parent::__construct();

		// Set starting Units, save the Unit-ID of the "No-Move"-unit
		$this->variantClasses['adjudicatorPreGame'] = 'Germany1648';
		
		// Color the territories, Draw the "neutral" SC's
		$this->variantClasses['drawMap']            = 'Germany1648';

		// Implement the "neutral units"
		$this->variantClasses['Chatbox']           = 'Germany1648';
		$this->variantClasses['processGame']       = 'Germany1648';
		$this->variantClasses['panelMembers']      = 'Germany1648';
		$this->variantClasses['panelMembersHome']  = 'Germany1648';
		$this->variantClasses['panelGameBoard']    = 'Germany1648';	
		$this->variantClasses['panelGame']         = 'Germany1648';	

		// Build anywhere
		$this->variantClasses['userOrderBuilds']    = 'Germany1648';
		$this->variantClasses['processOrderBuilds'] = 'Germany1648';
		$this->variantClasses['OrderInterface']     = 'Germany1648';	
		
	}
	
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1648);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1648);
		};';
	}
}
?>
