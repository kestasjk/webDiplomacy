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
 * A supporting unit; support hold and move have many similarities
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
abstract class adjSupport extends adjHold
{
	protected abstract function attacked();
	
	protected function _success()
	{
		try
		{
			if ( $this->attacked() )
				return false;
		}
		catch(adjParadoxException $p) { }
		
		try
		{
			if ( $this->dislodged() )
				return false;
		}
		catch(adjParadoxException $pe)
		{
			if ( isset($p) ) $p->downSizeTo($pe);
			else $p = $pe;
		}
		
		if ( isset($p) ) throw $p;
		else return true;
	}
}

/**
 * Support moving unit
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjSupportMove extends adjSupport
{
	public $supporting;
	
	public function setUnits(array $units)
	{
		$this->supporting = $units[$this->supporting];
		
		parent::setUnits($units);
	}
	
	protected function attacked()
	{
		foreach($this->attackers as $attacker)
		{
			if ( isset($this->supporting->defender) )
				if ( $attacker->id == $this->supporting->defender->id )
					continue; // The unit attacking me is the unit I'm supporting against
			
			try
			{
				if ( $attacker->compare('attackStrength','>',0) )
					return true;
			}
			catch(adjParadoxException $pe)
			{
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		if ( isset($p) ) throw $p;
		else return false;
	}
}

/**
 * Support holding unit
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjSupportHold extends adjSupport
{
	protected function attacked()
	{
		foreach($this->attackers as $attacker)
		{
			try
			{
				if ( $attacker->compare('attackStrength','>',0) )
					return true;
			}
			catch(adjParadoxException $pe)
			{
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		if ( isset($p) ) throw $p;
		else return false;
	}
}

?>