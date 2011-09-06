<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Chaos variant for webDiplomacy

	The Chaos variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaos variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release by Carey Jensen
	1.5:   new webdip v.97 code by Oliver Auth
	1.5.1: small adjustments to the new variant.php
	1.5.2: better multicolor-chatbox
	1.5.3: fixed: spelling error on smallmap
	1.5.4: fixed: wrong occupationbar colors
	1.5.5: fixed: retreats not cleared after turn
	1.6:   default builds if no order first turn
	1.6.1: fixed: spelling error in default builds
	1.7:   new home-view (with line-breaks)
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ChaosVariant extends WDVariant {
	public $id         =17;
	public $mapID      =17;
	public $name       ='Chaos';
	public $fullName   ='Chaos';
	public $description='The classic map for 34 players.';
	public $author     ='Unknown';
	public $adapter    ='Carey Jensen / Oliver Auth';
	public $version    ='1.7';
	public $homepage   ='http://www.variantbank.org/results/rules/c/chaos.htm';

	public $countries=array(
		'Ankara'       , 'Belgium', 'Berlin'  , 'Brest'    , 'Budapest', 'Bulgaria'  , 'Constantinople', 'Denmark', 'Edinburgh',
		'Greece'       , 'Holland', 'Kiel'    , 'Liverpool', 'London'  , 'Marseilles', 'Moscow'        , 'Munich' , 'Naples'   ,
		'Norway'       , 'Paris'  , 'Portugal', 'Rome'     , 'Rumania' , 'Serbia'    , 'Sevastopol'    , 'Smyrna' , 'Spain'    ,
		'St-Petersburg', 'Sweden' , 'Trieste' , 'Tunis'    , 'Venice'  , 'Vienna'    , 'Warsaw'                                );
		
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Chaos';
		$this->variantClasses['adjudicatorPreGame'] = 'Chaos';
		$this->variantClasses['OrderInterface']     = 'Chaos';
		$this->variantClasses['processGame']        = 'Chaos';
		$this->variantClasses['userOrderBuilds']    = 'Chaos';
		$this->variantClasses['processOrderBuilds'] = 'Chaos';
		$this->variantClasses['Chatbox']            = 'Chaos';
		$this->variantClasses['panelMembersHome']   = 'Chaos';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1901);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1901);
		};';
	}
}

?>