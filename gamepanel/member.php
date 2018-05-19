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
 * This class displays the member subsection of a game panel.
 *
 * @package GamePanel
 */
class panelMember extends Member
{
	/**
	 * The icon showing whether we've received messages from this user, if we're a member
	 * @return string
	 */
	function memberSentMessages()
	{
		global $User;
		if($this->Game->Members->isJoined())
			if(in_array($this->countryID,$this->Game->Members->ByUserID[$User->id]->newMessagesFrom))
				return libHTML::unreadMessages('board.php?gameID='.$this->gameID.'&msgCountryID='.$this->countryID.'#chatbox');
	}

	/**
	 * The member bar shown at the top of a board page for members of the game.
	 * @return string
	 */
	function memberHeaderBar()
	{
		$buf = '';
		libHTML::alternate();
		
		global $checkMissingOrders;
		
		if ( $this->Game->phase != 'Pre-game' && $this->Game->phase != 'Finished')
		{
			global $DB;
			
			$row = $DB->sql_hash("select count(1) from wD_Members where gameID = ".$this->gameID." and (orderStatus not like '%Saved%' and status not like '%Defeated%' and orderStatus not like '%Completed%' and orderStatus not like '%Ready%')");
			foreach ( $row as $name=>$value )
			{
				$checkMissingOrders = $value;
			}
			
			if ($checkMissingOrders >= 1)
			{
				$buf .= '<div class="panelAnonOnlyFlag"><b>At least 1 country still needs to enter orders!</b></div>';
			}
			else
			{
				$buf .= '<div class="panelAnonOnlyFlag"><b>All countries have entered orders.</b></div>';
			}
		}
		
		if ( $this->Game->phase != 'Pre-game' )
		{
			$buf .= '
			<div class="panelBarGraphMember memberProgressBar barAlt'.libHTML::$alternate.'">'.$this->memberProgressBar().'</div>';
		}
		else
		{
			$buf .= '<div class="panelBarGraphMember memberProgressBarBlank"> </div>';
		}
		
		$buf .= '<div class="memberBoardHeader barAlt'.libHTML::$alternate.' barDivBorderTop ">
			<table><tr class="member">';

		$buf .= '
			<td class="memberLeftSide">
				<span class="memberCountryName">'.$this->memberCountryName().'</span>';

		$buf .= '
			</td>
			<td class="memberRightSide '.
				($this->status=='Left'||$this->status=='Resigned'||$this->status=='Defeated'?'memberStatusFade':'').
				'">
				<div>
				<div class="memberUserDetail">
					'.$this->memberFinalizedFull().'<br />
					'.$this->memberMessagesFull().'
				</div>
				<div class="memberGameDetail">
					'.$this->memberGameDetail().'
				</div>
				<div style="clear:both"></div>
				</div>
			</td>
			</tr>
			</table></div>';

		return $buf;
	}

	/**
	 * The finalized icon plus explanation
	 * @return string
	 */
	function memberFinalizedFull()
	{
		return $this->memberFinalized().' - <span class="member'.$this->id.'StatusText">'.$this->orderStatus->iconText().'</span>';
	}

	/**
	 * The messages icon, if recieved, plus explanation
	 * @return string
	 */
	function memberMessagesFull()
	{
		if ( count($this->newMessagesFrom) )
		{
			if ( count($this->newMessagesFrom) == 1 && in_array('0',$this->newMessagesFrom) )
				return libHTML::maybeReadMessages('board.php?gameID='.$this->gameID.'#chatbox').' - '.l_t('Unread global messages');
			else
				return libHTML::unreadMessages('board.php?gameID='.$this->gameID.'#chatbox').' - '.l_t('Unread messages');
		}
		else
			return l_t('No unread messages');
	}

	
	
	/**
	 * The messages icon
	 * @return string
	 */
	function memberMessages()
	{
		if ( count($this->newMessagesFrom) )
		{
			if ( count($this->newMessagesFrom) == 1 && in_array('0',$this->newMessagesFrom) )
				return libHTML::maybeReadMessages('board.php?gameID='.$this->gameID.'#chatbox');
			else
				return libHTML::unreadMessages('board.php?gameID='.$this->gameID.'#chatbox');
		}
		else
			return l_t('No unread messages');
	}

	/**
	 * The members country name, colored
	 * @return string
	 */
	function memberCountryName()
	{
		global $User;

		if( $this->countryID != 0 )
			return '<span class="country'.$this->countryID.' '.($User->id==$this->userID?'memberYourCountry':'').' memberStatus'.$this->status.'">'.
				l_t($this->country).'</span>';
		else
			return '';
	}

	private $isNameHidden;
	function isNameHidden()
	{
		global $User;

		if ( !isset($this->isNameHidden) )
		{
			if ( $this->Game->isMemberInfoHidden() && $User->id!=$this->userID )
				$this->isNameHidden = true;
			else
				$this->isNameHidden = false;
		}

		return $this->isNameHidden;
	}
	
	private $isLastSeenHidden;
	function isLastSeenHidden()
	{
		global $User;
		$this->isLastSeenHidden = true;
		if (($User->type['Moderator']) && (! $this->Game->Members->isJoined())) 
		{
			$this->isLastSeenHidden = false;
		}

		return $this->isLastSeenHidden;
	}
	/**
	 * The name of the user playing as this member, his points, and whether he's logged on
	 * @return string
	 */
	function memberName()
	{
		if ($this->isNameHidden())
			return '('.l_t('Anonymous').')';
		else
			return '<a href="profile.php?userID='.$this->userID.'">'.$this->username.'</a>
				'.'
				<span class="points">('.$this->points.libHTML::points().User::typeIcon($this->userType).')</span>'
				.(defined('AdminUserSwitch') ? ' (<a href="board.php?gameID='.$this->gameID.'&auid='.$this->userID.'" class="light">+</a>)':'');
	}

	/**
	 * The username colored as per the countryID
	 * @return string
	 */
	function memberNameCountry()
	{
		global $User;
		$buf = '';
		if( $this->countryID != 'Unassigned' )
			$buf .= '<span class="memberStatus'.$this->status.'">';

		if ( $this->isNameHidden() )
			$buf .= '<span class="country'.$this->countryID.'">'.l_t($this->country).'</span>';
		else
			$buf .= '<a class="country'.$this->countryID.'" href="profile.php?userID='.$this->userID.'">'.$this->username.'</a>';

		$buf .= '</span>';

		return $buf;
	}

	/**
	 * Units and SCs count, colored green if growing and red if shrinking
	 * @return string
	 */
	function memberUnitSCCount()
	{
		if ( $this->unitNo < $this->supplyCenterNo )
			$unitStyle = "good";
		elseif ( $this->unitNo > $this->supplyCenterNo )
			$unitStyle = "bad";
		else
			$unitStyle = "neutral";

		return '<span class="memberSCCount">'.l_t('%s supply-centers, %s units',
			'<em>'.$this->supplyCenterNo.'</em>',
			'<em class="'.$unitStyle.'">'.$this->unitNo.'</em>').'</span>';
	}

	/**
	 * The amount of points bet, info on current value/amount won. Colored depending on success/failure.
	 * @return string
	 */
	function memberBetWon()
	{
		$buf = l_t('Bet:').' <em>'.$this->bet.libHTML::points().'</em>, ';

		if ( $this->Game->phase == 'Pre-game' )
			return l_t('Bet:').' <em>'.$this->bet.libHTML::points().'</em>';

		if( $this->status == 'Playing' || $this->status == 'Left' )
		{
			$buf .= l_t('worth:').' <em';
			$value = $this->Game->Scoring->pointsForDraw($this);
			if ( $value > $this->bet )
				$buf .= ' class="good"';
			elseif ( $value < $this->bet )
				$buf .= ' class="bad"';

			$buf .= '>'.$value.libHTML::points().'</em>';
			return $buf;
		}
		elseif ( $this->status == 'Won' ||
			($this->Game->potType == 'Points-per-supply-center' &&  $this->status == 'Survived') || $this->status == 'Drawn' )
		{
			$buf .= l_t('won:').' <em';
			$value = $this->pointsWon;
			if ( $value > $this->bet )
				$buf .= ' class="good"';
			elseif ( $value < $this->bet )
				$buf .= ' class="bad"';

			$buf .= '>'.$value.libHTML::points().'</em>';
			return $buf;
		}
		else
		{
			return l_t('Bet:').' <em class="bad">'.$this->bet.libHTML::points().'</em>';
		}
	}

	/**
	 * An obsolete stub function
	 * @param boolean $ingame If true the output is tweaked for in-game display
	 * @return string
	 */
	function summary($ingame=false)
	{
		return $this->memberName();
	}

	/**
	 * The progress bar for this member, showing current SCs and units, and distance to SCTarget SCs
	 * @return string
	 */
	function memberProgressBar()
	{
		// $Remaining
		// $SCEqual, ($Remaining)
		// $SCEqual, $UnitDeficit, ($Remaining)
		// $SCEqual, $UnitSurplus, ($Remaining)

		libHTML::$first=true;

		if ( ($this->supplyCenterNo + $this->unitNo ) == 0 )
		{
			return '<table class="memberProgressBarTable"><tr>
				<td class="memberProgressBarRemaining '.libHTML::first().'" style="width:100%"></td>
				</tr></table>';
		}

		$dividers = array();

		if( $this->unitNo < $this->supplyCenterNo )
		{
			$dividers[$this->unitNo] = 'SCs';
			$dividers[$this->supplyCenterNo] = 'UnitDeficit';
		}
		else
		{
			$dividers[$this->supplyCenterNo] = 'SCs';

			if( $this->unitNo > $this->supplyCenterNo )
				$dividers[$this->unitNo] = 'UnitSurplus';
		}

		$SCTarget = $this->Game->Variant->supplyCenterTarget;

		$buf = '';
		$lastNumber = 0;
		foreach($dividers as $number=>$type)
		{
			if( ($number - $lastNumber) == 0 ) continue;
			if( $lastNumber == $SCTarget ) break;
			if( $number > $SCTarget ) $number = $SCTarget;

			$width = round(($number - $lastNumber)/$SCTarget * 100);

			$buf .= '<td class="memberProgressBar'.$type.' '.libHTML::first().'" style="width:'.$width.'%"></td>';

			$lastNumber = $number;
		}

		if ( $number < $SCTarget)
		{
			$width = round(($SCTarget - $number)/$SCTarget * 100);
			$buf .= '<td class="memberProgressBarRemaining '.libHTML::first().'" style="width:'.$width.'%"></td>';
		}

		return '<table class="memberProgressBarTable"><tr>'.$buf.'</tr></table>';
	}

	/**
	 * The country name, colored
	 * @return unknown_type
	 */
	function countryColored()
	{
		return $this->memberCountryName();
	}

	/**
	 * Details about this members in-game stats
	 * @return string
	 */
	function memberGameDetail()
	{
		$buf = '';
		if ( $this->status != 'Playing')
			$buf .= '<span class="memberStatus"><em>'.l_t($this->status).'</em>. </span>';

		if ( $this->Game instanceof panelGameBoard || $this->status == 'Defeated' )
			$buf .= '<span class="memberPointsCount">'.$this->memberBetWon().'</span><br />';

		if ($this->status != 'Defeated')
			$buf .= '<span class="memberUnitCount">'.$this->memberUnitSCCount().'</span>';

		return $buf;
	}

	/**
	 * This member's votes cast
	 * @return string
	 */
	function memberVotes()
	{
        	global $User;

		$buf=array();
		foreach($this->votes as $voteName)
		{
			if ( $voteName == 'Pause' && $this->Game->processStatus=='Paused' )
				$voteName = 'Unpause';
			// Do we hide draws?
			if ( $voteName == 'Draw' && $this->Game->drawType == 'draw-votes-hidden' 
				&& $User->id != $this->userID ) 
			{
				// Moderators can see draws in games they're not in
				if (($User->type['Moderator']) && (! $this->Game->Members->isJoined())) 
				{
					$buf[]=l_t("(Hidden Draw)");
				}
				continue;
			}
			$buf[]=l_t($voteName);
		}

		// Display hidden draw votes message if appropriate
		if ( $this->Game->drawType == 'draw-votes-hidden'
			&& $User->id != $this->userID 
			&& !(($User->type['Moderator']) && (! $this->Game->Members->isJoined()))) 
			$buf[]=l_t("(any draw votes are hidden)");

		if( count($buf) )
			return l_t('Votes:').' <span class="memberVotes">'.implode(', ',$buf).'</span>';
		else
			return false;
	}

	/**
	 * Details about this members user info
	 * @return string
	 */
	function memberUserDetail()
	{
		$buf = '<span class="memberName">'.$this->memberName().'</span> ';

		if( $this->Game instanceof panelGameBoard
			&& $this->status == 'Playing' && $this->Game->phase != 'Finished' )
		{
			if ( !$this->isLastSeenHidden() )
				$buf .= '<br /><span class="memberLastSeen">
						'.l_t('Last seen:').' <strong>'.$this->lastLoggedInTxt().'</strong>';

			$voteList = $this->memberVotes();
			if($voteList)
				$buf .= '<br />'.$voteList;

			if ( $this->missedPhases == 2 )
				$buf .= '<br /><span class="missedPhases">'.l_t('Missed the last phase').'</span>';

			$buf .= '</span>';
		}


		return $buf;
	}

	/**
	 * Finalized icon
	 * @return string
	 */
	function memberFinalized()
	{
		if( $this->status!='Playing' ) return '';

		return '<span class="member'.$this->id.'StatusIcon">'.$this->orderStatus->icon().'</span>';
	}

	private function muteMember() {
		global $User;

		static $alreadyMuted;
		if( isset($alreadyMuted) ) return;
		$alreadyMuted=true;

		if( $User->type['User'])
			$User->toggleCountryMute($this->gameID, $this->countryID);
	}

	private function muteIcon() {
		global $User;

		$buf = '';
		if( $User->type['User'] && $this->userID!=$User->id)
		{
			$isMuted = $User->isCountryMuted($this->gameID, $this->countryID);

			if( isset($_REQUEST['toggleMute']) && $_REQUEST['toggleMute']==$this->countryID) {
				$this->muteMember();
				$isMuted = !$isMuted;
			}

			$toggleMuteURL = 'board.php?gameID='.$this->gameID.'&toggleMute='.$this->countryID.'&rand='.rand(1,99999).'#chatboxanchor';
			$buf .= '<br />'.($isMuted ? libHTML::muted($toggleMuteURL) : libHTML::unmuted($toggleMuteURL));
		}
		return $buf;
	}

	/**
	 * The bar as displayed in the in-summary list of members
	 * @return string
	 */
	function memberBar()
	{
		global $User;
		if ((($User->type['Moderator']) && (! $this->Game->Members->isJoined())) || $this->Game->anon == 'No') 
		{
			$buf = '<td class="memberLeftSide">
			<span class="memberCountryName">'.$this->memberSentMessages().' '.$this->memberFinalized().$this->memberCountryName().'</span>';
		}
		else
		{
			$buf = '<td class="memberLeftSide">
			<span class="memberCountryName">'.$this->memberSentMessages().$this->memberCountryName().' '.'</span>';

		}

		$buf .= $this->muteIcon();

		$buf .= '
			</td>
			<td class="memberRightSide '.
				($this->status=='Left'||$this->status=='Resigned'||$this->status=='Defeated'?'memberStatusFade':'').
				'">
				<div>
				<div class="memberUserDetail">
					'.$this->memberUserDetail().'
				</div>
				<div class="memberGameDetail">
					'.$this->memberGameDetail().'
				</div>
				<div style="clear:both"></div>
				</div>';

		if ( $this->Game->phase != 'Pre-game' )
			$buf .= '<div class="panelBarGraphCountry memberProgressBar">'.$this->memberProgressBar().'</div>';

		$buf .= '</td>';

		return $buf;
	}

	/**
	 * Obsolete: The member's name and countryID info
	 * @return string
	 */
	function name()
	{
		$output = $this->profile_link();

		if ( $this->Game->phase != 'Pre-game' )
		{
			$output .= l_t(' as %s',$this->countryID);

			switch($this->status)
			{
				case 'Resigned':
				case 'Left':
					$output .= '<strong>'.l_t(', in civil disorder').'</strong>';
					break;
				case 'Playing':
					$output .= $this->memberFinalized();
			}

			if($this->status != 'Defeated')
				$output .= ': '.l_t('%s supply centers','<strong>'.$this->supplyCenterNo.'</strong>');
		}

		return $output;
	}
}
?>
