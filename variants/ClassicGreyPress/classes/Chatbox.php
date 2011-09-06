<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the ClassicGreyPress variant for webDiplomacy

	The ClassicGreyPress variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicGreyPress variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class GreyMember 
{
	public function memberCountryName() { return 'Grey Press'; }
	public function memberNameCountry() { return 'Grey Press'; }
	public function memberBar()         { return ''; }
	public function isNameHidden()      { return false;	}
	public $userID=4;
	public $online=0;
}

class ClassicGreyPressVariant_Chatbox extends Chatbox {

	/**
	 * The UserID for the GreyPress
	 */
	public $greyID;

	/**
	 *  Add a special Member for the Chatbox-display:
	 */
	public function __construct()
	{
		global $Game;
		$Game->Variant->countries[]='Grey Press';
		$Game->Members->ByCountryID[count($Game->Variant->countries)]=new GreyMember();
		$this->greyID = count($Game->Variant->countries);
	}
	
	/**
	 * And remove it when done:
	 */
	public function __destruct()
	{
		global $Game;
		unset ($Game->Members->ByCountryID[count($Game->Variant->countries)]);
		array_pop($Game->Variant->countries);
	}

	/**
	 * If a message is sent to GreyPress forward it to it's destination...
	 */
	public function postMessage($msgCountryID)
	{

		global $Game, $Member, $User;

		if( isset($_REQUEST['newmessage']) AND $_REQUEST['newmessage']!="" )
		{

			$newmessage = trim($_REQUEST['newmessage']);
		
			if( isset($Member) &&  $Game->pressType != 'NoPress' )
			{
			
				$pos = strpos($newmessage, ":");
				$toID = false;
				
				if ($pos == 4 && $Game->pressType == 'PublicPressOnly')
				{
					$toID = 0;
					$fromID = $this->greyID;
					$newmessage = ltrim(substr($newmessage, $pos+1));					
				}
				elseif ($pos > 0 && ($msgCountryID == $this->greyID))
				{
					$country = substr($newmessage, 0, $pos);
					$newmessage = ltrim(substr($newmessage, $pos+1));
					if ($country=='Global')
						$toID = 0;
					else
					{
						$toID = array_search($country, $Game->Variant->countries);
						if ($toID !== false)
							$toID++;
					}
					$fromID = $this->greyID;
				}
				else
				{
					$toID = $msgCountryID;
					$fromID = $Member->countryID;
				}
				
				if ($toID === false)
				{
					libGameMessage::send($this->greyID, $Member->countryID, "[no valid forward] " . $newmessage);
				}
				else
				{
					libGameMessage::send($toID , $fromID, $newmessage);
					if ($fromID == $this->greyID && $Game->pressType != 'PublicPressOnly')
					{
						libGameMessage::send($this->greyID, $Member->countryID, "[to: ". $country."] " . $newmessage);
					}
				}
				
			}
			elseif ( $User->type['Moderator'] )
			{
				libGameMessage::send(0, 0, '('.$User->username.'): '.$newmessage);
			}
		}
	}

	/**
	 * Hide the real time the GreyPress did a post.
	 */
	public function renderMessages($msgCountryID, $messages)
	{

		global $Game;
		$times=array();
		for ( $i=count($messages); $i >= 1; --$i )
		{
			if ($messages[$i-1]['fromCountryID'] == $this->greyID )
			{
				$messages[$i-1]['timeSent'] &= 0xfffff000;
				$times[] = $messages[$i-1]['timeSent'];
			}
		}

		$ret=parent::renderMessages($msgCountryID, $messages);
		foreach ($times as $replace )
			$ret=str_replace('><span class="timestamp" unixtime="'.$replace,'>~<span class="timestamp" unixtime="'.$replace, $ret);

		return $ret;
	}

}

?>

	
