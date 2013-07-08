<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 8-Player variant for webDiplomacy

	The South America 8-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The South America 8-Player variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SouthAmerica8Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Argentina' => array('Buenos Aires'=>'Fleet','Cordoba'  =>'Army' ,'Salta'=>'Army'),
		'Bolivia'   => array('Antofagasta' =>'Fleet','La Paz'   =>'Army'),
		'Brazil'    => array('Manaus'      =>'Army' ,'Slv'      =>'Fleet','Rio de Janeiro'=>'Army','Sao Paulo'=>'Fleet'),
		'Chile'     => array('Valparaiso'  =>'Fleet','Santiago' =>'Army'),
		'Colombia'  => array('Cartagena'   =>'Fleet','Bogota'   =>'Army ','Cali'=>'Fleet'),
		'Paraguay'  => array('Concepcion'  =>'Army' ,'Asuncion' =>'Army'),
		'Peru'      => array('Trujillo'    =>'Army' ,'Lima'     =>'Fleet'),
		'Venezuela' => array('Caracas'     =>'Fleet','Maracaibo'=>'Army'),
	);

}

?>
