<?php
/*
	Copyright (C) 2012 Gavin Atkinson / Oliver Auth

	This file is part of the Pirates variant for webDiplomacy

	The Pirates variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Pirates variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

require_once ('variants/Pirates/classes/adjMove.php');

class PiratesVariant_adjHeadToHeadMove extends PiratesVariant_adjMove
{

	function __construct($id, $countryID)
	{		
		parent::__construct($id, $countryID);
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
				// We're in a head to head and I don't have more attack strength than the defender
				if ( ! $this->compare('attackStrength', '>', array($this->defender, 'defendStrength') ) )
					return false;
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
	
	protected function _defendStrength()
	{
		return $this->supportStrength();
	}
}
