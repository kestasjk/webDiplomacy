<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Duo variant for webDiplomacy

	The Duo variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Duo variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_adjudicatorPreGame extends adjudicatorPreGame
{
	protected $countryUnits = array(
		'Green' => array('Jadestadt'=>'Army','Schloss Gruenburg'=>'Fleet','Gruenheim'  =>'Army'),
		'Red'   => array('Rotheim'  =>'Army','Zinnoberburg'     =>'Fleet','Karminstadt'=>'Army'),
		'Black' => array('Westberg' =>'Army','Ostberg'          =>'Army' ,'Norterend'  =>'Army' ,'Sund'  =>'Army',
					     'Gawar'    =>'Army','Pirh'             =>'Army' ,'Abaun'      =>'Fleet','Helom'=>'Fleet')
	 );	 
}