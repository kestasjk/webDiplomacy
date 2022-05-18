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
 * An object which can represent or send a notice, either from a game or user.
 * Used only on the home page.
 *
 * @package Base
 */
class notice
{
	private $hash;
	private $toUserID;
	private $fromID;
	private $type;
	private $keep;
	private $private;
	private $text;
	private $linkName;
	private $linkID;
	private $linkURL;
	private $timeSent;

	private static $recent;
	private static $new;
	
	public function viewedSplitter()
	{
		global $User;

		self::$recent=($this->timeSent >= $_SESSION['lastSeenHome'] );
		self::$new=($this->timeSent >= $User->timeLastSessionEnded );
	}

	public function __construct(array $hash)
	{
		foreach($hash as $n=>$v)
			$this->{$n}=$v;

		if( isset($this->linkID) && $this->linkID )
		{
			if( $this->type=='Game' )
				$this->linkURL = 'board.php?gameID='.$this->linkID;
			elseif( $this->type=='PM' || $this->type=='User' )
				$this->linkURL = 'profile.php?userID='.$this->linkID.'#message';
			elseif( $this->type=='Group' )
				$this->linkURL = 'group.php?groupId='.$this->linkID.'#message';
			else
				$this->linkURL = '';
		}
		else
			$this->linkURL = '';
	}

	public static $noticesPage=false;

	private function isRespondable()
	{
		global $User;

		if( !self::$noticesPage ) return false;

		if ( $this->type != 'PM' ) return false;

		if ( substr($this->linkName,0,3) == l_t("To:") ) return false;

		return true;
	}

	public static function sendPMs()
	{
		global $User;

		if ( isset($_REQUEST['message']) && $_REQUEST['message'] && isset($_REQUEST['toUserID']) && $_REQUEST['toUserID'] )
		{
			$UserPMTo = new User((int)$_REQUEST['toUserID']);

			if( $UserPMTo->isUserMuted($User->id) )
			{
				return l_t("%s has muted you; could not sent message.",$UserPMTo->username);
			}
			else
			{
              if ( $UserPMTo->sendPM($User, $_REQUEST['message']) )
              {
                  return l_t("Message sent to %s successfully.",$UserPMTo->username);
              } 
              else 
              {
                  return l_t("Private message could not be sent. You may be silenced or muted.");
              }
			}
		}
		return false;
	}

	private function replyBox()
	{
		if( !isset(Config::$customForumURL) ) 
		{
			return '<a name="messagebox"></a>
			<form action="index.php?toUserID='.$this->fromID.'&notices=on" method="post">
				<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />
				<textarea name="message" style="width:90%" rows="3"></textarea></li>
				<input type="submit" class="form-submit" value="'.l_t('Reply').'" /></li>
			</form>
			</div>';
		} 
		else 
		{
			return '<a name="messagebox"></a>
			</div>';
		}
	}

	public function html()
	{
		global $User;

		// If the alert is to inform the user they have missed a deadline then highlight it in red to grab their attention. Doing this inline
		// instead of changing css class to avoid impacting the js that highlights game alerts on hover over. 
		if (strpos($this->text, 'You have missed a deadline') !== false)
		{
			$buf = '<div style="background-color: #F08080;" class="homeNotice '.($this->type=='Game'?'" gameID="'.$this->fromID.'"':'userID'.$this->fromID.'"').'>
			<div class="homeForumGroup homeForumAlt'.libHTML::alternate().'">
				<div class="homeForumSubject homeForumTopBorder">'.$this->fromLink().'</div>
				<div class="homeForumPostAlt'.libHTML::alternate().' homeForumPost">
					<div class="homeForumPostTime">'.libTime::text($this->timeSent).' ';
		}

		elseif (strpos($this->text, 'Your application to join') !== false || strpos($this->text, 'Congratulations! You have been accepted into') !== false || strpos($this->text, 'You have been removed from') !== false)
		{
			$buf = '<div style="background-color: #F08080;" class="homeNotice '.($this->type=='Game'?'" gameID="'.$this->fromID.'"':'userID'.$this->fromID.'"').'>
			<div class="homeForumGroup homeForumAlt'.libHTML::alternate().'">
				<div class="homeForumSubject homeForumTopBorder">'.$this->fromLink().'</div>
				<div class="homeForumPostAlt'.libHTML::alternate().' homeForumPost">
					<div class="homeForumPostTime">'.libTime::text($this->timeSent).' ';
		}

		else
		{
			$buf = '<div class="homeNotice '.($this->type=='Game'?'" gameID="'.$this->fromID.'"':'userID'.$this->fromID.'"').'>
			<div class="homeForumGroup homeForumAlt'.libHTML::alternate().'">
				<div class="homeForumSubject homeForumTopBorder">'.$this->fromLink().'</div>
				<div class="homeForumPostAlt'.libHTML::alternate().' homeForumPost">
					<div class="homeForumPostTime">'.libTime::text($this->timeSent).' ';
		}

		if ( self::$recent )
			$buf .= libHTML::unreadMessages();
		elseif ( self::$new )
			$buf .= libHTML::maybeReadMessages();

		$buf .= '</div>
					<div class="homeForumMessage">'.$this->text.'</div>
					<div style="clear:both"></div>
					</div>';

		if( $this->isRespondable() )
			$buf .= '<div class="homeForumPostAlt'.libHTML::alternate().' homeForumPost">'.$this->replyBox().'</div>';

		$buf .= '	</div>';
		$buf .= '</div>';

		return $buf;
	}

	public function fromLink()
	{
		$linkName=$this->linkName;
		if(strlen($linkName)>35) $linkName = substr($linkName,0,35).'...';

		if( $this->linkURL )
			$buf = '<a href="'.$this->linkURL.'" '.( $this->type=='Game' ? 'gameID="'.$this->linkID.'"' : '' ).'>'.$linkName.'</a>';
		else
			$buf = $linkName;

		return $buf;
	}

	public function timeSent()
	{
		return libTime::text($this->timeSent);
	}

	public function message()
	{
		return $this->text;
	}

	static public function getType($type=false, $limit=35, $linkID=0)
	{
		global $DB, $User;

		$notices=array();

		$tabl=$DB->sql_tabl("SELECT n.*
			FROM wD_Notices n
			LEFT JOIN wD_Games g on g.name = n.linkName and n.type = 'Game'
			LEFT JOIN wD_Members m on m.gameID = g.id and n.type = 'Game' and m.userID = ".$User->id."
			WHERE (m.hideNotifications is null or m.hideNotifications = 0) and n.toUserID=".$User->id.($type ? " AND n.type='".$type."'" : '').($linkID>0?" AND n.fromID = ".$linkID." ":"")."
			ORDER BY n.timeSent DESC ".($limit?'LIMIT '.$limit:''));
		while($hash=$DB->tabl_hash($tabl))
		{
			$notices[] = new notice($hash);
		}

		return $notices;
	}
	public static function send($toUserID, $fromID, $type, $keep, $private, $text, $linkName, $linkID='NULL')
	{
		global $DB;
		$linkName=$DB->escape($linkName,true);
		$text=$DB->msg_escape($text,true);
		$DB->sql_put("INSERT INTO wD_Notices
			(toUserID, fromID, type, keep, private, `text`, linkName, linkID,timeSent)
			VALUES (
			".$toUserID.", ".$fromID.", '".$type."', '".$keep."',
				'".$private."', '".$text."', '".$linkName."', ".$linkID.",".time().")");
	}
}

?>
