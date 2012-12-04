<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Imperial2 variant for webDiplomacy

	The Imperial2 variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Imperial2 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Imperial2Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Austria' => array('Croatia'        => 'Fleet', 'Vienna'    => 'Army' , 'Venice'      => 'Army' , 'Budapest'         => 'Army' ),
		'Brazil'  => array('Rio de Janeiro' => 'Fleet', 'Recife'    => 'Fleet', 'Brasilia'    => 'Army' , 'Oliveira'         => 'Army' ),
		'Britain' => array('London'         => 'Fleet', 'Edinburgh' => 'Fleet', 'Nova Scotia' => 'Fleet', 'Falkland Islands' => 'Fleet',
				           'Gibraltar'      => 'Fleet', 'Aden'      => 'Fleet', 'Bombay'      => 'Fleet', 'Singapore'        => 'Fleet',
						   'Hong Kong'      => 'Fleet', 'Dublin'    => 'Army' , 'Quebec'      => 'Army' , 'Vancouver'        => 'Army' ,
						   'Delhi'          => 'Army' , 'Perth'     => 'Army'),
		'China'   => array('Peking'         => 'Army' , 'Shanghai'  => 'Army' , 'Canton'      => 'Army' , 'Manchuria'        => 'Army' ,
		                   'Wuhan'          => 'Army' , 'Chungking' => 'Army' , 'Sinkiang'    => 'Army'),
		'CSA'     => array('Richmond'       => 'Army' , 'Atlanta'   => 'Army' , 'Austin'      => 'Army' , 'New Orleans'      => 'Army'),
		'France'  => array('Paris'          => 'Army' , 'Marseilles'=> 'Army' , 'Grain Coast' => 'Army' , 'Cambodia'         => 'Army' ,
		                   'Nantes'         => 'Fleet', 'Corsica'   => 'Fleet', 'Cayenne'     => 'Fleet', 'Monrovia'         => 'Fleet',		   
						   'Society Islands'=> 'Fleet', 'Cochin'    => 'Fleet'),
		'Holland' => array('Holland'        => 'Fleet', 'Cape Town' => 'Fleet', 'Ceylon'      => 'Fleet', 'Sumatra'          => 'Fleet',
		                   'Java'           => 'Fleet', 'Paramaribo'=> 'Army' , 'Transvaal'   => 'Army' , 'Borneo'           => 'Army'),
		'Japan'   => array('Edo'            => 'Army' , 'Sapporo'   => 'Fleet', 'Kagoshima'   => 'Fleet', 'Okinawa'          => 'Fleet'),
		'Mexico'  => array('Mexico City'    => 'Army' , 'Merida'    => 'Fleet', 'Mazatlan'    => 'Fleet'),
		'Prussia' => array('Berlin'         => 'Army' , 'Prussia'   => 'Army' , 'Silesia'     => 'Army' , 'Ruhr'             => 'Army'),
		'Russia'  => array('Moscow'         => 'Army' , 'Warsaw'    => 'Army' , 'Georgia'     => 'Army' , 'Orenburg'         => 'Army' ,
		                   'Port Arthur'    => 'Fleet', 'Irkutsk'   => 'Army' , 'Vladivostok' => 'Army' , 'Anchorage'        => 'Army' ,
						   'Sevastopol'     => 'Fleet', 'Omsk'      => 'Army' , 'St Petersburg (South Coast)' => 'Fleet'),
		'Turkey'  => array('Constantinople' => 'Army' , 'Cyprus'    => 'Fleet', 'Baghdad'     => 'Fleet', 'Sofia'            => 'Army' ,
                           'Cairo'          => 'Army' , 'Angora (North Coast)'=> 'Fleet'),
		'USA'     => array('Washington DC'  => 'Army' , 'Detroit'   => 'Army' , 'Chicago'     => 'Army' , 'San Francisco'    => 'Army' ,
						   'Oregon'         => 'Fleet', 'New York'  => 'Fleet')
		);
		
}