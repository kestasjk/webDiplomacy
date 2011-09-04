<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Based on the same interface as drawMap, but the final product is an XML file
 * rather than a PNG image. For use with future map interface projects; all the
 * info needed to draw a map is here, all that is needed for interactive maps is
 * an order submission interface.
 *
 * @package Map
 */
class drawMapXML
{
	private $territories=array();
	private $retreatSuccessLock=array();

	public function addTerritoryNames() { }

	private static function filterTerrID($terrName)
	{
		$terrName = strtolower($terrName);
		$terrName = str_replace(' ', '_', $terrName);
		$terrName = str_replace('-', '_', $terrName);
		$terrName = str_replace('.', '', $terrName);
		$terrName = str_replace('(', '', $terrName);
		$terrName = str_replace(')', '', $terrName);
		return $terrName;
	}

	private function enterTerritory($terrName)
	{
		if ( isset($this->territories[$terrName]) ) return;

		$fullTerrName = $terrName;
		$terrName = self::filterTerrID($terrName);

		$this->territories[$fullTerrName] = array('id'=>$terrName);
	}

	public function colorTerritory($terrName, $countryID)
	{
		$this->enterTerritory($terrName);

		$this->territories[$terrName]['nation'] = $countryID;
	}

	public function countryIDFlag($terrName, $countryID)
	{
		$this->enterTerritory($terrName);

		$this->territories[$terrName]['owner'] = $countryID;
	}

	public function addUnit($terrName, $unitType)
	{
		$this->enterTerritory($terrName);

		$this->territories[$terrName]['unit'] = strtolower($unitType);
	}

	public function drawMove($fromTerrID, $toTerrID, $success)
	{
		$this->enterTerritory($fromTerrID);

		$this->territories[$fromTerrID]['order'] = 'move';
		$this->territories[$fromTerrID]['to'] = self::filterTerrID($toTerrID);

		if ( !isset($this->retreatSuccessLock[$fromTerrID]) )
			$this->territories[$fromTerrID]['success'] = ($success?'true':'false');
	}

	public function drawSupportHold($fromTerrID, $toTerrID, $success)
	{
		$this->enterTerritory($fromTerrID);

		$this->territories[$fromTerrID]['order'] = 'supporthold';
		$this->territories[$fromTerrID]['to'] = self::filterTerrID($toTerrID);
		$this->territories[$fromTerrID]['from'] = self::filterTerrID($fromTerrID);

		if ( !isset($this->retreatSuccessLock[$fromTerrID]) )
			$this->territories[$fromTerrID]['success'] = ($success?'true':'false');
	}

	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		$this->enterTerritory($terrID);

		$this->territories[$terrID]['order'] = 'supportmove';
		$this->territories[$terrID]['to'] = self::filterTerrID($toTerrID);
		$this->territories[$terrID]['from'] = self::filterTerrID($fromTerrID);

		if ( !isset($this->retreatSuccessLock[$fromTerrID]) )
			$this->territories[$fromTerrID]['success'] = ($success?'true':'false');
	}

	public function drawConvoy($terrID, $fromTerrID, $toTerrID)
	{
		$this->enterTerritory($terrID);

		$this->territories[$terrID]['order'] = 'convoy';
		$this->territories[$terrID]['to'] = self::filterTerrID($toTerrID);
		$this->territories[$terrID]['from'] = self::filterTerrID($fromTerrID);
	}

	public function drawRetreat($fromTerrID, $toTerrID, $success)
	{
		$this->enterTerritory($fromTerrID);

		$this->territories[$fromTerrID]['order'] = 'retreat';
		$this->territories[$fromTerrID]['retreat'] = self::filterTerrID($toTerrID);

		$this->territories[$fromTerrID]['success'] = ($success?'true':'false');
		$this->retreatSuccessLock[$fromTerrID] = true;
	}

	public function drawDislodgedUnit($terrID) { }

	public function drawStandoff($terrName) { }


	public function drawCreatedUnit($terrID, $unitType)
	{
		$this->enterTerritory($terrID);

		$this->territories[$terrID]['winter'] = 'build';
		$this->territories[$terrID]['unit'] = strtolower($unitType);
	}

	public function drawDestroyedUnit($terrID)
	{
		$this->enterTerritory($terrID);

		$this->territories[$terrID]['winter'] = 'disband';
	}

	public function caption($text)
	{

	}

	public function write($filename)
	{
		$buffer = "<mapupdate>\n";

		foreach($this->territories as $territory)
		{
			if ( !count($territory) ) continue;

			$buffer .= "\t".'<terrID ';

			foreach($territory as $name=>$value)
			{
				$buffer .= $name.'="'.$value.'" ';
			}

			$buffer .= '/>'."\n";
		}

		$buffer .= "</mapupdate>";

		file_put_contents($filename, $buffer);
	}
}

?>