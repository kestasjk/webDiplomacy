<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the BalkanWarsVI variant for webDiplomacy

	The BalkanWarsVI variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The BalkanWarsVI variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class BalkanWarsVIVariant_adjudicatorPreGame extends adjudicatorPreGame
{
	protected $countryUnits = array(
//		'Albania' => array(                                                                    ),
		'Bulgaria'=> array('Sofia'    =>'Army' , 'Varna'    =>'Fleet', 'Plovdiv'       =>'Army'),
		'Greece'  => array('Solonika' =>'Army' , 'Sparta'   =>'Fleet'                          ),
		'Rumania' => array('Constanta'=>'Fleet', 'Bucharest'=>'Army' , 'Galati'        =>'Army'),
		'Serbia'  => array('Belgrade' =>'Army' , 'Nish'     =>'Army' , 'Skopje'        =>'Army'),
		'Turkey'  => array('Smyrna'   =>'Fleet', 'Izmit'    =>'Fleet', 'Constantinople'=>'Army')
	);
	
}