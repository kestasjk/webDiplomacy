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

require_once(l_r('gamepanel/gameboard.php'));
require_once(l_r('gamepanel/membershome.php'));

/**
 * This class displays the game panel within a home context. Output is trimmed down to size,
 * the viewer is always a member, and finished games aren't seen via this class.
 *
 * @package GamePanel
 */
class panelGameHome extends panelGameBoard
{
	public function __construct($gameData)
	{
		parent::__construct($gameData);
	}

	/**
	 * Load panelMembersHome instead of panelMembers
	 */
	function loadMembers()
	{
		$this->Members = $this->Variant->panelMembersHome($this);
	}

	/**
	 * A summary using the modified gamePanelHome class, and no votes(). Also returns
	 * instead of printing directly.
	 * @return string
	 */
	function summary()
	{
		return '
		<div class="gamePanelHome variant'.$this->Variant->name.'" gameID="'.$this->id.'">
			'.$this->header().'
			'.$this->members().'
			'.$this->links().'
			<div class="bar lastBar"> </div>
		</div>
		';
	}

	/**
	 * Shortened game time remaining info
	 * @return string
	 */
	function gameTimeRemaining()
	{
		if( $this->processStatus == 'Paused' )
			return l_t('Paused').' <img src="'.l_s('images/icons/pause.png').'" title="'.l_t('Paused').'" />';
		elseif( $this->processStatus == 'Crashed' )
			return l_t('Crashed');

		if (!isset($timerCount))
			static $timerCount=0;
		$timerCount++;

		$buf = $this->processTimetxt();

		return $buf;
	}

	/**
	 * Game name
	 * @return string
	 */
	function titleBarName()
	{
		$name=parent::titleBarName();
		if(strlen($name)>30) $name = substr($name,0,30).'...';
		return '<span class="homeGameTitleBar" gameID="'.$this->id.'">'.$name.'</span>';
	}

	/**
	 * Shortened titlebar info
	 * @return string
	 */
	function titleBar()
	{
		global $User;
		$buf = '
			<div class="titleBarRightSide">
				<span class="gameTimeRemaining">'.$this->gameTimeRemaining().'</span>
			</div>

			<div class="titleBarLeftSide">
				<div class="titleBarHomeGameName">'.$this->gameIcons().' <span class="gameName">'.$this->titleBarName().'</span></div>
			</div>
			<div style="clear:both"></div>

			<div class="titleBarRightSide">
				<span class="gameDate">'.$this->datetxt().'</span>,
				<span class="gamePhase">'.l_t($this->phase).'</span>
			</div>
			<div class="titleBarLeftSide">
				Pot: <span class="gamePot">'.$this->pot().'</span>';

		$alternatives=array();
		if( $this->pressType=='NoPress')
			$alternatives[]=l_t('No chat');
		elseif( $this->pressType=='RulebookPress' )
			$alternatives[]=l_t('Rulebook chat');
		elseif( $this->pressType=='PublicPressOnly' )
			$alternatives[]=l_t('Public chat');
		if( $this->anon=='Yes' )
			$alternatives[]=l_t('Anon');
		if( $this->drawType=='draw-votes-hidden')
			$alternatives[]=l_t('Hidden draw votes');

		if( $this->minimumReliabilityRating > 0) 
		{
			$alternatives[]= l_t('<span class="%s">RR%s%%</span>',
			($User->reliabilityRating < $this->minimumReliabilityRating ? 'Austria' :''), 
			($this->minimumReliabilityRating));
		}

		if ( $alternatives )
			$buf .= '
				<br /><span class="gamePot">'.implode(', ',$alternatives).'</span>
			';

		$buf .= '</div>
			<div style="clear:both"></div>';

		return $buf;
	}

	/**
	 * Shortened pot data. Finished games aren't displayed via Home
	 * @return string
	 */
	function pot()
	{
		return $this->pot.' '.libHTML::points().'<span title="'.$this->Scoring->longName().'"> '.$this->Scoring->Abbr().'</span>';
	}

	/**
	 * Links to the game and game archives
	 * @return string
	 */
	function links()
	{
		global $DB, $User;
		$userInGame = 0;
		list($userInGame) = $DB->sql_row("SELECT count(1) FROM wD_Members WHERE userID =".$User->id." and gameID =".$this->id);
		$watchString= '';	

		if ($this->watched() || $userInGame == 0)
		{
			if ($this->watched()) { $watchString = '- <a href="board.php?gameID='.$this->id.'&unwatch">'.l_t('Stop spectating').'</a>'; }
			if( $this->phase == 'Pre-game')
			{
				return '<div class="bar homeGameLinks barAlt'.libHTML::alternate().'">
					<a href="board.php?gameID='.$this->id.'">'.l_t('Open').'</a>
					'.$watchString.'
					</div>';
			}
			else
			{
				return '<div class="bar homeGameLinks barAlt'.libHTML::alternate().'">
					<a href="board.php?gameID='.$this->id.'#gamePanel">'.l_t('Open').'</a> 
					'.$watchString.'
					</div>';
			}
		}
		else
		{
			$noticesStatus = 5;
			$SubmitName = 'Toggle Notices';
			list($noticesStatus) = $DB->sql_row("SELECT hideNotifications FROM wD_Members WHERE userID =".$User->id." and gameID =".$this->id);
			if ($noticesStatus == 1) { $SubmitName = 'Enable Notices'; }
			else if ($noticesStatus == 0) { $SubmitName = 'Disable Notices'; }

			if( $this->phase == 'Pre-game')
			{
				return '<div class="bar homeGameLinks barAlt'.libHTML::alternate().'">
				<form action="#" method="post">
				'.libAuth::formTokenHTML().'
					<a href="board.php?gameID='.$this->id.'">'.l_t('Open').'</a>
					<input type="hidden" value="'.$this->id.'" name="gameToggleName" />
					<input type="submit" title="Turn on/off the notifications for this game." style="float: right;" class = "home-submit toggle-notice" name="submit" value="'.$SubmitName.'"/>
					</form>
					</div>';
			}
			else
			{
				return '<div class="bar homeGameLinks barAlt'.libHTML::alternate().'">
					
					<form action="#" method="post">
					'.libAuth::formTokenHTML().'
					<a href="board.php?gameID='.$this->id.'#gamePanel">'.l_t('Open').'</a> 
					<input type="hidden" value="'.$this->id.'" name="gameToggleName" />
					<input type="submit" title="Turn on/off the notifications for this game." style="float: right;" class = "home-submit toggle-notice" name="submit" value="'.$SubmitName.'"/>
					</form>
					
					</div>';
			}
		}	
	}
}
?>
