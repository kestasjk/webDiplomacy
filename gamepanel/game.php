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

require_once(l_r('gamepanel/members.php'));

/**
 * The game panel class; it extends the Game class, which contains the information, with a set
 * of functions which display HTML giving info on the game and allowing certain interactions with it.
 *
 * This class is also extended to behave differently when viewed in a game board, or on the user's home
 * page. The plain class is used on the game-listings and profile page.
 *
 * The panelGame class has corresponding panelMembers and panelMember classes, which extend Members
 * and Member in similar ways.
 *
 * Nothing in panelGame will change the objects being displayed in any way, however they may provide
 * interfaces to do so (e.g. voting, leaving, joining), but other code like board.php will actually
 * act on any received form data; these classes are for display only.
 *
 * With a few exceptions all panel* functions return HTML strings. Also the convention is that if
 * HTML data is enclosed in a <div> it will leave its caller to create the div for it. So
 * '<div class="titleBar">'.$this->titleBar().'</div>' is seen, instead of titleBar() adding the div
 * itself.
 *
 * @package GamePanel
 */
class panelGame extends Game
{
	/**
	 * print the HTML for this game panel; header, members info, voting info, links
	 */
	function summary()
	{
		print '
		<div class="gamePanel variant'.$this->Variant->name.'">
			'.$this->header().'
			'.$this->members().'
			'.$this->votes().'
			'.$this->links().'
			<div class="bar lastBar"> </div>
		</div>
		';
	}

	public function __construct($gameData)
	{
		parent::__construct($gameData);
	}

	/**
	 * Load panelMembers, instead of Members
	 */
	function loadMembers()
	{
		$this->Members = $this->Variant->panelMembers($this);
	}

	/**
	 * The full bar with a notice about the game; used for game-over and game-starting details.
	 *
	 * @return string
	 */
	function gameNoticeBar()
	{
		if( $this->phase == 'Finished' )
			return $this->gameGameOverDetails();
		elseif( $this->phase == 'Pre-game' && count($this->Members->ByID)==count($this->Variant->countries) )
		{
			if ( $this->isLiveGame() )
				return l_t('%s players joined; game will start at the scheduled time', count($this->Variant->countries));
			else
				return l_t('%s players joined; game will start on next process cycle', count($this->Variant->countries));
		}
		elseif( $this->missingPlayerPolicy=='Wait'&&!$this->Members->isCompleted() && time()>=$this->processTime )
			return l_t("One or more players need to complete their orders before this wait-mode game can go on");
	}

	/*
	 * This is a cute way of displaying the current phase as highlighted out of the list
	 * of available ones, which become smaller, but it took up too much space
	function titleBarPhase()
	{
		return ;
		if( $this->phase == 'Pre-game' || $this->phase == 'Builds' )
			return $this->phase;

		$activePhases = array(
			'Diplomacy'=>'<span class="gamePhaseInactive">Diplomacy</span>',
			'Retreats'=>'<span class="gamePhaseInactive">Retreats</span>'
		);

		if( ($this->turn%2) != 0 )
			$activePhases['Builds']='<span class="gamePhaseInactive">Builds</span>';

		$activePhases[$this->phase] = $this->phase;

		return implode(' - ',$activePhases);
	}
	*/

	function pausedInfo() {
		return l_t('Paused').' <img src="'.l_s('images/icons/pause.png').'" title="'.l_t('Game paused').'" />';
	}

	/**
	 * The next-process data, depending on whether paused/crashed/finished/etc
	 *
	 * @return string
	 */
	function gameTimeRemaining()
	{

		if( $this->phase == 'Finished' )
			return '<span class="gameTimeRemainingNextPhase">'.l_t('Finished:').'</span> '.
				libTime::text($this->processTime);

		if( $this->processStatus == 'Paused' )
			return $this->pausedInfo();
		elseif( $this->processStatus == 'Crashed' )
			return l_t('Crashed');

		if (!isset($timerCount))
			static $timerCount=0;
		$timerCount++;

		if( $this->phase == 'Pre-game' )
			$buf = '<span class="gameTimeRemainingNextPhase">'.l_t('Start:').'</span> '.
				$this->processTimetxt().' ('.libTime::text($this->processTime).')';
		else
		{
			$buf = '<span class="gameTimeRemainingNextPhase">'.l_t('Next:').'</span> '.
				$this->processTimetxt().' ('.libTime::text($this->processTime).')';

			//if ( $this->Members->isJoined() )
				//$buf .= ' <span class="gameTimeRemainingFixed">('.libTime::text($this->processTime).')</span>';

		}

		return $buf;
	}

	/**
	 * What circumstances did the game end in? Who won, etc
	 * @return string
	 */
	function gameGameOverDetails()
	{
		if( $this->gameOver == 'Won' )
		{
			foreach($this->Members->ByStatus['Won'] as $Winner);
			return l_t('Game won by %s',$Winner->memberName());
		}
		elseif( $this->gameOver == 'Drawn' )
		{
			return l_t('Game drawn');
		}
	}


	/**
	 * Icons for the game, e.g. private padlock and featured star
	 * @return string
	 */
	function gameIcons()
	{
		global $Misc;

		$buf = '';
		if( $this->pot > $Misc->GameFeaturedThreshold )
			$buf .= '<img src="'.l_s('images/icons/star.png').'" alt="'.l_t('Featured').'" title="'.l_t('This is a featured game, one of the highest stakes games on the server!').'" /> ';

		if( $this->private )
			$buf .= '<img src="'.l_s('images/icons/lock.png').'" alt="'.l_t('Private').'" title="'.l_t('This is a private game; invite code needed!').'" /> ';

		return $buf;
	}

	/**
	 * The title bar, giving the vital game related data
	 *
	 * @return string
	 */
	function titleBar()
	{
		$rightTop = '
			<div class="titleBarRightSide">
				<span class="gameTimeRemaining">'.$this->gameTimeRemaining().'</span>
			</div>
			';

		$rightBottom = '<div class="titleBarRightSide">
				<span class="gameHoursPerPhase">
					'.$this->gameHoursPerPhase().'
				</span>
			</div>';

		$date=' - <span class="gameDate">'.$this->datetxt().'</span>, <span class="gamePhase">'.l_t($this->phase).'</span>';

		$leftTop = '<div class="titleBarLeftSide">
				'.$this->gameIcons().
				'<span class="gameName">'.$this->titleBarName().'</span>';

		$leftBottom = '<div class="titleBarLeftSide">
				'.l_t('Pot:').' <span class="gamePot">'.$this->pot.' '.libHTML::points().'</span>';

		$leftBottom .= $date;

		$leftTop .= '</div>';
		$leftBottom .= '</div>';

		$buf = '
			'.$rightTop.'
			'.$leftTop.'
			<div style="clear:both"></div>
			'.$rightBottom.'
			'.$leftBottom.'
			<div style="clear:both"></div>
			';

		$buf .= $this->gameVariants();

		return $buf;
	}

	function gameVariants() {
		$alternatives=array();
		if( $this->variantID!=1 )
			$alternatives[]=$this->Variant->link();
		if( $this->pressType=='NoPress')
			$alternatives[]=l_t('No in-game messaging');
		elseif( $this->pressType=='RulebookPress')
			$alternatives[]=l_t('Rulebook press');
		elseif( $this->pressType=='PublicPressOnly' )
			$alternatives[]=l_t('Public messaging only');
		if( $this->anon=='Yes' )
			$alternatives[]=l_t('Anonymous players');
		$alternatives[]=$this->Scoring->longName();
		if( $this->drawType=='draw-votes-hidden') 
			$alternatives[]=l_t('Hidden draw votes');
		if( $this->missingPlayerPolicy=='Wait' )
			$alternatives[]=l_t('Wait for orders');

		if ( $alternatives )
			return '<div class="titleBarLeftSide">
				<span class="gamePotType">'.implode(', ',$alternatives).'</span>
				</div>
			<div style="clear:both"></div>
			';
		else
			return '';
	}

	/**
	 * Hours per phase, whether the game is slow or fast etc
	 * @return string
	 */
	function gameHoursPerPhase()
	{
		$buf = l_t('<strong>%s</strong> /phase',libTime::timeLengthText($this->phaseMinutes*60)).
			' <span class="gameTimeHoursPerPhaseText">(';

		if ( $this->isLiveGame() )
			$buf .= l_t('live');
		elseif ( $this->phaseMinutes < 6*60 )
			$buf .= l_t('very fast');
		elseif ( $this->phaseMinutes < 16*60 )
			$buf .= l_t('fast');
		elseif ( $this->phaseMinutes < 36*60 )
			$buf .= l_t('normal');
		elseif ( $this->phaseMinutes < 3*24*60 )
			$buf .= l_t('slow');
		else
			$buf .= l_t('very slow');

		return $buf .')</span>';
	}

	/**
	 * The notifications list, not yet used, for showing notifications data related to a game within its game-panel
	 * @return string
	 */
	function notificationsList()
	{
		return '';
		return '<div class="notification">
					<span class="date"></span>
					<span class="message"></span>
				</div>';
	}

	/**
	 * Votes form data, only available in the board and if a member, so returns nothing here
	 * @return string
	 */
	function votes()
	{
		return '';
	}

	/**
	 * The header; the vital game info and the vital notice bar
	 * @return string
	 */
	function header()
	{
		$buf = '<div class="bar titleBar"><a name="gamePanel"></a>
				'.$this->titleBar().'
			</div>';

		$noticeBar = $this->gameNoticeBar();
		if ( $noticeBar )
			return $buf.'
				<div class="bar gameNoticeBar barAlt'.libHTML::alternate().'">
					'.$noticeBar.'
				</div>';
		else
			return $buf;
	}

	/**
	 * Members data; info about each member is given surrounded by the occupation-bar
	 * @return string
	 */
	function members()
	{
		$occupationBar = $this->Members->occupationBar();
		$buf = '';
		if ($this->moderatorSeesMemberInfo()) 
		{                                                
                	$buf .= '<div class="bar titleBar modEyes">Anonymous</div>';
		}
		$buf .= '<div class="panelBarGraph occupationBar">
				'.$occupationBar.'
			</div>
			<div class="membersList membersFullTable'.($this->moderatorSeesMemberInfo() ? ' modEyes': '').'">
				'.$this->Members->membersList().'
			</div>
			<div class="panelBarGraph occupationBar">
				'.$occupationBar.'
			</div>';
		return $buf;
	}

	/**
	 * The links allowing players to join/view games and see the archive data
	 * @return string
	 */
	function links()
	{
		$buf = '
			<div class="bar enterBar">
				<div class="enterBarJoin">
					'.$this->joinBar().'
				</div>
				<div class="enterBarOpen">
					'.$this->openBar().'
				</div>
				<div style="clear:both"></div>
			</div>
			';

		return $buf;
	}

	/**
	 * Links to the games archived data, maps/orders/etc
	 * @return string
	 */
	function archiveBar()
	{
		return '<strong>'.l_t('Archive:').'</strong> '.
			'<a href="board.php?gameID='.$this->id.'&amp;viewArchive=Orders">'.l_t('Orders').'</a>
			- <a href="board.php?gameID='.$this->id.'&amp;viewArchive=Maps">'.l_t('Maps').'</a>
			- <a href="board.php?gameID='.$this->id.'&amp;viewArchive=Messages">'.l_t('Messages').'</a>';
//			- <a href="board.php?gameID='.$this->id.'&amp;viewArchive=Reports">Reports</a>';
	}

	/**
	 * The invite code box for joining private games
	 * @return string
	 */
	private static function passwordBox()
	{
		return ' <span class="gamePasswordBox"><label>'.l_t('Invite Code:').'</label> <input type="password" name="gamepass" size="10" /></span> ';
	}

	/**
	 * A bar with form buttons letting you join/leave a game
	 * @return string
	 */
	function joinBar()
	{
		global $User;

		if ( $this->Members->isJoined() )
		{
			if ( $this->phase == 'Pre-game' )
			{
				$reason=$this->Members->cantLeaveReason();

				if($reason)
					return l_t("(Can't leave game; %s.)",$reason);
				else
					return '<form onsubmit="return confirm(\''.l_t('Are you sure you want to leave this game?').'\');" method="post" action="board.php?gameID='.$this->id.'"><div>
					<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />
					<input type="submit" name="leave" value="'.l_t('Leave game').'" class="form-submit" />
					</div></form>';
			}
			else
				return '';
		}
		else 
		{
			$buf = '';
			
			if ( $this->isJoinable() )
			{
				if( $this->minimumBet <= 100 && !$User->type['User'] && !$this->private )
					return l_t('A newly registered account can join this game; '.
						'<a href="register.php" class="light">register now</a> to join.');

				$question = l_t('Are you sure you want to join this game?').'\n\n';
				if ( $this->isLiveGame() )
				{
					$question .= l_t('The game will start at the scheduled time even if all %s players have joined.', count($this->Variant->countries));
				}
				else
				{
					$question .= l_t('The game will start when all %s players have joined.', count($this->Variant->countries));
				}

				if ($this->minimumReliabilityRating > 0)
				{
					$buf .= l_t('Minimum Reliability Rating: <span class="%s">%s%%</span>.',
						($User->reliabilityRating < $this->minimumReliabilityRating ? 'Austria' :'Italy'), 
						($this->minimumReliabilityRating));
				}
				if ($User->reliabilityRating >= $this->minimumReliabilityRating) 
				{

					$buf .= '<form onsubmit="return confirm(\''.$question.'\');" method="post" action="board.php?gameID='.$this->id.'"><div>
						<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />';

					if( $this->phase == 'Pre-game' )
					{
						$buf .= l_t('Bet to join: %s: ','<em>'.$this->minimumBet.libHTML::points().'</em>');
					}
					else
					{
						$buf .= $this->Members->selectCivilDisorder();
					}

					if ( $this->private )
						$buf .= '<br />'.self::passwordBox();

					$buf .= ' <input type="submit" name="join" value="'.l_t('Join').'" class="form-submit" />';

					$buf .= '</div></form>';
				}
			}
			if( $User->type['User'] && $this->phase != 'Finished')
			{
				$buf .= '<form method="post" action="redirect.php">'
				       .'<input type="hidden" name="gameID" value="'.$this->id.'">';
				if( ! $this->watched() ) {
					$buf .= '<input style="margin-top: 0.5em;" type="submit" title="'.l_t('Adds this game to the watched games list on your home page, and subscribes you to game notifications').'" '
					       .'class="form-submit" name="watch" value="'.l_t('Spectate game').'">';
				} else {
					$buf .= '<input type="submit" title="'.l_t('Removes this game from the watch list on your home page, and unsubscribes you from game notifications').'" '
						       .'class="form-submit" name="unwatch" value="'.l_t('Stop spectating game').'">';
				}
				$buf .= '</form>';
			}
		}
		return $buf;
	}

	/**
	 * A bar with a button letting people view the game
	 * @return string
	 */
	function openBar()
	{
		global $User;

		if( !$this->Members->isJoined() && $this->phase == 'Pre-game' )
			return '';

		return '<a href="board.php?gameID='.$this->id.'#gamePanel">'.
			l_t($this->Members->isJoined()?'Open':'View').'</a>';

		return '<form method="get" action="board.php#gamePanel"><div>
			<input type="hidden" name="gameID" value="'.$this->id.'" />
			<input type="submit" value="" class="form-submit" />
			</div></form>';
	}
}


?>
