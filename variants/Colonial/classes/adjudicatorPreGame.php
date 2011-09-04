<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Colonial variant for webDiplomacy

	The Colonial variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Colonial variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ColonialVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Britain' => array('Madras'  =>'Army' , 'Delhi'         =>'Army' , 'Bombay'    =>'Fleet'   ,
		                   'Aden'    =>'Fleet', 'Hong Kong'     =>'Fleet', 'Singapore' =>'Fleet'  ),
		'China'   => array('Peking'  =>'Army' , 'Canton'        =>'Army' , 'Sinkiang'  =>'Army'    ,
		                   'Shanghai'=>'Army' , 'Manchuria'     =>'Army'                          ),
		'Russia'  => array('Moscow'  =>'Army' , 'Vladivostok'   =>'Army' , 'Omsk'      =>'Army'    ,
		                   'Odessa'  =>'Fleet', 'Port Arthur'   =>'Fleet'                         ),
		'France'  => array('Tongking'=>'Army' , 'Annam'         =>'Fleet', 'Cochin'    =>'Army'   ),
		'Holland' => array('Sumatra' =>'Fleet', 'Java'          =>'Fleet', 'Borneo'    =>'Army'   ),
		'Japan'   => array('Otaru'   =>'Fleet', 'Tokyo'         =>'Fleet', 'Kyoto'     =>'Army'    ,
		                   'Kyushu'  =>'Fleet'                                                    ),
		'Turkey'  => array('Angora'  =>'Army' , 'Constantinople'=>'Fleet', 'Baghdad'    =>'Fleet' ) 
	);

}

?>
