<?php
/*
	Copyright (C) 2011 Carey Jensen / Oliver Auth

	This file is part of the Chaoctopi variant for webDiplomacy

	The Chaoctopi variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaoctopi variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release based on the Chaos variant 1.7 and the Octopus variant 1.0.1 (by Emmanuele)
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ChaoctopiVariant extends WDVariant {
	public $id         =54;
	public $mapID      =54;
	public $name       ='Chaoctopi';
	public $fullName   ='Chaoctopi';
	public $description=' The Chaoctopi variant is a Chaos game with Octopus moves.';
	public $author     ='kaner406';
	public $adapter    ='Emmanuele Ravaioli / Carey Jensen / Oliver Auth';
	public $version    ='1.0';

	public $countries=array(
		'Ankara'       , 'Belgium', 'Berlin'  , 'Brest'    , 'Budapest', 'Bulgaria'  , 'Constantinople', 'Denmark', 'Edinburgh',
		'Greece'       , 'Holland', 'Kiel'    , 'Liverpool', 'London'  , 'Marseilles', 'Moscow'        , 'Munich' , 'Naples'   ,
		'Norway'       , 'Paris'  , 'Portugal', 'Rome'     , 'Rumania' , 'Serbia'    , 'Sevastopol'    , 'Smyrna' , 'Spain'    ,
		'St-Petersburg', 'Sweden' , 'Trieste' , 'Tunis'    , 'Venice'  , 'Vienna'    , 'Warsaw'                                );
		
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Chaoctopi';
		$this->variantClasses['adjudicatorPreGame'] = 'Chaoctopi';
		$this->variantClasses['OrderInterface']     = 'Chaoctopi';
		$this->variantClasses['processGame']        = 'Chaoctopi';
		$this->variantClasses['userOrderBuilds']    = 'Chaoctopi';
		$this->variantClasses['processOrderBuilds'] = 'Chaoctopi';
		$this->variantClasses['Chatbox']            = 'Chaoctopi';
		$this->variantClasses['panelMembersHome']   = 'Chaoctopi';
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
