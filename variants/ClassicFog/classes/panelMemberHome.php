<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Claccic-Fog-of-War variant for webDiplomacy

	The Claccic-Fog-of-War variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General Public
	License as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Claccic-Fog-of-War variant for webDiplomacy is distributed in the hope that 
	it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFogVariant_panelMemberHome extends panelMemberHome
{

	// Hide the finalized Check, so nobody can guess what players need to enter orders in a given phase
	function memberFinalized()
	{
		global $User;
		if ($this->status!='Playing' ) return '';
		if (($this->userID == $User->id) || ($User->type['Admin'])) return parent::memberFinalized();
		return '<span class="member'.$this->id.'StatusIcon"><img src="variants/ClassicFog/resources/question.png" alt="?" title="Unknown orderstatus" /></span>';
	}
	
}

