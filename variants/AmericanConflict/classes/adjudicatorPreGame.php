<?php
/*
	Copyright (C) 2012 Gavin Atkinson

	This file is part of the American Conflict variant for webDiplomacy

	The American Conflict variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The American Conflict variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

*/
defined('IN_CODE') or die('This script can not be run by itself.');

class AmericanConflictVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Russia'=> array('Anchorage' =>'Fleet','Vladivostok'     =>'Fleet' ,'Archangelsk'         =>'Fleet' ),
		'Confederate States'         => array('Tennessee'     =>'Army','Richmond'       =>'Army' ,'Louisiana'      =>'Fleet' ),
		'United States'       => array('Washington DC'   =>'Army' ,'Chicago'   =>'Army' ,'Massachusetts'       =>'Fleet' ,'San Francisco'       =>'Fleet' ),
		'England'        => array('Montreal'=>'Army' ,'Vancouver'=>'Army' ,'Portsmouth'=>'Fleet' ,'Kingston'     =>'Fleet'),
		'France'          => array('La Rochelle'  =>'Fleet' ,'Veracruz'      =>'Fleet','Guadalajara'            =>'Army'),
		'Spain'            => array('Holguin'    =>'Fleet','Cadiz'     =>'Fleet' ,'Puerto Rico'          =>'Fleet')
	);

}