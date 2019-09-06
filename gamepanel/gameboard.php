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
	function mapHTML() 
	{
		global $User;

		$mapTurn = (($this->phase=='Pre-game'||$this->phase=='Diplomacy') ? $this->turn-1 : $this->turn);
		$smallmapLink = 'map.php?gameID='.$this->id.'&turn='.$mapTurn .($User->options->value['showMoves'] == 'No'? '&hideMoves':'');
		$largemapLink = $smallmapLink.'&mapType=large'.($User->options->value['showMoves']=='No'?'&hideMoves':'');

		$staticFilename=Game::mapFilename($this->id, $mapTurn, 'small');

		if( file_exists($staticFilename) && $User->options->value['showMoves'] == 'Yes' )
			$smallmapLink = STATICSRV.$staticFilename.'?nocache='.rand(0,99999);

		$map = '
		<div id="mapstore">
			<img id="mapImage" src="'.$smallmapLink.'" alt=" " title="'.l_t('The small map for the current phase. If you are starting a new turn this will show the last turn\'s orders').'" />
			<p class="lightgrey" style="text-align:center">
				<a class="mapnav" href="#" onClick="loadMap('.$this->id.','.$mapTurn.',-1); return false;">
                      <img id="Start" src="'.l_s('images/historyicons/Start_disabled.png').'" alt="'.l_t('Start').'" title="'.l_t('View the map from the first turn').'" /></a>
				<a class="mapnav" href="#" onClick="loadMapStep('.$this->id.','.$mapTurn.',-1); return false;"><img id="Backward" src="'.l_s('images/historyicons/Backward_disabled.png').'" alt="'.l_t('Backward').'" title="'.l_t('View the map from the previous turn').'" /></a>
                <!--    The following is the toggle for removing the movement arrows. Uncomment this section if you want the movement arrow toggle.
                <a class="mapnav" href="#" onClick="toggleMoves('.$this->id.','.$mapTurn.'); return false;"><img id="NoMoves" src="images/historyicons/hidemoves.png" alt="NoMoves" title="Toggle movement lines" />
                </a>                 -->
			   <a id="LargeMapLink" class="mapnav" href="'.$largemapLink.'" target="_blank" class="light"><img src="'.l_s('images/historyicons/external.png').'" alt="'.l_t('Open large map').'" title="'.l_t('This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough.').'" /></a></span>
                     
				<a class="mapnav" href="#" onClick="loadMapStep('.$this->id.','.$mapTurn.',1); return false;"><img id="Forward" src="'.l_s('images/historyicons/Forward_disabled.png').'" alt="'.l_t('Forward').'" title="'.l_t('View the map from the next turn').'" /></a>
				<a class="mapnav" href="#" onClick="loadMap('.$this->id.','.$mapTurn.','.$mapTurn.'); return false;"><img id="End" src="'.l_s('images/historyicons/End_disabled.png').'" alt="'.l_t('End').'" title="'.l_t('View the map from the most recent turn').'" /></a>'.
				($this->Members->isJoined() ? '<a class="mapnav" href="#" onClick="togglePreview('.$this->id.','.$mapTurn.'); return false;"><img id="Preview" src="images/historyicons/Preview.png" alt="PreviewMoves" title="Show server side stored orders on the map" /></a>' : '').'
							
			</p>
			<p id="History" class="lightgrey"></p>
		</div>';

		$this->mapJS($mapTurn);

		return $map;
	}

	protected function mapJS($mapTurn) 
	{
		libHTML::$footerScript[] = 'turnToText='.$this->Variant->turnAsDateJS()."
		mapArrows($mapTurn,$mapTurn);
		";
		libHTML::$footerIncludes[] = l_j('mapUI.js');
	}

	function links()
	{
		$buf = '';

		if ( $this->phase != 'Pre-game') 
			$buf .= '<div class="bar archiveBar"> '.$this->archiveBar().'</div> ';

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
		if ( ( $this->phase == 'Pre-game' || $this->phase == 'Finished' ) || !isset($this->Members->ByUserID[$User->id]) ) return '';

		$vAllowed = Members::$votes;
		$vSet = $this->Members->ByUserID[$User->id]->votes;
		$vPassed = $this->Members->votesPassed();

		$vCancel=array();
		$vVote=array();

		foreach($vAllowed as $vote)
		{
			// Set when the option to vote concede is allowed. Restrict it to games set via the config. 
			if ($vote == 'Concede')
			{
				if ( (empty(Config::$concedeVariants)) || (in_array($this->variantID, Config::$concedeVariants)) )
				{
					if(in_array($vote, $vSet))
					{
						if(!in_array($vote, $vPassed)) $vCancel[]=$vote;
					}
					else $vVote[]=$vote;
				}
			}
			else
			{
				if(in_array($vote, $vSet))
				{
					if(!in_array($vote, $vPassed)) $vCancel[]=$vote;
				}
				else $vVote[]=$vote;
			}			
		}

		$buf = '<div style="width: 300px; margin: 0 auto; text-align:center;"><a href="contactUsDirect.php" align="center";>Need help?</a></div>
		<div class="bar membersList memberVotePanel"><a name="votebar"></a>
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
		$buf = '<form onsubmit="return confirm(\''. l_t("Are you sure you want to cast this vote?").'\');" action="board.php?gameID='.$this->id.'#votebar" method="post">';
		$buf .= '<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />';

        $buf .= '<div class="memberUserDetail">';

		foreach($vVote as $vote)
		{
			if ( $vote == 'Pause' && $this->processStatus == 'Paused' )
				$vote = 'Unpause';

			$buf .= '<input type="submit" class="form-submit" name="'.$vote.'" value="'.l_t($vote).'" /> ';
		}
		$buf .= '</div></form>';
		$buf .= '<form onsubmit="return confirm(\''. l_t("Are you sure you want to withdraw this vote?").'\');" action="board.php?gameID='.$this->id.'#votebar" method="post">';
		$buf .= '<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />';

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
		
		$buf .= '</form>';
		
		$buf .= '<img id = "modBtnVote" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" style="padding: 8px;"/>
		<div id="voteModal" class="modal">
			<div class="modal-content">
				<span class="close1">&times;</span>
				<p><strong>Draw Vote: </strong></br>
					If all remaining players vote draw, the game will be drawn. ';
		switch ($this->potType) 
		{
			case 'Points-per-supply-center':
					$buf .= 'This game is scored using points per supply center. In a draw, points are split evenly among all players remaining.';
					break;
			case 'Winner-takes-all':
					$buf .= 'This game is scored using draw size scoring. In a draw, points are split evenly among all players remaining.';
					break;             
			case 'Unranked':
					$buf .= 'This game is unranked. In a draw, all points are returned to their previous owners.';
					break;             
			case 'Sum-of-squares':
					$buf .= 'This game is scored using sum of squares. In a draw, points are split among remaining players based upon how many supply centers they have.';
					break;             
			default:
					trigger_error("Unknown pot type '".$this->potType."'");
					break;
		}
		switch ($this->drawType) 
		{
			case 'draw-votes-public':
				$buf .= ' Draw votes are publicly displayed in this game.';
				break;
			case 'draw-votes-hidden':
				$buf .= ' Draw votes are not publicly known in this game.';
				break;
			default:
				trigger_error("Unknown draw type '".$this->drawType."'");
				break;
		}
		$buf.= '</p>';
		
		if( $this->processStatus == 'Paused' )
		{
			$buf .= '<p><strong>Unpause Vote: </strong></br>
						If all remaining players vote unpause, the game will be unpaused. If a game has been paused for a long period of time, you may email the mods at webdipmod@gmail.com and they will look into getting the game started back up.
					</p>';
		}
		else
		{
			$buf .= '<p><strong>Pause Vote: </strong></br>
						If all remaining players vote pause, the game will be paused. The game will remain paused until all players vote unpause. If you need a game paused'. ($this->pressType == 'NoPress' ? '' : ' due to an emergency').', click on the Need Help? link just above this icon to contact the mods.
					</p>';
		}
		
		$buf .= '<p><strong>Cancel Vote: </strong></br>
					If all remaining players vote cancel, the game will be cancelled. All points will be refunded, and the game will be deleted. Cancels are typically used in the first year or two of a game with missing players.
				</p>
			</div>
		</div>';
		
		$buf .= '<script>
		var modal1 = document.getElementById("voteModal");
		var btn1 = document.getElementById("modBtnVote");
		var span1 = document.getElementsByClassName("close1")[0];
		btn1.onclick = function() { modal1.style.display = "block"; }
		span1.onclick = function() { modal1.style.display = "none"; }
		window.onclick = function(event) {
		  if (event.target == modal1) { modal1.style.display = "none"; }
		}
		</script>';

    $buf .= '<div style="clear:both"></div>';

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

		$buf .= '<div class="panelBarGraphTop occupationBar">'.$this->Members->occupationBar().'</div>';

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
		$buf = '<div class="titleBar">
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
		</div>';
	}
}
?>
