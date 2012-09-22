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

require_once('gamepanel/members.php');
require_once('lib/reliability.php');

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
			return count($this->Variant->countries).' players joined; game will start on next process cycle';
		elseif( $this->missingPlayerPolicy=='Strict'&&!$this->Members->isComplete() && time()>=$this->processTime )
			return "One or more players need to complete their orders before this strict/tournament game can go on";
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
		return 'Paused <img src="images/icons/pause.png" title="Game paused" />';
	}

	/**
	 * The next-process data, depending on whether paused/crashed/finished/etc
	 *
	 * @return string
	 */
	function gameTimeRemaining()
	{

		if( $this->phase == 'Finished' )
			return '<span class="gameTimeRemainingNextPhase">Finished:</span> '.
				libTime::text($this->processTime);

		if( $this->processStatus == 'Paused' )
			return $this->pausedInfo();
		elseif( $this->processStatus == 'Crashed' )
			return 'Crashed';

		if (!isset($timerCount))
			static $timerCount=0;
		$timerCount++;

		if( $this->phase == 'Pre-game' )
			$buf = '<span class="gameTimeRemainingNextPhase">Start:</span> '.
				$this->processTimetxt().' ('.libTime::text($this->processTime).')';
		else
		{
			$buf = '<span class="gameTimeRemainingNextPhase">Next:</span> '.
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
			return 'Game won by '.$Winner->memberName();
		}
		elseif( $this->gameOver == 'Drawn' )
		{
			return 'Game drawn';
		}
	}

	/**
	 * Game name
	 * @return string
	 */
	function titleBarName()
	{
		return $this->name;
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
			$buf .= '<img src="images/icons/star.png" alt="Featured" title="This is a featured game, one of the highest stakes games on the server!" /> ';

		if( $this->private )
			$buf .= '<img src="images/icons/lock.png" alt="Private" title="This is a private game; password needed!" /> ';

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

		$date=' - <span class="gameDate">'.$this->datetxt().'</span>, <span class="gamePhase">'.$this->phase.'</span>';

		$leftTop = '<div class="titleBarLeftSide">
				'.$this->gameIcons().
				'<span class="gameName">'.$this->titleBarName().'</span>';

		$leftBottom = '<div class="titleBarLeftSide">
				Pot: <span class="gamePot">'.$this->pot.' '.libHTML::points().'</span>';
			//<span class="gamePotType" title="'.$this->potType.'">('.($this->potType=='Points-per-supply-center'?'PPSC':'WTA').')</span>';

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
	
		global $User;
		
		$alternatives=array();
		if( $this->variantID!=1 )
			$alternatives[]=$this->Variant->link();
		if( $this->pressType=='NoPress')
			$alternatives[]='Gunboat';
		elseif( $this->pressType=='PublicPressOnly' )
			$alternatives[]='Public Press';
		if( $this->anon=='Yes' )
			$alternatives[]='Anon';
		if( $this->potType=='Winner-takes-all' )
			$alternatives[]='WTA';
		if( $this->chessTime > 0)
			$alternatives[]='Chess:'.$this->chessTime." min.";
			
		// The NMR-policy defaults
		if( ($this->specialCDturn != Config::$specialCDturnsDefault || $this->specialCDcount != Config::$specialCDcountDefault) && $this->specialCDturn >= $this->turn)
		{
			if ( $this->specialCDturn == 0 )
				$alternatives[]='NMR: Off';
			elseif( $this->specialCDturn == 5  && $this->specialCDcount == 2 )
				$alternatives[]='NMR: Committed';
			elseif( $this->specialCDturn > 90 && $this->specialCDcount > 90 )
				$alternatives[]='NMR: Serious';
			else
			
				$alternatives[]='NMR:'.($this->specialCDturn > 90 ? '&infin;' : $this->specialCDturn).'/'.($this->specialCDcount > 90 ? '&infin;' : ($this->specialCDcount == 0 ? 'off' : $this->specialCDcount));
		}
		
		//	Show the end of the game in the options if set.
		if(( $this->targetSCs > 0) && ($this->maxTurns > 0))
			$alternatives[]='EoG: '.$this->targetSCs.' SCs or "'.$this->Variant->turnAsDate($this->maxTurns -1).'"';
		elseif( $this->maxTurns > 0)
			$alternatives[]='EoG: "'.$this->Variant->turnAsDate($this->maxTurns -1).'"';
		elseif( $this->targetSCs > 0)
			$alternatives[]='EoG: '.$this->targetSCs.' SCs';
			
		if( $this->rlPolicy=='Friends')
			$alternatives[]='OnlyFriends';

		if ( $alternatives )
			return '<div class="titleBarLeftSide" style="float:left">
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
		$buf = '<strong>'.libTime::timeLengthText($this->phaseMinutes*60).'</strong>
			/phase <span class="gameTimeHoursPerPhaseText">(';

		if ( $this->phaseMinutes < 60 )
			$buf .= 'live';
		elseif ( $this->phaseMinutes < 6*60 )
			$buf .= 'very fast';
		elseif ( $this->phaseMinutes < 16*60 )
			$buf .= 'fast';
		elseif( $this->phaseMinutes < 36*60 )
			$buf .= 'normal';
		elseif( $this->phaseMinutes < 3*24*60 )
			$buf .= 'slow';
		else
			$buf .= 'very slow';

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
		$buf = '<div class="panelBarGraph occupationBar">
				'.$occupationBar.'
			</div>
			<div class="membersList membersFullTable">
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
		return '<strong>Archive:</strong> '.
			'<a href="board.php?gameID='.$this->id.'&amp;viewArchive=Orders">Orders</a>
			- <a href="board.php?gameID='.$this->id.'&amp;viewArchive=Maps">Maps</a>
			- <a href="board.php?gameID='.$this->id.'&amp;viewArchive=Messages">Messages</a>';
//			- <a href="board.php?gameID='.$this->id.'&amp;viewArchive=Reports">Reports</a>';
	}

	/**
	 * The password box for joining private games
	 * @return string
	 */
	private static function passwordBox()
	{
		return ' <span class="gamePasswordBox"><label>Password:</label> <input type="password" name="gamepass" size="10" /></span> ';
	}

	/**
	 * A bar with form buttons letting you join/leave a game
	 * @return string
	 */
	function joinBar()
	{
		global $DB,$User;

		if ( $this->Members->isJoined() )
		{
			if ( $this->phase == 'Pre-game' )
			{
				$reason=$this->Members->cantLeaveReason();

				if($reason)
					return "(Can't leave game; ".$reason.".)";
				else
					return '<form onsubmit="return confirm(\'Are you sure you want to leave this game?\');" method="post" action="board.php?gameID='.$this->id.'"><div>
					<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />
					<input type="submit" name="leave" value="Leave game" class="form-submit" />
					</div></form>';
			}
			else
				return '';
		}
		elseif ( !$this->isJoinable() )
			return '';

		if( $this->minimumBet <= 100 && !$User->type['User'] && !$this->private )
			return 'A newly registered account can join this game;
				<a href="register.php" class="light">register now</a> to join.';

				
		$buf = '<form onsubmit="return confirm(\'Are you sure you want to join this game?';
		
		if ($this->phaseMinutes > 30)
		{
			list($turns,$games) = $DB->sql_row('SELECT SUM(turn), COUNT(*) FROM wD_Games WHERE variantID='.$this->Variant->id.' AND phase = "Finished"');
			if ($games > 3)
			{
				$avgDur = libTime::timeLengthText((($turns / $games) - $this->turn) * 2.5 * $this->phaseMinutes * 60 );
				if ($avgDur > 0)
					$buf .= '\nLooking at our stats this game might take (roughly) '.$avgDur.' to complete.';
			}
		}
		
		$buf .= '\');" method="post" action="board.php?gameID='.$this->id.'"><div>
			<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />';
			
		// Show join requirements:
		if (($this->minRating > 0) || ($this->minPhases > 0))
		{
			$buf .= '<em>Requirements:</em> ';
			if( $this->minRating > 0)
				$buf .= 'RR >= <em>'.libReliability::Grade($this->minRating).'</em> / ';
			if( $this->minPhases > 0)
				$buf .= 'MinPhases > <em>'.(int)($this->minPhases - 1) .'</em> / ';
		}
		
		if( $this->phase == 'Pre-game'&& count($this->Members->ByCountryID)>0 )
		{
			$buf .= '<label>Join for</label> <em>'.$this->minimumBet.libHTML::points().'</em> as: <select name="countryID">';
			foreach($this->Variant->countries as $id=>$name)
				if (!isset($this->Members->ByCountryID[($id +1)]))
					$buf .= '<option value="'.($id +1).'" />'.$name.'</option>';
			$buf .= '</select>';
			
			if ( $this->private )
				$buf .= '<br />'.self::passwordBox();
				
			$buf .= '<input type="submit" name="join" value="Join" class="form-submit" />';			
		}
		elseif( $this->phase == 'Pre-game' )
		{
			$buf .= 'Bet to join: <em>'.$this->minimumBet.libHTML::points().'</em>: ';

			if ( $this->private )
				$buf .= '<br />'.self::passwordBox();

			$buf .= '<input type="submit" name="join" value="Join" class="form-submit" />';

		}
		else
		{
			$buf .= $this->Members->selectCivilDisorder();

			if ( $this->private )
				$buf .= '<br />'.self::passwordBox();

			$buf .= ' <input type="submit" name="join" value="Join" class="form-submit" />';
		}

		$buf .= '</div></form>';
		return $buf;
	}

	/**
	 * A bar with a button letting people view the game
	 * @return string
	 */
	function openBar()
	{
		global $User;

/*		I've put the following code in remarks to isplay the view Button, even if it's the PreGame-Phase 
		(to view the chat for example).
		if( !$this->Members->isJoined() && $this->phase == 'Pre-game' )
			return '';
*/
		return '<a href="board.php?gameID='.$this->id.'#gamePanel">'.
			($this->Members->isJoined()?'Open':'View').'</a>';

		return '<form method="get" action="board.php#gamePanel"><div>
			<input type="hidden" name="gameID" value="'.$this->id.'" />
			<input type="submit" value="" class="form-submit" />
			</div></form>';
	}
}


?>
