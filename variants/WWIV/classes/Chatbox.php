<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the WWIV variant for webDiplomacy

	The WWIV variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The WWIV variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CountryName_Chatbox extends Chatbox
{
	public function renderMessages($msgCountryID, $messages) {
		global $Member;

		for($i=0; $i<count($messages); $i++)
			if( $messages[$i]['fromCountryID']!=0)
				if (!isset($Member) || $Member->countryID != $messages[$i]['fromCountryID'])
					$messages[$i]['message'] = '[<strong>'.$this->countryName($messages[$i]['fromCountryID']).'</strong>]<span style="color: black;">:'.$messages[$i]['message'];
				else
					$messages[$i]['message'] = '[<strong>You</strong>]<span style="color: black;">:'.$messages[$i]['message'];				

		return parent::renderMessages($msgCountryID, $messages);

	}
}

class NewMessageLimit_Chatbox extends CountryName_Chatbox
{
	function getMessages ( $msgCountryID, $limit=20 )
	{
		global $Game;
		$limit = $limit * 4;
		return parent::getMessages( $msgCountryID, $limit );
	}
}

class WWIVVariant_Chatbox extends NewMessageLimit_Chatbox {}
