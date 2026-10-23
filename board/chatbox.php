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
 * The chat-box for the board. On board.php the panel itself is drawn by javascript/wd/chat.js, which reads the
 * messages from the game's messages.json and game/playercontext and sends through the API as the /game/ board
 * does; this class gives that panel its place on the page and what it needs to know (panelHTML(), panelConfig()),
 * handles "Mark unread", which has no API route, and renders messages as HTML for the messages archive.
 *
 * @package Board
 */
class Chatbox
{
	/**
	 * Mark the messages from a country unread again, which the panel's "Mark unread" link posts back for.
	 *
	 * @param int $countryID
	 */
	public function markUnread($countryID)
	{
		global $Member, $Game, $DB;

		$countryID = (int)$countryID;
		if( !isset($Member) || $countryID < 0 || $countryID > count($Game->Variant->countries) ) return;
		if( in_array($countryID, $Member->newMessagesFrom) ) return;

		$DB->sql_put("UPDATE wD_Members SET newMessagesFrom = IF( (newMessagesFrom+0) = 0,'".$countryID."', CONCAT_WS(',',newMessagesFrom,'".$countryID."') )
					WHERE gameID = ".$Game->id." AND countryID=".$Member->countryID);
		$Member->newMessagesFrom[]=$countryID;
		$DB->sql_put("COMMIT");
	}

	/**
	 * The chat panel's place on the page: an element javascript/wd/chat.js fills in, and the form "Mark unread" posts.
	 *
	 * @return string
	 */
	public function panelHTML()
	{
		global $Game, $Member;

		$html = '<a name="chatboxanchor"></a><a name="chatbox"></a>
			<div id="wdChat" class="wdchat variant'.$Game->Variant->name.'">
				<div class="wdchat-loading notice">'.l_t('Loading messages...').'</div>
			</div>';

		if( isset($Member) )
			$html .= '<form method="post" id="wdChatMarkUnread" action="board.php?gameID='.$Game->id.'#chatboxanchor" style="display:none">
				<input type="hidden" name="MarkAsUnread" value="on" />
				<input type="hidden" name="msgCountryID" value="" />
				<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />
			</form>';

		return $html;
	}

	/**
	 * What the panel needs to know about the game and the viewer, for wD.Chat (javascript/wd/chat.js): the
	 * viewer's country, who may be written to, and how the players are named to this viewer.
	 *
	 * @return array
	 */
	public function panelConfig()
	{
		global $Game, $Member, $User;

		$countries = array();
		$members = array();
		foreach($Game->Variant->countries as $index => $countryName)
		{
			$countryID = $index + 1;
			$countries[] = array('countryID' => $countryID, 'name' => l_t($countryName));
			if( isset($Game->Members->ByCountryID[$countryID]) )
				$members[] = array(
					'countryID' => $countryID,
					'name' => $Game->Members->ByCountryID[$countryID]->memberNameCountry(),
					'tabName' => $Game->Members->ByCountryID[$countryID]->memberCountryName(),
				);
		}

		$isMember = ( isset($Member) && is_a($Member, 'userMember') );

		return array(
			'countryID' => $isMember ? (int)$Member->countryID : 0,
			'isMember' => $isMember,
			// A moderator or director who isn't playing may post to everyone, as game/sendmessage allows
			'canPostGlobal' => ( !$isMember && ( $User->type['Moderator'] || $Game->isDirector($User->id) ) ),
			'pressType' => $Game->pressType,
			'phase' => $Game->phase,
			'turn' => (int)$Game->turn,
			'countries' => $countries,
			'members' => $members,
			'colourblind' => ( $User->getOptions()->value['colourblind'] != 'No' ),
			'variantName' => $Game->Variant->name,
			'markUnreadForm' => $isMember ? 'wdChatMarkUnread' : null,
			'analyticsEventName' => libHTML::analyticsGameEventName('send_message', $Game),
		);
	}

	protected function countryName($countryID) {
		global $Game;

		if( $countryID==0 )
			return 'Global';
		else
			return $Game->Variant->countries[$countryID-1];
	}

	/**
	 * Retrieve and parse the messages which have been sent via this tab into an HTML table
	 *
	 * @param string $msgCountryID The name of the countryID/tab which we have open
	 * @return string The HTML for the messages we have sent/recieved
	 */
	function getMessages ( $msgCountryID, $limit=50 )
	{
		global $DB, $User, $Member, $Game;

		if( !isset($Member) ) $msgCountryID=0;

		if ( $msgCountryID == -1 ) // 'All' ?
		{
			$where = "toCountryID = 0".(isset($Member)?" OR fromCountryID = ".$Member->countryID." OR toCountryID = ".$Member->countryID:'');
		}
		elseif ( $msgCountryID == 0 ) // Global
		{
			// Get all messages addressed to everyone
			$where = "toCountryID = 0";
		}
		else
		{
			// Only get messages sent between
			$where = "( toCountryID = ".$Member->countryID." AND fromCountryID = ".$msgCountryID." )
						/* To me, from him */
					OR
					( fromCountryID = ".$Member->countryID." AND toCountryID = ".$msgCountryID." )
						/* To him, from me */";
		}

		$tabl = $DB->sql_tabl("SELECT message, toCountryID, fromCountryID, turn, timeSent
				FROM wD_GameMessages WHERE
					gameID = ".$Game->id." AND
					(
						".$where."
					)
				order BY id DESC ".($limit?"LIMIT ".$limit:""));

		unset($where);

		// The latest message comes first, be we want to print the oldest message first
		$messages = array();
		while ( $message = $DB->tabl_hash($tabl) )
		{
			$messages[] = $message;
		}

		return $this->renderMessages($msgCountryID, $messages);
	}

	public function renderMessages($msgCountryID, $messages)
	{
		global $DB, $User, $Member, $Game;

		$messagestxt = "";

		$alternate = false;
		for ( $i = count($messages)-1; $i>=0; --$i )
		{
			$message = $messages[$i];

			$alternate = ! $alternate;

			$messagestxt .= '<TR class="replyalternate'.($alternate ? '1' : '2' ).
				' gameID'.$Game->id.'countryID'.$message['fromCountryID'].'">'.
				// Add gameID####countryID### to allow muted countries to be hidden
					'<TD class="left time">'.libTime::text($message['timeSent']);

			$messagestxt .=  '</TD>
					<TD class="right ';

			if ( $message['fromCountryID'] != 0 ) // GameMaster
			{
				// If the message isn't from the GameMaster color it in the countryID's color
				$messagestxt .= 'country'.$message['fromCountryID'];
			}

			$messagestxt .= '">';

			if ( $msgCountryID == -1 && isset($Member) ) // -1 = All
			{
				if($Member->countryID == $message['fromCountryID'])
					$fromtxt = l_t(', from <strong>you</strong>');
				elseif( 0==$message['fromCountryID'] )
					$fromtxt = l_t(', from <strong>Gamemaster</strong>');
				else
					$fromtxt = l_t(', from <strong>%s</strong>',l_t($this->countryName($message['fromCountryID'])));

				if($Member->countryID == $message['toCountryID'])
					$messagestxt .=  '('.l_t('To: <strong>You</strong>').$fromtxt.') - ';
				else
					$messagestxt .=  '('.l_t('To: <strong>%s</strong>',l_t($this->countryName($message['toCountryID']))).$fromtxt.') - ';
			}

			if ( $message['turn'] < $Game->turn )
			{
				$messagestxt .= '<strong>'.$Game->datetxt($message['turn']).'</strong>: ';
			}

			if( is_object($Member) && $message['fromCountryID'] == $Member->countryID )
				$message['message'] = '<span class="messageFromMe">'.$message['message'].'</span>';

			// Display the country name in front of the text (for colorblind people)
			if ( $User->getOptions()->value['colourblind'] != 'No')
			{
				if(isset($Member) && $Member->countryID == $message['fromCountryID'])
					$messagestxt .=  '<strong>You:</strong> ';
				elseif( $message['fromCountryID'] != 0 )
					$messagestxt .=  '<strong>'.$this->countryName($message['fromCountryID']).':</strong> ';
			}

			$messagestxt .= $message['message'].
					'</TD>
				</TR>';
		}

		return $messagestxt;
	}
}

?>
