<?php
/*
	Copyright (C) 2013 Firehawk

	This file is part of the Colonial1885 variant for webDiplomacy

	The Colonial1885 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Colonial1885 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Colonial1885Variant_adjudicatorPreGame extends adjudicatorPreGame
{
	protected $countryUnits = array(
		'Britain'  => array('Edinburgh'=>'Fleet','London'=>'Fleet','Liverpool'=>'Army','Gibraltar'=>'Fleet','Gold Coast'=>'Army','Singapore'=>'Fleet','Aden'=>'Fleet','Cape Town'=>'Fleet','Hong Kong'=>'Fleet','Bombay'=>'Fleet','Delhi'=>'Army','New South Wales'=>'Army'),
		'France'  => array('Brest'=>'Fleet','Paris'=>'Army','Marseilles'=>'Army','Algiers'=>'Army','Senegambia'=>'Fleet','Tongking'=>'Fleet','Saigon'=>'Army','Madagascar'=>'Fleet'),
		'Germany'  => array('Kiel'=>'Fleet','Berlin'=>'Army','Posen'=>'Army','Munich'=>'Army','Kamerun'=>'Army','Dar es Salaam'=>'Fleet','Wilhelmsland'=>'Fleet'),
		'Austria'  => array('Trieste'=>'Fleet','Sarajevo'=>'Army','Budapest'=>'Army','Vienna'=>'Army'),
		'Italy'  => array('Venice'=>'Fleet','Rome'=>'Army','Sicily'=>'Army','Naples'=>'Fleet','Eritrea'=>'Army'),
		'Holland'  => array('Holland'=>'Fleet','Borneo'=>'Army','Sumatra'=>'Fleet','Java'=>'Fleet','Transvaal'=>'Army'),
		'Russia'  => array('St. Petersburg (South Coast)'=>'Fleet','Moscow'=>'Army','Warsaw'=>'Army','Sevastopol'=>'Fleet','Orenburg'=>'Army','Vladivostok'=>'Fleet','Port Arthur'=>'Fleet','Irkutsk'=>'Army','Omsk'=>'Army','Rostov'=>'Army'),
		'Turkey'  => array('Constantinople'=>'Army','Smyrna'=>'Fleet','Angora'=>'Fleet','Cairo'=>'Army','Baghdad'=>'Fleet'),
		'China'  => array('Sinkiang'=>'Army','Peking'=>'Army','Shanghai'=>'Army','Kashgar'=>'Army','Nanking'=>'Army','Mukden'=>'Army','Canton'=>'Army'),
		'Japan' => array('Kyoto'=>'Fleet','Sapporo'=>'Fleet','Kyushu'=>'Fleet','Tokyo'=>'Army')
	);
}