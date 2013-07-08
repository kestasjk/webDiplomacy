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

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HavenVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Archers'     => array('Prydain'          =>'Fleet','Llyr'     =>'Fleet','Loxley'       =>'Army' ),
		'Barbarians'  => array('Paras Derval'     =>'Fleet','GuTanoth' =>'Army' ,'Brennin'      =>'Army' ),
		'Centaurs'    => array('Anvard'           =>'Fleet','Tumnus'   =>'Army' ,'Grissel'      =>'Fleet'),
		'Dwarves'     => array('Mount Nimro'      =>'Fleet','Carpantha'=>'Army' ,'Undermountain'=>'Army' ),
		'Elves'       => array('Prekkendorran Hts'=>'Army' ,'Garthim'  =>'Fleet','Gelfling'     =>'Fleet'),
		'Faeries'     => array('Vinyaya'          =>'Fleet','Oz'       =>'Army' ,'Ella'         =>'Fleet'),
		'Gnomes'      => array('Hundred Acre Wood'=>'Fleet','Khemri'   =>'Army' ,'Newa River'   =>'Fleet'),
		'Hobbits'     => array('Mordor'           =>'Fleet','Rohan'    =>'Fleet','Lindon'       =>'Army' ),
		'Knights'     => array('Wing Hove'        =>'Army' ,'Arborlon' =>'Fleet','Grimpen Ward' =>'Fleet'),
		'Leprechauns' => array('Knockshegowna'    =>'Fleet','Gollerus' =>'Fleet','Lubrick'      =>'Army' ),
		'Magicians'   => array('Tarsis'           =>'Army' ,'Ergoth'   =>'Fleet','Krynn'        =>'Fleet'),
		'Nomads'      => array('Fantastica'       =>'Fleet','To-Gai-Ru'=>'Fleet','Auryn'        =>'Fleet'), 
		'Ogres'       => array('Horborixen'       =>'Fleet','Nehwon'   =>'Army' ,'Lankhmar'     =>'Fleet'),
		'Pirates'     => array('Pans Labyrinth'   =>'Army' ,'Riku'     =>'Fleet','The Neverwood'=>'Fleet'),
		'Rogues'      => array('The Silver city'  =>'Army' ,'Grimheim' =>'Army' ,'Ashan'        =>'Fleet'),
		'Samurai'     => array('Traal'            =>'Army' ,'Magrathea'=>'Fleet','Fjord'        =>'Fleet'),
		'Trolls'      => array('Sorrows End'      =>'Fleet','Niflheim' =>'Army' ,'Kahvi'        =>'Fleet'),
		'Undead'      => array('Skullcap'         =>'Fleet','Everglot' =>'Army' ,'Skellington'  =>'Fleet'),
		'Wizards'     => array('Baldurs Gate'     =>'Fleet','Waterdeep'=>'Fleet','Faerun'       =>'Army' )
	);

}