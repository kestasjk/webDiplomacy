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
 * An army which is moving via convoy. Needs extra code to determine the path decision; must
 * be able to traverse convoy chain, making sure not to throw a paradox exception until all 
 * routes are tried and failed (in the event of a paradox)
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjConvoyMove extends adjMove
{
	/**
	 * A convoy chain of dependency nodes. See the comments within checkChain() for
	 * the structure of convoyChain.
	 *
	 * @var array
	 */
	public $convoyChain;
	
	/**
	 * A recursive function to import the unit objects into the chain
	 * array which is otherwise full of IDs
	 *
	 * @param array $units
	 * @param unknown_type[optional] $chain The chain getting processed 
	 */
	public function setUnits(array $units, &$chain=false)
	{
		if ( $chain === false )
		{
			if ( is_array($this->convoyChain) )
			{
				// If it isn't array it was set to false, and that means this convoy has failed
				$this->setUnits($units, $this->convoyChain);
			}
			
			parent::setUnits($units);
			return;
		}
		
		foreach($chain as &$var)
		{
			// &$var means that the chain is edited, and $var isn't just a copy
			
			if ( is_array($var) )
				$this->setUnits($units, $var); // Convert the sub-array into unit objects
			else
				$var = $units[$var]; // Set the ID to be a unit object instead
		}
	}
	
	protected function _path()
	{
		// If the convoy chain was set to false this convoy has already failed
		if ( $this->convoyChain === false ) return false;
		else return $this->checkChain($this->convoyChain);
	}
	
	/**
	 * A recursive function to check whether the convoy chain is a valid path. Returns
	 * true if the sub-chain does have a valid path to the destination, false if the
	 * sub-chain does not
	 *
	 * @param array $chain
	 * @return boolean
	 */
	private function checkChain($chain)
	{
		/*
		 * First off this (sub) chain must contain no dislodged units. If it does
		 * then the (sub) chain is broken, and any sub chains beyond are useless.
		 * 
		 * If this chain contains no dislodged units and there are no arrays in
		 * the chain then the end has been reached, and the path is successful
		 * 
		 * If the chain contains no dislodged units and there are arrays in the
		 * chain then the end has not yet been reached, and further sub chains need
		 * to be traversed
		 * 
		 * [A] ->[B] ->[C] ->{[D] ->[F] ->[End] | [E] ->[End]}
		 * array( A,B,C, array(D,F, array()), array(E, array()) )
		 * 
		 * So to use any sub-arrays you must first check that all units in the current
		 * array are not dislodged, and if a sub-array contains nothing then the
		 * chain is complete 
		 */
		foreach($chain as $unit)
		{
			if ( $unit instanceof adjHold )
			{
				try
				{
					if ( $unit->dislodged() )
						return false;
				}
				catch(adjParadoxException $pe)
				{
					if ( isset($p) ) $p->downSizeTo($pe);
					else $p = $pe;
				}
			}
		}
		
		// If a paradox has been found there's no point in going any deeper into the convoy chain
		if ( isset($p) ) throw $p;
		
		/*
		 * Units checked, check whether the array is empty or if there are sub-arrays to check 
		 */
		$count = 0;
		foreach($chain as $subChain)
		{
			if ( is_array($subChain) )
			{
				try
				{
					$count++;
					if ( $this->checkChain($subChain) )
						return true;
				}
				catch(adjParadoxException $pe)
				{
					if( isset($p) ) $p->downSizeTo($pe);
					else $p = $pe;
				}
				
			}
		}
		
		// The only possible route through the convoy chain is part of a paradox chain
		// A parent chain may still be able to avert this paradox by finding another route
		if ( isset($p) ) throw $p;
		
		// There was nothing inside the sub-chain, which means we have found the end of the chain!
		if ( $count == 0 ) return true;
		else return false;
	}
}

?>