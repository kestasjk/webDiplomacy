<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the MateAgainstMate variant for webDiplomacy

	The MateAgainstMate variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The MateAgainstMate variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MateAgainstMateVariant_adjudicatorPreGame extends adjudicatorPreGame
{
	// Set the staring units:
	protected $countryUnits = array(
		'Indonesia'=> array('Jakarta (North Coast)'=>'Fleet' ,'Surabaya' =>'Fleet' ,'Bali' =>'Fleet', 'Irian Jaya' =>'Fleet'),
		'Western Australia' => array('Broome' =>'Fleet' ,'Kalgoorlie' =>'Army' ,'Perth' =>'Fleet'),
		'South Australia' => array('Adelaide' =>'Fleet' ,'Alice Springs' =>'Army' ,'Darwin' =>'Fleet'),
		'Tasmania'=> array('Launceston'=>'Fleet' ,'Hobart' =>'Fleet' ,'Antarctic Mining Territory' =>'Fleet'),
		'New Zealand' => array('Christchurch' =>'Fleet' ,'Auckland'=>'Fleet' ,'Wellington'=>'Army'),
		'Victoria' => array('Melbourne' =>'Fleet','Geelong' =>'Army' ,'Mildura'=>'Fleet' ,'Bendigo'=>'Army'),
		'New South Wales' => array('Sydney' =>'Fleet' ,'Coffs Harbour' =>'Fleet' ,'Broken Hill'=>'Army','Albury'=>'Army'),
		'Queensland' => array('Brisbane' =>'Fleet' ,'Cairns' =>'Fleet','Mount Isa'=>'Army'),
		'Neutral units'=> array('East Timor'=>'Army','New Caledonia'=>'Army')
	);
}
