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

require_once(l_r('lib/gamemessage.php'));

/**
 * The chat-box for the board. From the tabs to the messages to the send-box, also
 * takes responsibility for sending any messages it recieves.
 *
 * @package Board
 */
class Chatbox
{
	/**
	 * Find the tab which the user has requested to see, and return the country name. (Can also be 'Global')
	 *
	 * Will set the countryID as a session var, so will remember the countryID selected once even if not specified afterwards.
	 *
	 * @return string
	 */
	public function findTab()
	{
		global $Member, $Game, $User;

		$msgCountryID = 0;

		// Find which member's messages we're looking at
		if( isset($_REQUEST['msgCountryID']) )
		{
			$msgCountryID = (int)$_REQUEST['msgCountryID'];
		}
		elseif( isset($_SESSION[$Game->id.'_msgCountryID']) )
		{
			/*
			 * This should only be used when entering a board, while within the board the msgCountryID
			 * should be passed with REQUEST, or else problems arise with multiple tabs
			 */
			$msgCountryID = $_SESSION[$Game->id.'_msgCountryID'];
		}
		$msgCountryID=(int)$msgCountryID;

		if ( $msgCountryID<=0 || $msgCountryID>count($Game->Variant->countries) )
			$msgCountryID = 0;

		// Enforce Global and Notes tabs when its not Regular press game.
		if ( $Game->pressType != 'Regular' && !(isset($Member) && $Member->countryID == $msgCountryID) )
			$msgCountryID = 0;

		$_SESSION[$Game->id.'_msgCountryID'] = $msgCountryID;

		if ( isset($Member) and in_array($msgCountryID, $Member->newMessagesFrom) )
		{
			/*
			 * The countryID we are viewing has new messages, which we are about to view.
			 * Register the new messages as seen
			 */
			$Member->seen($msgCountryID);
		}
		return $msgCountryID;
	}

	/**
	 * Post a message to the given countryID, if there is one to be posted. Will also send messages as a
	 * GameMaster if the user is a moderator which isn't joined into the game.
	 *
	 * @param $msgCountryID The countryID to post to, may include 0 (Global)
	 */
	public function postMessage($msgCountryID)
	{
		global $Member, $Game, $User, $DB;

		if( isset($_REQUEST['newmessage']) AND $_REQUEST['newmessage']!="" )
		{
			$newmessage = trim($_REQUEST['newmessage']);

			if ( isset($Member) &&
			     ( $Game->pressType == 'Regular' ||                                        // All tabs allowed for Regular
			       $Member->countryID == $msgCountryID ||                                  // Notes tab always allowed
			       ( $msgCountryID == 0 &&                                                 // Global tab allowed for...
			         ( $Game->pressType == 'PublicPressOnly' ||                            // public press and
			           ( $Game->pressType == 'NoPress' && $Game->phase == 'Finished' ))))) // finished nopress.
			{
				$sendingToMuted = false;

				if( $msgCountryID != 0 ) {
					$SendToUser = new User($Game->Members->ByCountryID[$msgCountryID]->userID);
					if( $SendToUser->isCountryMuted($Game->id, $Member->countryID) )
						$sendingToMuted = true;
				}

				if( $sendingToMuted )
					libGameMessage::send($Member->countryID, $msgCountryID, l_t("Cannot send message; this country has muted you."));
				else
					libGameMessage::send($msgCountryID, $Member->countryID, $newmessage);
			}
			elseif( $User->type['Moderator'] || defined('AdminUserSwitch'))
			{
				$fromName = (($User->type['ForumModerator'] || $User->type['Admin']) ? $User->username.' (Moderator)' : 'Mod-Team');
				libGameMessage::send(0, 0, '<strong>'.$fromName.': </strong>'.$newmessage);
			}
		}
	}

	/**
	 * Output the chatbox HTML; output the tabs, then the information about the player we're talking to,
	 * then the correspondance we have with the current msgCountryID at the moment, then the post-box for
	 * new messages we want to send
	 *
	 * @param string $msgCountryID The id of the country/tab which we have open
	 * @return string The HTML for the chat-box
	 */
	public function output ($msgCountryID)
	{
		global $DB, $Game, $User, $Member;

		$chatbox = '<a name="chatboxanchor"></a><a name="chatbox"></a>';

		// Print each user's tab
		if( isset($Member) )
			$chatbox .= $this->outputTabs($msgCountryID);

		// Create the chatbox

		// Print info on the user we're messaging
		// Are we viewing another user, or the global chatbox?

		$chatbox .= '<DIV class="chatbox '.(!isset($Member)?'chatboxnotabs':'').'">
					<TABLE class="chatbox">
					<TR class="barAlt2 membersList">
					<TD>';

		if ( $Game->phase != 'Pre-game' )
		{
			if ( $msgCountryID == 0 )
			{
				$memList=array();
				for($countryID=1; $countryID<=count($Game->Variant->countries); $countryID++)
					$memList[]=$Game->Members->ByCountryID[$countryID]->memberNameCountry();
				$chatbox .= '<div class="chatboxMembersList">'.implode(', ',$memList).'</div>';
			}
			else
			{
				$chatbox .= $Game->Members->ByCountryID[$msgCountryID]->memberBar();
			}
		}
		else if (!isset($Member) || $Member->countryID != $msgCountryID)
		{
			$chatbox .= $Game->Members->ByCountryID[$msgCountryID]->memberBar();
		}

		$chatbox .= '</TD></TR></TABLE></DIV>';

		// Print the messages in the chatbox
		$chatbox .= '<DIV id="chatboxscroll" class="chatbox"><TABLE class="chatbox">';

		$messages = $this->getMessages($msgCountryID);

		if ( $messages == "" )
		{
			$chatbox .= '<TR class="barAlt1"><td class="notice">
					'.l_t('No messages yet posted.').
					'</td></TR>';
		}
		else
		{
			$chatbox .= $messages;
		}

		$chatbox .= '</TABLE></DIV>';

		if ( ( $User->type['Moderator'] && $msgCountryID == 0 ) ||
		     ( isset($Member) &&
		       ( $Game->pressType == 'Regular' ||                                         // All tabs allowed for Regular
		         $Member->countryID == $msgCountryID ||                                   // Notes tab always allowed
		         ( $msgCountryID == 0 &&                                                  // Global tab allowed for...
		           ( $Game->pressType == 'PublicPressOnly' ||                             // public press and
		             ( $Game->pressType == 'NoPress' && $Game->phase == 'Finished' )))))) // finished nopress.
		{
			$chatbox .= '<DIV class="chatbox"><TABLE>
					<TR class="barAlt2">
					<form method="post" class="safeForm" action="board.php?gameID='.$Game->id.'&amp;msgCountryID='.$msgCountryID.'">
						<TD class="left send">
							<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />
							<input type="submit" tabindex="2" class="form-submit" value="'.l_t('Send').'" name="Send" />
						</TD>
						<TD class="right">
							<TEXTAREA id="sendbox" tabindex="1" NAME="newmessage" style="width:98% !important" width="100%" ROWS="3"></TEXTAREA>
						</TD>
					</form>
					</TR>
				</TABLE></DIV>';
		}

		libHTML::$footerScript[] = '
			var cbs = $("chatboxscroll");

			cbs.scrollTop = cbs.scrollHeight;
			';

		// Don't focus the chatbox if the user is entering orders
		if ( isset($_REQUEST['msgCountryID']) )
			libHTML::$footerScript[] = '
				var sb = $("sendbox");
				if( sb != null && !Object.isUndefined(sb) ) {
					$("sendbox").focus();
				}
			';

		return $chatbox;
	}

	/**
	 * Output the tabs which go on top of the chat-box, along with online notifications and message notifications
	 * where applicable
	 * @param string $msgCountryID The name of the countryID/tab which we have open
	 * @return string The HTML for the chat-box tabs
	 */
	protected function outputTabs ( $msgCountryID )
	{
		global $Member, $Game;

		$tabs = '<div id="chatboxtabs" class="gamelistings-tabs">';
		
		if ( $Game->phase == 'Pre-game' )
			return $tabs.'</div>';
			
		for( $countryID=0; $countryID<=count($Game->Variant->countries); $countryID++)
		{
			// Do not allow country specific tabs for restricted press games.
			if ($Game->pressType != 'Regular' && $countryID != 0 && $countryID != $Member->countryID ) continue;

			$tabs .= ' <a href="./board.php?gameID='.$Game->id.'&amp;msgCountryID='.$countryID.'&amp;rand='.rand(1,100000).'#chatboxanchor" '.
				'class="country'.$countryID.' '.( $msgCountryID == $countryID ? 'current"'
					: '" title="'.l_t('Open %s chatbox tab"',( $countryID == 0 ? 'the global' : $this->countryName($countryID)."'s" )) ).'>';

			if ( $countryID == $Member->countryID )
			{
				$tabs .= l_t('Notes');
			}
			elseif(isset($Game->Members->ByCountryID[$countryID]))
			{
				$tabs .= $Game->Members->ByCountryID[$countryID]->memberCountryName();
				if ( $Game->Members->ByCountryID[$countryID]->online && !$Game->Members->ByCountryID[$countryID]->isNameHidden() )
					$tabs .= ' '.libHTML::loggedOn($Game->Members->ByCountryID[$countryID]->userID);
			}
			else
			{
				$tabs .= l_t('Global');
			}

			if ( $msgCountryID != $countryID and in_array($countryID, $Member->newMessagesFrom) )
			{
				// This isn't the tab I am currently viewing, and it has sent me new messages
				$tabs .= ' '.libHTML::unreadMessages();
			}

			$tabs .= '</a>';
		}

		$tabs .= '</div>';

		return $tabs;
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
	function getMessages ( $msgCountryID, $limit=20 )
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
				order BY id ".($msgCountryID==-1?'ASC':'DESC').' '.($limit?"LIMIT ".$limit:""));

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
		for ( $i=count($messages); $i >= 1; --$i )
		{
		
			$message = $messages[$i-1];
			
			if (($Game->anon == 'Yes' && $Game->phase != 'Finished' 
					&& $message['turn']==0 
					&& $message['toCountryID']==0)
					&& (!(($User->type['Moderator']) && !($Game->Members->isJoined())))
				)
					$message['message'] = preg_replace ('/^\((.*): /','(Anonymous): ',$message['message']);
		
			$alternate = ! $alternate;

			// If member info is hidden and the message isn't from me
			if ( $Game->isMemberInfoHidden() && ( !is_object($Member) || $message['fromCountryID'] != $Member->countryID ) )
			{
				/*
				 * Take the last 2^12 bits off the timestamp (~70 mins), to fudge
				 * it so players can't use it to compare to who was online/offline
				 * at the time.
				 */
				// 1010-1010-1010-1010-1010-xxxx-xxxx-xxxx -> 1010-1010-1010-1010-1010-0000-0000-0000
				$message['timeSent'] &= 0xfffff000;
				$approxIndicator = '~';
			}
			else
			{
				$approxIndicator = '';
			}

			$messagestxt .= '<TR class="replyalternate'.($alternate ? '1' : '2' ).
				' gameID'.$Game->id.'countryID'.$message['fromCountryID'].'">'.
				// Add gameID####countryID### to allow muted countries to be hidden
					'<TD class="left time">'.$approxIndicator.libTime::text($message['timeSent']);

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

			// Display the country name in front of the text (for colorblind people)
			if ( $User->showCountryNames == 'Yes')
				$messagestxt .=  '<span style="color: grey;">';
			
			if ( $message['turn'] < $Game->turn )
			{
				$messagestxt .= '<strong>'.$Game->datetxt($message['turn']).'</strong>: ';
			}

			if ( $User->showCountryNames == 'Yes')
				$messagestxt .=  '<span style="color: black;">';

			if( is_object($Member) && $message['fromCountryID'] == $Member->countryID )
				$message['message'] = '<span class="messageFromMe">'.$message['message'].'</span>';

			// Display the country name in front of the text (for colorblind people)
			if ( $User->showCountryNames == 'Yes')
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