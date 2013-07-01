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

require_once(l_r('gamepanel/game.php'));

/**
 * This class displays the game panel within a board context. It displays more info
 * and gives different functionality (e.g. voting)
 *
 * @package GamePanel
 */
class panelGameBoard extends panelGame
{
	function mapHTML() {
		$mapTurn = (($this->phase=='Pre-game'||$this->phase=='Diplomacy') ? $this->turn-1 : $this->turn);
		$smallmapLink = 'map.php?gameID='.$this->id.'&turn='.$mapTurn;
		$largemapLink = $smallmapLink.'&mapType=large';

		$staticFilename=Game::mapFilename($this->id, $mapTurn, 'small');
		if( file_exists($staticFilename) )
			$smallmapLink = STATICSRV.$staticFilename.'?nocache='.rand(0,99999);

		$map = '
		<div id="mapstore">
		  <script>   
            jQuery(document) .ready(function($) {
              $("#ex1").zoom({ url:" '.$largemapLink.'" });
                                                }) ;
          </script> 
                    <span class = \'zoom\' id= \'ex1\'>
			<img id="mapImage" src="'.$smallmapLink.'" alt=" " title="'.l_t('The small map for the current phase. If you are starting a new turn this will show the last turn\'s orders').'" />
			         <p class="lens">Click</p></span>
			<p class="lightgrey" style="text-align:center">
				<a href="#" onClick="loadMap('.$this->id.','.$mapTurn.',-1); return false;"><img id="Start" src="'.l_s('images/historyicons/Start_disabled.png').'" alt="'.l_t('Start').'" title="'.l_t('View the map from the first turn').'" /></a>
				<a href="#" onClick="loadMapStep('.$this->id.','.$mapTurn.',-1); return false;"><img id="Backward" src="'.l_s('images/historyicons/Backward_disabled.png').'" alt="'.l_t('Backward').'" title="'.l_t('View the map from the previous turn').'" /></a>
				<a href="#" onClick="toggleMoves('.$this->id.','.$mapTurn.'); return false;"><img id="NoMoves" src="'.l_s('images/historyicons/hidemoves.png').'" alt="'.l_t('noMoves').'" title="'.l_t('Toggle movement lines').'" /></a>

				<span id="LargeMapLink" class="lightgrey" style="width:150px"><a href="'.$largemapLink.'" target="_blank" class="light">
					<img src="'.l_s('images/historyicons/external.png').'" alt="'.l_t('Open large map').'" title="'.l_t('This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough.').'" />
				</a></span>

				<a href="#" onClick="loadMapStep('.$this->id.','.$mapTurn.',1); return false;"><img id="Forward" src="'.l_s('images/historyicons/Forward_disabled.png').'" alt="'.l_t('Forward').'" title="'.l_t('View the map from the next turn').'" /></a>
				<a href="#" onClick="loadMap('.$this->id.','.$mapTurn.','.$mapTurn.'); return false;"><img id="End" src="'.l_s('images/historyicons/End_disabled.png').'" alt="'.l_t('End').'" title="'.l_t('View the map from the most recent turn').'" /></a>
			</p>
			<p id="History" class="lightgrey"></p>
		</div>
';

		$this->mapJS($mapTurn);

		return $map;
	}

	protected function mapJS($mapTurn) {
		libHTML::$footerScript[] = 'turnToText='.$this->Variant->turnAsDateJS()."
		mapArrows($mapTurn,$mapTurn);
		";
		libHTML::$footerIncludes[] = l_j('mapUI.js');
	}

	function links()
	{
		$buf = '';

		if ( $this->turn > 0 || !( $this->phase == 'Pre-game' || $this->phase == 'Diplomacy' ) )
			$buf .= '
				<div class="bar archiveBar">
					'.$this->archiveBar().'
				</div>
				';

		$buf .= parent::links();

		return $buf;
	}

	function pausedInfo()
	{
		$buf = parent::pausedInfo();

		if( is_null($this->pauseTimeRemaining) )
			$remaining = $this->phaseMinutes*60;
		else
			$remaining = $this->pauseTimeRemaining;

		return $buf.' ('.l_t('%s left on unpause',libTime::timeLengthText($remaining)).')';
	}

	/**
	 * The main board-only functionality; the votes form for members to vote with.
	 * Finds allowed votes, takes what the member has voted for, and the votes which
	 * are passed, and gives the list of votes which can be voted/cancelled in a
	 * form, which board.php processes.
	 * @return string
	 */
	function votes()
	{
		global $User;
		if ( ( $this->phase == 'Pre-game' || $this->phase == 'Finished' ) ||
			!isset($this->Members->ByUserID[$User->id]) )
			return '';

		$vAllowed = Members::$votes;
		$vSet = $this->Members->ByUserID[$User->id]->votes;
		$vPassed = $this->Members->votesPassed();

		$vCancel=array();
		$vVote=array();
		foreach($vAllowed as $vote)
			if(in_array($vote, $vSet))
			{
				if(!in_array($vote, $vPassed))
					$vCancel[]=$vote;
			}
			else
				$vVote[]=$vote;

		$buf = '<div class="bar membersList memberVotePanel"><a name="votebar"></a>
				<table><tr class="member">
			<td class="memberLeftSide">
				<strong>'.l_t('Votes:').'</strong>
			</td>
			<td class="memberRightSide">
				'.$this->showVoteForm($vVote, $vCancel).'
			</td>
			</tr>
			</table>';

		return $buf . '</div>';
	}

	/**
	 * Returns the actual form, given the votes which can be voted for, and votes which can
	 * be cancelled.
	 *
	 * @param array $vVote Allowed votes
	 * @param array $vCancel Votes which can be cancelled
	 * @return string
	 */
	function showVoteForm($vVote, $vCancel)
	{
		$buf = '<form action="board.php?gameID='.$this->id.'#votebar" method="post">';
		$buf .= '<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />';

		$buf .= '<div class="memberUserDetail">';
		foreach($vVote as $vote)
		{
			if ( $vote == 'Pause' && $this->processStatus == 'Paused' )
				$vote = 'Unpause';

			$buf .= '<input type="submit" class="form-submit" name="'.$vote.'" value="'.l_t($vote).'" /> ';
		}
		$buf .= '</div>';

		if( $vCancel )
		{
			$buf .= '<div class="memberGameDetail">'.l_t('Cancel:').' ';
			foreach($vCancel as $vote)
			{
				if ( $vote == 'Pause' && $this->processStatus == 'Paused' )
					$vote = 'Unpause';

				$buf .= '<input type="submit" class="form-submit" name="'.$vote.'" value="'.l_t($vote).'" /> ';
			}

			$buf .= '</div>';
		}

		$buf .= '</form><div style="clear:both"></div>';
		return $buf;
	}

	public function __construct($gameData)
	{
		parent::__construct($gameData);
	}

	/**
	 * No open bar from within an open game
	 * @return string Nothing
	 */
	public function openBar()
	{
		return '';
	}

	/**
	 * The vital game header info, but with an occupation bar and presented to fit at the
	 * top of a game board.
	 *
	 * @return string
	 */
	public function contentHeader()
	{
		global $User;


		$buf = '<a name="gamePanel"></a>';
		$buf .= $this->header();

		$buf .= '<div class="panelBarGraph occupationBar">'.$this->Members->occupationBar().'</div>';

		return '<div class="variant'.$this->Variant->name.'">'.$buf.'</div>';
	}

	/**
	 * A modified header, which will also print the info about the member which has joined if applicable,
	 * for use at the top of a game board.
	 * @return string
	 */
	function header()
	{
		global $User;
		libHTML::$alternate=2;
		$buf = '<div class="titleBar barAlt'.libHTML::alternate().'">
				'.$this->titleBar().'
			</div>';

		$noticeBar = $this->gameNoticeBar();
		if ( $noticeBar )
		{
			$buf .= '
				<div class="bar gameNoticeBar barAlt'.libHTML::alternate().'">
					'.$noticeBar.'
				</div>';
		}

		if ( $this->Members->isJoined() && $this->phase != 'Pre-game' )
		{
			$buf .= '<div class="membersList">'.$this->Members->ByUserID[$User->id]->memberHeaderBar().'</div>';
		}


		return $buf;
	}

	/**
	 * A summary which is header-less, since it is displayed at the top of board.
	 * @return string
	 */
	function summary()
	{
		print '
		<div class="gamePanel variant'.$this->Variant->name.'">
			'.($this->Members->isJoined()?$this->votes():'').'
			'.$this->members().'
			'.$this->links().'
			<div class="bar lastBar"> </div>
		</div>
		';
	}
}
?>
