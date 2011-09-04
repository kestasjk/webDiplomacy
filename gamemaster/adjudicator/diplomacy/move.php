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
 * A moving unit class
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjMove extends adjHold
{
	public $preventers = array();
	public $defender;
	
	public function setUnits(array $units)
	{
		if ( isset($this->defender) )
			$this->defender = $units[$this->defender];
		
		foreach($this->preventers as &$preventer)
			$preventer = $units[$preventer];
		parent::setUnits($units);
	}
	
	protected function _holdStrength()
	{
		try
		{
			if ( $this->success() )
			{
				$min = 0;
				$max = 0;
			}
			else
			{
				$min = 1;
				$max = 1;
			}
		}
		catch(adjParadoxException $p)
		{
			$min = 0;
			$max = 1;
		}
		
		$holdStrength = array('min'=>$min,'max'=>$max);
		if ( isset($p) )
			$holdStrength['paradox'] = $p;
		
		return $holdStrength;
	}
	
	protected function _preventStrength()
	{
		try
		{
			// No path; we can't have any effect on the target
			if ( !$this->path() )
				return array('max'=>0,'min'=>0);
		}
		catch(adjParadoxException $p)
		{
			// We might end up path-less
			$min = 0;
		}
		
		if ( isset($this->defender) )
		{
			try
			{
				// If we're in a head to head and our opponent has won we can't prevent anything
				
				if ( $this instanceof adjHeadToHeadMove and $this->defender->success() )
					return array('max'=>0,'min'=>0);
			}
			catch(adjParadoxException $pe)
			{
				// The opponent might succeed
				$min = 0;
				
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		// The max/min array of the units which are supporting my move
		
		$prevent = $this->supportStrength();
		
		if ( isset($min) ) $prevent['min'] = $min;
		
		if ( isset($prevent['paradox']) and isset($p) )
			$prevent['paradox']->downSizeTo($p);
		elseif( isset($p) )
			$prevent['paradox'] = $p;
		
		return $prevent;
	}
	
	protected function _dislodged()
	{
		// This needs to be known first, so the paradox doesn't need to be handled
		if ( $this->success() ) 
			return false;
		else
			return parent::_dislodged();
	}
	
	protected function _success()
	{
		try
		{
			/*
			 * Checking that our attack strength is greater than 0 is a roundabout
			 * way of checking whether we have a path to the destination. If we don't
			 * our attack strength is 0, and empty territories are considered to have
			 * a hold strength of 0.
			 */
			if ( ! $this->compare('attackStrength', '>', 0 ) )
				return false;
		}
		catch(adjParadoxException $p)
		{ }
		
		if ( isset($this->defender) )
		{
			try
			{
				// We're moving head to head
				if ( $this instanceof adjHeadToHeadMove )
				{
					// We're in a head to head and I don't have more attack strength than the defender
					if ( ! $this->compare('attackStrength', '>', array($this->defender, 'defendStrength') ) )
						return false;
				}
				else
				{
					// I do not have more attack strength than the defender has hold strength
					if ( ! $this->compare('attackStrength', '>', array($this->defender, 'holdStrength') ) )
						return false;
				}
			}
			catch(adjParadoxException $pe)
			{
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		// I need to have more attack strength than each preventer
		foreach($this->preventers as $preventer)
		{
			try
			{
				if ( ! $this->compare('attackStrength', '>', array($preventer, 'preventStrength') ) )
					return false;
			}
			catch(adjParadoxException $pe)
			{
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		if ( isset($p) ) throw $p;
		else return true;
	}
	
	private function defenderMoving()
	{
		if ( $this->defender instanceof adjMove and $this->defender->success() )
			return true;
		else
			return false;
	}
	
	protected function _attackStrength()
	{
		try
		{
			// No path; we can't have any effect on the target
			if ( !$this->path() )
				return array('max'=>0,'min'=>0);
		}
		catch(adjParadoxException $p)
		{
			// We might end up path-less
			$min = 0;
		}
		
		if( isset($this->defender) )
		{
			// We're moving into a defender

			try
			{
				if ( $this instanceof adjHeadToHeadMove or !$this->defenderMoving() )
				{
					// The defender is either attacking us, or not moving
					
					if ( $this->countryID == $this->defender->countryID )
						return array('min'=>0,'max'=>0); // It's one of our own; we can't attack it
	
					/*
					 * Because we are attacking a holding/headtohead unit make sure that we only recieve support
					 * from countries other than the one we are attacking
					 */
					$checkCountryID = true; 
				}
			}
			catch(adjParadoxException $pe)
			{
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
				
				/*
				 * Because a paradox has occurred we may end up having to check nationalities,
				 * but we may not. So we need to get the min as if we were checking, and the 
				 * max as if we weren't.
				 */
				
				// The min number of supports from checking the nationalities
				$attackStrength = $this->supportStrength(true);
				
				// The might be set to 0, if the path is still unsure
				if ( isset($min) )
					$attackStrength['min'] = $min;
				
				if ( isset($attackStrength['paradox']) )
					$attackStrength['paradox']->downSizeTo($p);
				else
					$attackStrength['paradox'] = $p;
				
				
				// The max number from not checking the nationalities
				$maxSupporters = $this->supportStrength(false);
				
				$attackStrength['max'] = $maxSupporters['max'];
				
				if ( isset($maxSupporters['paradox']) )
					$attackStrength['paradox']->downSizeTo($maxSupporters['paradox']);
				
				return $attackStrength;
			}
		}
		
		if ( ! isset($checkCountryID) )
			$checkCountryID = false;
		
		$attackStrength = $this->supportStrength($checkCountryID);
		
		// The might be set to 0, if the path is still unsure
		if ( isset($min) )
			$attackStrength['min'] = $min;
			
		if ( isset($attackStrength['paradox']) and isset($p) )
			$attackStrength['paradox']->downSizeTo($p);
		elseif( isset($p) )
			$attackStrength['paradox'] = $p;
			
		return $attackStrength;
	}
	
	/**
	 * This is not an official value, but a helper for the other numeric functions
	 * which perform similar tasks; counting all supporting units
	 *
	 * @param bool $checkCountryID Do we need to check the supporting countryID?
	 * @return int|array A max/min/paradox array, or an int
	 */
	protected function supportStrength($checkCountryID=false)
	{
		$min = 1;
		$max = 1;
		
		foreach($this->supporters as $supporter)
		{
			/*
			 * If specified then countries are checked to ensure no-one can
			 * give attack support against their own countryID
			 */
			if ( $checkCountryID and $this->defender->countryID == $supporter->countryID )
				continue;
			
			try
			{
				if( $supporter->success() )
				{
					$min++;
					$max++;
				}
			}
			catch(adjParadoxException $pe)
			{
				$max++; // It is a possible supporter
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		$support = array('min'=>$min,'max'=>$max);
		if ( isset($p) )
			$support['paradox'] = $p;
		
		return $support;
	}
}

/**
 * A unit engaging in a head to head move; only difference is its defense strength
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjHeadToHeadMove extends adjMove
{
	protected function _defendStrength()
	{
		return $this->supportStrength();
	}
}

?>