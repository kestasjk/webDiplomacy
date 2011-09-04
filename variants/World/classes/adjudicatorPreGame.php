<?php
/*
	Copyright (C) 2010 Carey Jensen / Kestas J. Kuliukas / Oliver Auth

	This file is part of the World variant for webDiplomacy

	The World variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The World variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/
class WorldVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Argentina'         => array('Santa Cruz'     =>'Army' , 'Buenos Aires'         =>'Fleet', 'Chile'            =>'Fleet'),
		'Brazil'            => array('Brasillia'      =>'Army' , 'Rio de Janeiro'       =>'Army' , 'Recife'           =>'Fleet'),
		'China'             => array('Beijing'        =>'Army' , 'Shanghai'             =>'Army' , 'Guangzhou'        =>'Fleet'),
		'Europe'            => array('Germany'        =>'Army' , 'France (North Coast)' =>'Fleet', 'Italy'            =>'Fleet'),
		'Frozen-Antarctica' => array('Casey'          =>'Fleet', 'Leningradskaya'       =>'Fleet', 'Mawson'           =>'Fleet'),
		'Ghana'             => array('Ghana'          =>'Army' , 'Mali'                 =>'Army' , 'Guinea'           =>'Fleet'),
		'India'             => array('Calcutta'       =>'Army' , 'Delhi'                =>'Army' , 'Bombay'           =>'Fleet'),
		'Kenya'             => array('Kenya'          =>'Army' , 'Uganda'               =>'Army' , 'Tanzania'         =>'Fleet'),
		'Libya'             => array('Libya'          =>'Army' , 'North Sudan'          =>'Army' , 'Egypt'            =>'Fleet'),
		'Near-East'         => array('Iraq'           =>'Army' , 'Saudi Arabia'         =>'Army' , 'Syria'            =>'Army' ),
		'Pacific-Russia'    => array('East Siberia'   =>'Army' , 'Yakutsk (South Coast)'=>'Fleet', 'Vladivostok'      =>'Army' ),
		'Quebec'            => array('Quebec'         =>'Army' , 'Newfoundland'         =>'Fleet', 'Ontario'          =>'Fleet'),
		'Russia'            => array('Belorussia'     =>'Army' , 'Saint Petersburg'     =>'Army' , 'Moscow'           =>'Army' ),
		'South-Africa'      => array('Namibia'        =>'Army' , 'Sanae IV'             =>'Fleet', 'South Africa'     =>'Fleet'),
		'USA'               => array('Texas'          =>'Army' , 'California'           =>'Fleet', 'Florida'          =>'Fleet'),
		'Western-Canada'    => array('Yukon Territory'=>'Army' , 'Northwest Territories'=>'Fleet', 'British Columbia' =>'Fleet'),
		'Oz'                => array('New South Wales'=>'Fleet', 'Victoria'             =>'Fleet', 'Western Australia'=>'Fleet')
	);

}