<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the ClassicVS variant for webDiplomacy

	The ClassicVS variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicVS variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CustomCountries_adjudicatorPreGame extends adjudicatorPreGame
{
	function adjudicate()
	{
		global $Game, $Variant, $DB;						
		
		// Do we have Random countries to assign?
		if (in_array('Random',$Game->Variant->countries))
		{
			preg_match('#\((.*?)\)#', $Game->name, $match);
			$options_old=$options=$match[1];
			
			// Make a list of all countries not in use at the moment:
			$avail_countries = '';
			if (strpos($options,'E') === false) $avail_countries .= 'E';
			if (strpos($options,'F') === false) $avail_countries .= 'F';
			if (strpos($options,'I') === false) $avail_countries .= 'I';
			if (strpos($options,'G') === false) $avail_countries .= 'G';
			if (strpos($options,'A') === false) $avail_countries .= 'A';
			if (strpos($options,'T') === false) $avail_countries .= 'T';
			if (strpos($options,'R') === false) $avail_countries .= 'R';
			
			// replace every "?" in the options string with a country-letter
			foreach (str_split($options) as $pos => $country)
				if ($country == '?') 
				{
					$country=$avail_countries[rand(0,strlen($avail_countries)-1)];
					$options[$pos]=$country;
					$avail_countries=str_replace($country,'',$avail_countries);
				}
			
			$new_name=str_replace($options_old,$options,$Game->name);
			$new_name=$DB->escape($new_name);
			
			// Find a unique game name
			$unique = false; $i = 1;
			while ( ! $unique )
			{
				list($count) = $DB->sql_row("SELECT COUNT(id) FROM wD_Games WHERE name='".$new_name.($i > 1 ? '-'.$i : '')."'");
				if ( $count == 0 )
					$unique = true;
				else
					$i++;
			}
			$Game->name = $new_name.($i > 1 ? '-'.$i : '');
			
			$DB->sql_put("UPDATE wD_Games SET name='".$Game->name."' WHERE id=".$Game->id);
			
			// We need to reload the Game-Object to generate the new Memberlist.		
			$Game = $Variant->processGame($Game->id);
		}
		parent::adjudicate();
	}
	
	// Give every country it's new ID and territories.
	protected function assignTerritories() {
		global $DB, $Game;

		foreach ($Game->Variant->countries as $index => $name)
		{
			$targetID = $index+1;
			if ($name == 'England') $sourceID=1;
			if ($name == 'France')  $sourceID=2;
			if ($name == 'Italy')   $sourceID=3;
			if ($name == 'Germany') $sourceID=4;
			if ($name == 'Austria') $sourceID=5;
			if ($name == 'Turkey')  $sourceID=6;
			if ($name == 'Russia')  $sourceID=7;
			$DB->sql_put(
				"INSERT INTO wD_TerrStatus ( gameID, countryID, terrID )
					SELECT ".$Game->id." as gameID, ".$targetID." as countryID, id as terrID
					FROM wD_Territories
				WHERE countryID = ".$sourceID."
					AND mapID=".$Game->Variant->mapID."
					AND (coast='No' OR coast='Parent')
					AND name != 'PreGameCheck'"
			);
		}
	}
	
	protected function assignUnits()
	{
		global $Game;
		foreach($this->countryUnits as $countryName => $params)
			if (!(in_array($countryName,$Game->Variant->countries)))
				unset ($this->countryUnits[$countryName]);
		parent::assignUnits();
	}
}

class ClassicVSVariant_adjudicatorPreGame extends CustomCountries_adjudicatorPreGame
{
	protected $countryUnits = array(
		'England' => array('London'=>'Fleet', 'Edinburgh'=>'Fleet', 'Liverpool'=>'Army'     ),
		'France'  => array('Brest'=>'Fleet' , 'Paris'=>'Army'     , 'Marseilles'=>'Army'    ),
		'Italy'   => array('Venice'=>'Army' , 'Rome'=>'Army'      , 'Naples'=>'Fleet'       ),
		'Germany' => array('Kiel'=>'Fleet'  , 'Berlin'=>'Army'    , 'Munich'=>'Army'        ),
		'Austria' => array('Vienna'=>'Army' , 'Trieste'=>'Fleet'  , 'Budapest'=>'Army'      ),
		'Turkey'  => array('Smyrna'=>'Army' , 'Ankara'=>'Fleet'   , 'Constantinople'=>'Army'),
		'Russia'  => array('Moscow'=>'Army' , 'St. Petersburg (South Coast)'=>'Fleet', 'Warsaw'=>'Army', 'Sevastopol'=>'Fleet')
	);
}
