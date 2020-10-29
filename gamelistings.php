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

/**
 * @package Base
 * @subpackage Game
 */

require_once('header.php');
require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/game.php'));

libHTML::starthtml();

print '<div class="content">';

// game listings tutorial
if (isset($_COOKIE['wD-Tutorial-JoinNewGame'])) {
	$tutorialMessage = l_t('
		This is the page where you can view games to join. You can choose to join new games 
		that have not begun yet, take over an open position that another player abandoned, 
		or check out any active and finished games. You can also find a full list of games you
		are in and check their status from here. 
		<br>
		To join a new game, you need to have the points required to join as well as the reliability
		rating required in order to join. Your reliability rating may not yet be high enough to join 
		some games. To improve your reliability rating, join a game you meet the requirements for or
		<a href="gamecreate.php" target="_blank">create a game of your own</a> and make sure that you 
		enter your moves before the deadline.
	');

	libHTML::help('Game Search', $tutorialMessage);

	unset($_COOKIE['wD-Tutorial-JoinNewGame']);
	setcookie('wD-Tutorial-JoinNewGame', '', time()-3600);
}

global $User, $Misc, $DB;

$tabs = array();
$sortCol = 'id';
$sortType = 'desc';

if ( isset($_REQUEST['sortCol']))
{
	if ($_REQUEST['sortCol'] == 'name') { $sortCol='name'; }
	else if ($_REQUEST['sortCol'] == 'pot') { $sortCol='pot'; }
	else if ($_REQUEST['sortCol'] == 'phaseMinutes') { $sortCol='phaseMinutes'; }
	else if ($_REQUEST['sortCol'] == 'minimumBet') {$sortCol='minimumBet'; }
	else if ($_REQUEST['sortCol'] == 'minimumReliabilityRating') {$sortCol='minimumReliabilityRating'; }
	else if ($_REQUEST['sortCol'] == 'watchedGames') {$sortCol='watchedGames'; }
	else if ($_REQUEST['sortCol'] == 'turn') {$sortCol='turn'; }
	else if ($_REQUEST['sortCol'] == 'processTime') {$sortCol='processTime'; }
}
if ( isset($_REQUEST['sortType'])) { if ($_REQUEST['sortType'] == 'asc') { $sortType='asc'; } }


if($User->type['User'])
	$tabs['My games']=l_t("Active games which you have joined");

$tabs['New']=l_t("Games which haven't yet started");
$tabs['Open Positions']=l_t("Public games which have open spaces");
$tabs['Active']=l_t("Games which are going on now");
$tabs['Finished']=l_t("Games which have ended");
$tabs['Search']=l_t("The full game listing search panel");

$tab = 'Active';
$tabNames = array_keys($tabs);

if( isset($_REQUEST['gamelistType']) && in_array($_REQUEST['gamelistType'], $tabNames) )
{
	$tab = $_SESSION['gamelistType'] = $_REQUEST['gamelistType'];
}
elseif( isset($_SESSION['gamelistType']) && in_array($_SESSION['gamelistType'], $tabNames) )
{
	$tab = $_SESSION['gamelistType'];
}
if ($tab <> 'Search')
{
	print "<a name='results'></a>";
}
print '<div class="gamelistings-tabsNew">';

$GamesNewUser = $GamesOpenUser = $GamesActiveUser = 0;
if($User->type['User'] )
{
	list($GamesNewUser) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id
		WHERE g.phase = 'Pre-game' AND m.userID = ".$User->id);
	list($GamesOpenUser) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id
		WHERE g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.phase <> 'Pre-game' AND g.phase <> 'Finished'
		AND m.userID = ".$User->id." AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " "));
	list($GamesMine) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id
		WHERE g.phase <> 'Finished' AND m.userID = ".$User->id);
	list($GamesOpen) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE minimumBet IS NOT NULL AND password IS NULL AND gameOver = 'No'
		AND phase <> 'Pre-game' AND phase <> 'Finished'
		AND ".$User->points." >= minimumBet AND ".$User->reliabilityRating." >= minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " "));
}
else
{
	list($GamesOpen) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE minimumBet IS NOT NULL AND password IS NULL AND gameOver = 'No'
		AND phase <> 'Pre-game' AND phase <> 'Finished'");
}
list($GamesNew) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE phase = 'Pre-game'");
list($GamesActive) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE phase <> 'Pre-game' AND phase <> 'Finished'");
list($GamesFinished) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE phase = 'Finished'");

$GamesNew -= $GamesNewUser;
$GamesOpen -= $GamesOpenUser;

foreach($tabs as $tabChoice=>$tabTitle)
{
	print '<a title="'.$tabTitle.'" href="gamelistings.php?gamelistType='.$tabChoice;

	if ( $tab == $tabChoice )
	{
		print '" class="gamelistings-tabsNewActive"';
	}
	else
		print '"';

	print '>'.l_t($tabChoice);

	switch($tabChoice)
	{
		case 'My games':
		print ' ('.$GamesMine.')';
		break;

		case 'New':
		print ' ('.$GamesNew.')';
		break;

		case 'Open Positions':
		print ' ('.$GamesOpen.')';
		break;

		case 'Active':
		print ' ('.$GamesActive.')';
		break;

	}

	print '</a> ';
}

print '</div>';


libHTML::pagebreak();

$pagenum = 1;
$resultsPerPage = 20;
$maxPage = 0;
$totalResults = 0;
$tournamentID = 0;

if ( isset($_REQUEST['pagenum'])) { $pagenum=(int)$_REQUEST['pagenum']; }


if ($tab == 'My games')
{
	if($User->type['User'])
	{
		$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id
			WHERE g.phase <> 'Finished' AND m.userID = ".$User->id;
		$totalResults = $GamesMine;
	}
	else
	{
		$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g WHERE g.phase <> 'Pre-game' AND g.phase <> 'Finished'";
		$totalResults = $GamesActive;
	}
}
elseif ($tab == 'New')
{
	$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g WHERE g.phase = 'Pre-game'";
	$totalResults = $GamesNew;
}
elseif ($tab == 'Open Positions')
{
	$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g WHERE g.phase <> 'Pre-game' AND g.phase <> 'Finished'
		AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No'";
		if($User->type['User'])
		{
			$SQL .= " AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
		}
	$totalResults = $GamesOpen;
}
elseif ($tab == 'Active')
{
	$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g WHERE g.phase <> 'Pre-game' AND g.phase <> 'Finished'";
	$totalResults = $GamesActive;
}
elseif ($tab == 'Finished')
{
	$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g WHERE g.phase = 'Finished'";
	$totalResults = $GamesFinished;
}
else
{
	$required = array('status', 'userGames', 'seeJoinable', 'privacy', 'potType', 'drawVotes', 'variant', 'excusedTurns', 'phaseLengthMin', 'phaseLengthMax', 'rrMin', 'rrMax', 'anonymity', 'messageNorm', 'messagePub', 'messageNon', 'messageRule','betMin','betMax', 'round');
	$missing = array_diff($required, array_keys($_REQUEST));
	foreach($missing as $m )
	{
    $_REQUEST[$m] = 'All';
	}
	if (isset($_REQUEST['userID']))
	{
		try
		{
			$UserProfile = new User($_REQUEST['userID']);
		}
		catch (Exception $e)
		{
			libHTML::error(l_t("Invalid user ID given."));
		}
		print l_t('<b>Showing only %s\'s games ',$UserProfile->username).'(<a href="gamelistings.php?gamelistType=Search">Clear</a>)</b></br>';
	}
	if (isset($_REQUEST['tournamentID']))
	{
		$tournamentID = (int)$_REQUEST['tournamentID'];
		list($tournamentName, $tournamentRounds) = $DB->sql_row('SELECT name, totalRounds FROM wD_Tournaments WHERE id = "'.$tournamentID.'";');
		if ($tournamentName == "")
		{
			unset ($_REQUEST['tournamentID']);
		}
		else
		{
			print l_t('<b>Showing only games from %s ',$tournamentName).'(<a href="gamelistings.php?gamelistType=Search">Clear</a>)</b></br>';
		}
	}
	print '</br><div class = "gameCreateShow">
			<FORM class="advancedSearch" method="get" action="gamelistings.php#results">
			<INPUT type="hidden" name="gamelistType" value="Search" />
			<p><strong>Game Status:<select class="gameCreate" name="status">
				<option'.(($_REQUEST['status']=='All') ? ' selected="selected"' : '').' value="All">All</option>
				<option'.(($_REQUEST['status']=='Pre-game') ? ' selected="selected"' : '').' value="Pre-game">Pre-Game</option>
				<option'.(($_REQUEST['status']=='Active') ? ' selected="selected"' : '').' value="Active">All Active</option>
				<option'.(($_REQUEST['status']=='Paused') ? ' selected="selected"' : '').' value="Paused">Paused</option>
				<option'.(($_REQUEST['status']=='Running') ? ' selected="selected"' : '').' value="Running">Running (excludes paused games)</option>
				<option'.(($_REQUEST['status']=='Finished') ? ' selected="selected"' : '').' value="Finished">All Finished</option>
				<option'.(($_REQUEST['status']=='Won') ? ' selected="selected"' : '').' value="Won">Won</option>
				<option'.(($_REQUEST['status']=='Drawn') ? ' selected="selected"' : '').' value="Drawn">Drawn</option>
			</select></p>';
			if (isset($_REQUEST['userID']))
			{
				print '<INPUT type="hidden" name="userID" value="'.$UserProfile->id.'">';
			}
			elseif (isset($_REQUEST['tournamentID']))
			{
				print '<INPUT type="hidden" name="tournamentID" value="'.$tournamentID.'">';
			}
			else
			{
				print '<p>My Games: <select class="gameCreate" name="userGames">
					<option'.(($_REQUEST['userGames']=='All') ? ' selected="selected"' : '').' value="All">All Games</option>
					<option'.(($_REQUEST['userGames']=='include') ? ' selected="selected"' : '').' value="include">My Games</option>
				</select></p>';
			}
			if (isset($_REQUEST['tournamentID']))
			{
				print '<p>Round: <select class="gameCreate" name="round">
					<option'.(($_REQUEST['round']=='All') ? ' selected="selected"' : '').' value="All">All</option>';
					for($i = 1; $i <= $tournamentRounds; $i++)
					{
						print '<option'.(($_REQUEST['round']== $i) ? ' selected="selected"' : '').' value="'.$i.'">'.$i.'</option>';
					}
				print '</select></p>';
			}
			else
			{
				print '<p>Joinable Games: <select class="gameCreate" name="seeJoinable">
					<option'.(($_REQUEST['seeJoinable']=='All') ? ' selected="selected"' : '').' value="All">All Games</option>
					<option'.(($_REQUEST['seeJoinable']=='yes') ? ' selected="selected"' : '').' value="yes">All Joinable Games</option>
					<option'.(($_REQUEST['seeJoinable']=='active') ? ' selected="selected"' : '').' value="active">Active Joinable Games Only</option>
					<option'.(($_REQUEST['seeJoinable']=='new') ? ' selected="selected"' : '').' value="new">New Joinable Games Only</option>
				</select></p>';
			}
			print '<p>Privacy: <select class="gameCreate" name="privacy">
				<option'.(($_REQUEST['privacy']=='All') ? ' selected="selected"' : '').' value="All">All</option>
				<option'.(($_REQUEST['privacy']=='private') ? ' selected="selected"' : '').' value="private">Private</option>
				<option'.(($_REQUEST['privacy']=='public') ? ' selected="selected"' : '').' value="public">Public</option>
			</select></p>
			<p>Scoring: <select class="gameCreate" name="potType">
				<option'.(($_REQUEST['potType']=='All') ? ' selected="selected"' : '').' value="All">All</option>
				<option'.(($_REQUEST['potType']=='dss') ? ' selected="selected"' : '').' value="dss">Draw Size Scoring</option>
				<option'.(($_REQUEST['potType']=='sos') ? ' selected="selected"' : '').' value="sos">Sum of Squares</option>
				<option'.(($_REQUEST['potType']=='ppsc') ? ' selected="selected"' : '').' value="ppsc">Points Per Supply Center</option>
				<option'.(($_REQUEST['potType']=='unranked') ? ' selected="selected"' : '').' value="unranked">Unranked</option>
			</select></p>
			<p>Draw Votes: <select class="gameCreate" name="drawVotes">
				<option'.(($_REQUEST['drawVotes']=='All') ? ' selected="selected"' : '').' value="All">All</option>
				<option'.(($_REQUEST['drawVotes']=='hidden') ? ' selected="selected"' : '').' value="hidden">Hidden Votes</option>
				<option'.(($_REQUEST['drawVotes']=='public') ? ' selected="selected"' : '').' value="public">Public Votes</option>
			</select></p>
			<p>Variant: <select class="gameCreate" name="variant">
				<option'.(($_REQUEST['variant']=='All') ? ' selected="selected"' : '').' value="All">All</option>';
			foreach (Config::$variants as $variantID=>$variantName)
			{
				if($variantID != 57) {print '<option'.(($_REQUEST['variant']==$variantName) ? ' selected="selected"' : '').' value='.$variantName.'>'.$variantName.'</option>';}
			}
			print '</select></p>
			<p>Excused Missing Turns: <select class="gameCreate" name="excusedTurns">
				<option'.(($_REQUEST['excusedTurns']=='All') ? ' selected="selected"' : '').' value="All">All</option>
				<option'.(($_REQUEST['excusedTurns']=='0') ? ' selected="selected"' : '').' value="0">0</option>
				<option'.(($_REQUEST['excusedTurns']=='1') ? ' selected="selected"' : '').' value="1">1</option>
				<option'.(($_REQUEST['excusedTurns']=='2') ? ' selected="selected"' : '').' value="2">2</option>
				<option'.(($_REQUEST['excusedTurns']=='3') ? ' selected="selected"' : '').' value="3">3</option>
				<option'.(($_REQUEST['excusedTurns']=='4') ? ' selected="selected"' : '').' value="4">4</option>
			</select></p>
			<p>Anonymity: <select class="gameCreate" name="anonymity">
						<option'.(($_REQUEST['anonymity']=='All') ? ' selected="selected"' : '').' value="All">All</option>
						<option'.(($_REQUEST['anonymity']=='yes') ? ' selected="selected"' : '').' value="yes">Anonymous</option>
						<option'.(($_REQUEST['anonymity']=='no') ? ' selected="selected"' : '').' value="no">Non-Anonymous</option>
					</select></p>
			<p>Phase Length From <select class="gameCreate" name="phaseLengthMin">
				<option'.(($_REQUEST['phaseLengthMin']=='All') ? ' selected="selected"' : '').' value="All">5 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMin']=='7m') ? ' selected="selected"' : '').' value="7m">7 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMin']=='10m') ? ' selected="selected"' : '').' value="10m">10 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMin']=='15m') ? ' selected="selected"' : '').' value="15m">15 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMin']=='20m') ? ' selected="selected"' : '').' value="20m">20 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMin']=='30m') ? ' selected="selected"' : '').' value="30m">30 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMin']=='1h') ? ' selected="selected"' : '').' value="1h">1 Hour</option>
				<option'.(($_REQUEST['phaseLengthMin']=='2h') ? ' selected="selected"' : '').' value="2h">2 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='4h') ? ' selected="selected"' : '').' value="4h">4 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='6h') ? ' selected="selected"' : '').' value="6h">6 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='8h') ? ' selected="selected"' : '').' value="8h">8 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='10h') ? ' selected="selected"' : '').' value="10h">10 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='12h') ? ' selected="selected"' : '').' value="12h">12 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='14h') ? ' selected="selected"' : '').' value="14h">14 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='16h') ? ' selected="selected"' : '').' value="16h">16 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='18h') ? ' selected="selected"' : '').' value="18h">18 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='20h') ? ' selected="selected"' : '').' value="20h">20 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='22h') ? ' selected="selected"' : '').' value="22h">22 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='1d') ? ' selected="selected"' : '').' value="1d">1 Day</option>
				<option'.(($_REQUEST['phaseLengthMin']=='1d1h') ? ' selected="selected"' : '').' value="1d1h">1 Day 1 Hour</option>
				<option'.(($_REQUEST['phaseLengthMin']=='1d12h') ? ' selected="selected"' : '').' value="1d12h">1 Day 12 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='2d') ? ' selected="selected"' : '').' value="2d">2 Days</option>
				<option'.(($_REQUEST['phaseLengthMin']=='2d2h') ? ' selected="selected"' : '').' value="2d2h">2 Days 2 Hours</option>
				<option'.(($_REQUEST['phaseLengthMin']=='3d') ? ' selected="selected"' : '').' value="3d">3 Days</option>
				<option'.(($_REQUEST['phaseLengthMin']=='4d') ? ' selected="selected"' : '').' value="4d">4 Days</option>
				<option'.(($_REQUEST['phaseLengthMin']=='5d') ? ' selected="selected"' : '').' value="5d">5 Days</option>
				<option'.(($_REQUEST['phaseLengthMin']=='6d') ? ' selected="selected"' : '').' value="6d">6 Days</option>
				<option'.(($_REQUEST['phaseLengthMin']=='7d') ? ' selected="selected"' : '').' value="7d">7 Days</option>
				<option'.(($_REQUEST['phaseLengthMin']=='10d') ? ' selected="selected"' : '').' value="10d">10 Days</option>
			</select>
			To<select class="gameCreate" name="phaseLengthMax">
				<option'.(($_REQUEST['phaseLengthMax']=='5m') ? ' selected="selected"' : '').' value="5m">5 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMax']=='7m') ? ' selected="selected"' : '').' value="7m">7 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMax']=='10m') ? ' selected="selected"' : '').' value="10m">10 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMax']=='15m') ? ' selected="selected"' : '').' value="15m">15 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMax']=='20m') ? ' selected="selected"' : '').' value="20m">20 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMax']=='30m') ? ' selected="selected"' : '').' value="30m">30 Minutes</option>
				<option'.(($_REQUEST['phaseLengthMax']=='1h') ? ' selected="selected"' : '').' value="1h">1 Hour</option>
				<option'.(($_REQUEST['phaseLengthMax']=='2h') ? ' selected="selected"' : '').' value="2h">2 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='4h') ? ' selected="selected"' : '').' value="4h">4 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='6h') ? ' selected="selected"' : '').' value="6h">6 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='8h') ? ' selected="selected"' : '').' value="8h">8 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='10h') ? ' selected="selected"' : '').' value="10h">10 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='12h') ? ' selected="selected"' : '').' value="12h">12 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='14h') ? ' selected="selected"' : '').' value="14h">14 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='16h') ? ' selected="selected"' : '').' value="16h">16 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='18h') ? ' selected="selected"' : '').' value="18h">18 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='20h') ? ' selected="selected"' : '').' value="20h">20 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='22h') ? ' selected="selected"' : '').' value="22h">22 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='1d') ? ' selected="selected"' : '').' value="1d">1 Day</option>
				<option'.(($_REQUEST['phaseLengthMax']=='1d1h') ? ' selected="selected"' : '').' value="1d1h">1 Day 1 Hour</option>
				<option'.(($_REQUEST['phaseLengthMax']=='1d12h') ? ' selected="selected"' : '').' value="1d12h">1 Day 12 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='2d') ? ' selected="selected"' : '').' value="2d">2 Days</option>
				<option'.(($_REQUEST['phaseLengthMax']=='2d2h') ? ' selected="selected"' : '').' value="2d2h">2 Days 2 Hours</option>
				<option'.(($_REQUEST['phaseLengthMax']=='3d') ? ' selected="selected"' : '').' value="3d">3 Days</option>
				<option'.(($_REQUEST['phaseLengthMax']=='4d') ? ' selected="selected"' : '').' value="4d">4 Days</option>
				<option'.(($_REQUEST['phaseLengthMax']=='5d') ? ' selected="selected"' : '').' value="5d">5 Days</option>
				<option'.(($_REQUEST['phaseLengthMax']=='6d') ? ' selected="selected"' : '').' value="6d">6 Days</option>
				<option'.(($_REQUEST['phaseLengthMax']=='7d') ? ' selected="selected"' : '').' value="7d">7 Days</option>
				<option'.(($_REQUEST['phaseLengthMax']=='All') ? ' selected="selected"' : '').' value="All">10 Days</option>
			</select></p>
			<p>Reliability Rating From <select class="gameCreate" name="rrMin">
				<option'.(($_REQUEST['rrMin']=='All') ? ' selected="selected"' : '').' value="All">0%</option>
				<option'.(($_REQUEST['rrMin']=='10') ? ' selected="selected"' : '').' value="10">10%</option>
				<option'.(($_REQUEST['rrMin']=='20') ? ' selected="selected"' : '').' value="20">20%</option>
				<option'.(($_REQUEST['rrMin']=='30') ? ' selected="selected"' : '').' value="30">30%</option>
				<option'.(($_REQUEST['rrMin']=='40') ? ' selected="selected"' : '').' value="40">40%</option>
				<option'.(($_REQUEST['rrMin']=='50') ? ' selected="selected"' : '').' value="50">50%</option>
				<option'.(($_REQUEST['rrMin']=='60') ? ' selected="selected"' : '').' value="60">60%</option>
				<option'.(($_REQUEST['rrMin']=='70') ? ' selected="selected"' : '').' value="70">70%</option>
				<option'.(($_REQUEST['rrMin']=='80') ? ' selected="selected"' : '').' value="80">80%</option>
				<option'.(($_REQUEST['rrMin']=='90') ? ' selected="selected"' : '').' value="90">90%</option>
				<option'.(($_REQUEST['rrMin']=='100') ? ' selected="selected"' : '').' value="100">100%</option>
			</select>
			To<select class="gameCreate" name="rrMax">
				<option'.(($_REQUEST['rrMax']=='0') ? ' selected="selected"' : '').' value="0">0%</option>
				<option'.(($_REQUEST['rrMax']=='10') ? ' selected="selected"' : '').' value="10">10%</option>
				<option'.(($_REQUEST['rrMax']=='20') ? ' selected="selected"' : '').' value="20">20%</option>
				<option'.(($_REQUEST['rrMax']=='30') ? ' selected="selected"' : '').' value="30">30%</option>
				<option'.(($_REQUEST['rrMax']=='40') ? ' selected="selected"' : '').' value="40">40%</option>
				<option'.(($_REQUEST['rrMax']=='50') ? ' selected="selected"' : '').' value="50">50%</option>
				<option'.(($_REQUEST['rrMax']=='60') ? ' selected="selected"' : '').' value="60">60%</option>
				<option'.(($_REQUEST['rrMax']=='70') ? ' selected="selected"' : '').' value="70">70%</option>
				<option'.(($_REQUEST['rrMax']=='80') ? ' selected="selected"' : '').' value="80">80%</option>
				<option'.(($_REQUEST['rrMax']=='90') ? ' selected="selected"' : '').' value="90">90%</option>
				<option'.(($_REQUEST['rrMax']=='All') ? ' selected="selected"' : '').' value="All">100%</option>
			</select></p>
			<p>Bet Size From <input type="number" class="gameCreate" name="betMin" onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="'.((int)$_REQUEST['betMin'] ? (int)$_REQUEST['betMin'] : " ").'"/>
			To<input type="number" class="gameCreate" name="betMax" onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="'.((int)$_REQUEST['betMax'] ? (int)$_REQUEST['betMax'] : " ").'" /></p>
			<p>Messaging Type: <input type="checkbox" class="gameCreate" name="messageNorm" value="Yes"'.((isset($_REQUEST['Submit']) && $_REQUEST['messageNorm'] <> "Yes") ? '' : ' checked').'>Regular
			<input type="checkbox" class="gameCreate" name="messagePub" value="Yes"'.((isset($_REQUEST['Submit']) && $_REQUEST['messagePub'] <> "Yes") ? '' : ' checked').'>Public Only
			<input type="checkbox" class="gameCreate" name="messageNon" value="Yes"'.((isset($_REQUEST['Submit']) && $_REQUEST['messageNon'] <> "Yes") ? '' : ' checked').'>No Messaging
			<input type="checkbox" class="gameCreate" name="messageRule" value="Yes"'.((isset($_REQUEST['Submit']) && $_REQUEST['messageRule'] <> "Yes") ? '' : ' checked').'>Rulebook
			</p>
			<p>Sort By: <select  class = "gameCreate" name="sortCol">
				<option'.(($sortCol=='id') ? ' selected="selected"' : '').' value="id">Game ID</option>
				<option'.(($sortCol=='name') ? ' selected="selected"' : '').' value="name">Game Name</option>
				<option'.(($sortCol=='pot') ? ' selected="selected"' : '').' value="pot">Pot Size</option>
				<option'.(($sortCol=='minimumBet') ? ' selected="selected"' : '').' value="minimumBet">Bet</option>
				<option'.(($sortCol=='phaseMinutes') ? ' selected="selected"' : '').' value="phaseMinutes">Phase Length</option>
				<option'.(($sortCol=='minimumReliabilityRating') ? ' selected="selected"' : '').' value="minimumReliabilityRating">Reliability Rating</option>
				<option'.(($sortCol=='watchedGames') ? ' selected="selected"' : '').' value="watchedGames">Spectator Count</option>
				<option'.(($sortCol=='turn') ? ' selected="selected"' : '').' value="turn">Game Turn</option>
				<option'.(($sortCol=='processTime') ? ' selected="selected"' : '').' value="processTime">Time to Next Phase</option>
			</select></p>
			<p><select class = "gameCreate" name="sortType">
				<option'.(($sortType=='asc') ? ' selected="selected"' : '').' value="asc">Ascending</option>
				<option'.(($sortType=='desc') ? ' selected="selected"' : '').' value="desc">Descending</option>
			</select></p></strong>
			<input type="submit" name="Submit" class="green-Submit" value="Search" /></form></div>
			</br>';
	$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g";
	$SQLCounter = "SELECT COUNT(1) FROM wD_Games g";
	if($_REQUEST['userGames'] == 'include')
	{
		$SQL .= " INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ". $User->id;
		$SQLCounter .= " INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ". $User->id;
	}
	elseif(isset($_REQUEST['userID']))
	{
		$SQL .= " INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ". $UserProfile->id;
		$SQLCounter .= " INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ". $UserProfile->id;
		if($User->id != $UserProfile->id && !$User->type['Moderator'])
		{
			$SQL .= " AND (g.anon = 'No' OR g.phase = 'Finished')";
			$SQLCounter .= " AND (g.anon = 'No' OR g.phase = 'Finished')";
		}
	}
	elseif(isset($_REQUEST['tournamentID']))
	{
		$SQL .= " INNER JOIN wD_TournamentGames t ON t.gameID = g.id WHERE t.tournamentID = ". $tournamentID;
		$SQLCounter .= " INNER JOIN wD_TournamentGames t ON t.gameID = g.id WHERE t.tournamentID = ". $tournamentID;
		if ((int)$_REQUEST['round'] <> 0)
		{
			$SQL .= " AND t.round = ".(int)$_REQUEST['round'];
			$SQLCounter .= " AND t.round = ".(int)$_REQUEST['round'];
		}
	}
	else
	{
		$SQL .= " WHERE 1=1";
		$SQLCounter .= " WHERE 1=1";
	}
	if(isset($_REQUEST['status']))
	{
		if($_REQUEST['status'] == 'Pre-game')
		{
			$SQL .= " AND g.phase = 'Pre-game'";
			$SQLCounter .= " AND g.phase = 'Pre-game'";
		}
		elseif($_REQUEST['status'] == 'Active')
		{
			$SQL .= " AND g.phase <> 'Pre-game' AND g.phase <> 'Finished'";
			$SQLCounter .= " AND g.phase <> 'Pre-game' AND g.phase <> 'Finished'";
		}
		elseif($_REQUEST['status'] == 'Paused')
		{
			$SQL .= " AND g.phase <> 'Pre-game' AND g.phase <> 'Finished' AND g.processStatus = 'Paused'";
			$SQLCounter .= " AND g.phase <> 'Pre-game' AND g.phase <> 'Finished' AND g.processStatus = 'Paused'";
		}
		elseif($_REQUEST['status'] == 'Running')
		{
			$SQL .= " AND g.phase <> 'Pre-game' AND g.phase <> 'Finished' AND g.processStatus <> 'Paused'";
			$SQLCounter .= " AND g.phase <> 'Pre-game' AND g.phase <> 'Finished' AND g.processStatus <> 'Paused'";
		}
		elseif($_REQUEST['status'] == 'Finished')
		{
			$SQL .= " AND g.phase = 'Finished'";
			$SQLCounter .= " AND g.phase = 'Finished'";
		}
		elseif($_REQUEST['status'] == 'Won')
		{
			$SQL .= " AND g.phase = 'Finished' AND g.gameOver = 'Won'";
			$SQLCounter .= " AND g.phase = 'Finished' AND g.gameOver = 'Won'";
		}
		elseif($_REQUEST['status'] == 'Drawn')
		{
			$SQL .= " AND g.phase = 'Finished' AND g.gameOver = 'Drawn'";
			$SQLCounter .= " AND g.phase = 'Finished' AND g.gameOver = 'Drawn'";
		}
	}
	if(isset($_REQUEST['seeJoinable']))
	{
		if($_REQUEST['seeJoinable'] == 'yes')
		{
			$SQL .= " AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")
				AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
			$SQLCounter .= " AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")
				AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
		}
		elseif ($_REQUEST['seeJoinable'] == 'active')
		{
			$SQL .= " AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.phase <> 'Pre-game' AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")
				AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
			$SQLCounter .= " AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.phase <> 'Pre-game' AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")
				AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
		}
		elseif ($_REQUEST['seeJoinable'] == 'new')
		{
			$SQL .= " AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.phase = 'Pre-game' AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")
				AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
			$SQLCounter .= " AND g.minimumBet IS NOT NULL AND g.password IS NULL AND g.gameOver = 'No' AND g.phase = 'Pre-game' AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")
				AND ".$User->points." >= g.minimumBet AND ".$User->reliabilityRating." >= g.minimumReliabilityRating".($User->userIsTempBanned() ? " AND 0=1" : " ");
		}
	}
	if(isset($_REQUEST['privacy']))
	{
		if($_REQUEST['privacy'] == 'private')
		{
			$SQL .= " AND g.password IS NOT NULL";
			$SQLCounter .= " AND g.password IS NOT NULL";
		}
		elseif($_REQUEST['privacy'] == 'public')
		{
			$SQL .= " AND g.password IS NULL";
			$SQLCounter .= " AND g.password IS NULL";
		}
	}
	if(isset($_REQUEST['potType']))
	{
		if($_REQUEST['potType'] == 'dss')
		{
			$SQL .= " AND g.potType = 'Winner-takes-all'";
			$SQLCounter .= " AND g.potType = 'Winner-takes-all'";
		}
		elseif($_REQUEST['potType'] == 'sos')
		{
			$SQL .= " AND g.potType = 'Sum-of-squares'";
			$SQLCounter .= " AND g.potType = 'Sum-of-squares'";
		}
		elseif($_REQUEST['potType'] == 'ppsc')
		{
			$SQL .= " AND g.potType = 'Points-per-supply-center'";
			$SQLCounter .= " AND g.potType = 'Points-per-supply-center'";
		}
		elseif($_REQUEST['potType'] == 'unranked')
		{
			$SQL .= " AND g.potType = 'Unranked'";
			$SQLCounter .= " AND g.potType = 'Unranked'";
		}
	}
	if(isset($_REQUEST['drawVotes']))
	{
		if($_REQUEST['drawVotes'] == 'hidden')
		{
			$SQL .= " AND g.drawType = 'draw-votes-hidden'";
			$SQLCounter .= " AND g.drawType = 'draw-votes-hidden'";
		}
		elseif($_REQUEST['drawVotes'] == 'public')
		{
			$SQL .= " AND g.drawType = 'draw-votes-public'";
			$SQLCounter .= " AND g.drawType = 'draw-votes-public'";
		}
	}
	if(isset($_REQUEST['variant']))
	{
		$variantName = $_REQUEST['variant'];
		if($variantName != 'All')
		{
			$variantID = array_search($variantName, Config::$variants);
			$SQL .= " AND g.variantID = ".$variantID;
			$SQLCounter .= " AND g.variantID = ".$variantID;
		}
	}
	if(isset($_REQUEST['excusedTurns']))
	{
		if($_REQUEST['excusedTurns'] == '0')
		{
			$SQL .= " AND g.excusedMissedTurns = '0'";
			$SQLCounter .= " AND g.excusedMissedTurns = '0'";
		}
		elseif($_REQUEST['excusedTurns'] == '1')
		{
			$SQL .= " AND g.excusedMissedTurns = '1'";
			$SQLCounter .= " AND g.excusedMissedTurns = '1'";
		}
		elseif($_REQUEST['excusedTurns'] == '2')
		{
			$SQL .= " AND g.excusedMissedTurns = '2'";
			$SQLCounter .= " AND g.excusedMissedTurns = '2'";
		}
		elseif($_REQUEST['excusedTurns'] == '3')
		{
			$SQL .= " AND g.excusedMissedTurns = '3'";
			$SQLCounter .= " AND g.excusedMissedTurns = '3'";
		}
		elseif($_REQUEST['excusedTurns'] == '4')
		{
			$SQL .= " AND g.excusedMissedTurns = '4'";
			$SQLCounter .= " AND g.excusedMissedTurns = '4'";
		}
	}
	if(isset($_REQUEST['anonymity']))
	{
		if($_REQUEST['anonymity'] == 'yes')
		{
			$SQL .= " AND g.anon = 'Yes'";
			$SQLCounter .= " AND g.anon = 'Yes'";
		}
		elseif($_REQUEST['anonymity'] == 'no')
		{
			$SQL .= " AND g.anon = 'No'";
			$SQLCounter .= " AND g.anon = 'No'";
		}
	}
	if(isset($_REQUEST['phaseLengthMin']))
	{
		if($_REQUEST['phaseLengthMin'] == '7m')
		{
			$SQL .= " AND g.phaseMinutes >= 7";
			$SQLCounter .= " AND g.phaseMinutes >= 7";
		}
		elseif($_REQUEST['phaseLengthMin'] == '10m')
		{
			$SQL .= " AND g.phaseMinutes >= 10";
			$SQLCounter .= " AND g.phaseMinutes >= 10";
		}
		elseif($_REQUEST['phaseLengthMin'] == '15m')
		{
			$SQL .= " AND g.phaseMinutes >= 15";
			$SQLCounter .= " AND g.phaseMinutes >= 15";
		}
		elseif($_REQUEST['phaseLengthMin'] == '20m')
		{
			$SQL .= " AND g.phaseMinutes >= 20";
			$SQLCounter .= " AND g.phaseMinutes >= 20";
		}
		elseif($_REQUEST['phaseLengthMin'] == '30m')
		{
			$SQL .= " AND g.phaseMinutes >= 30";
			$SQLCounter .= " AND g.phaseMinutes >= 30";
		}
		elseif($_REQUEST['phaseLengthMin'] == '1h')
		{
			$SQL .= " AND g.phaseMinutes >= 60";
			$SQLCounter .= " AND g.phaseMinutes >= 60";
		}
		elseif($_REQUEST['phaseLengthMin'] == '2h')
		{
			$SQL .= " AND g.phaseMinutes >= 120";
			$SQLCounter .= " AND g.phaseMinutes >= 120";
		}
		elseif($_REQUEST['phaseLengthMin'] == '4h')
		{
			$SQL .= " AND g.phaseMinutes >= 240";
			$SQLCounter .= " AND g.phaseMinutes >= 240";
		}
		elseif($_REQUEST['phaseLengthMin'] == '6h')
		{
			$SQL .= " AND g.phaseMinutes >= 360";
			$SQLCounter .= " AND g.phaseMinutes >= 360";
		}
		elseif($_REQUEST['phaseLengthMin'] == '8h')
		{
			$SQL .= " AND g.phaseMinutes >= 480";
			$SQLCounter .= " AND g.phaseMinutes >= 480";
		}
		elseif($_REQUEST['phaseLengthMin'] == '10h')
		{
			$SQL .= " AND g.phaseMinutes >= 600";
			$SQLCounter .= " AND g.phaseMinutes >= 600";
		}
		elseif($_REQUEST['phaseLengthMin'] == '12h')
		{
			$SQL .= " AND g.phaseMinutes >= 720";
			$SQLCounter .= " AND g.phaseMinutes >= 720";
		}
		elseif($_REQUEST['phaseLengthMin'] == '14h')
		{
			$SQL .= " AND g.phaseMinutes >= 840";
			$SQLCounter .= " AND g.phaseMinutes >= 840";
		}
		elseif($_REQUEST['phaseLengthMin'] == '16h')
		{
			$SQL .= " AND g.phaseMinutes >= 960";
			$SQLCounter .= " AND g.phaseMinutes >= 960";
		}
		elseif($_REQUEST['phaseLengthMin'] == '18h')
		{
			$SQL .= " AND g.phaseMinutes >= 1080";
			$SQLCounter .= " AND g.phaseMinutes >= 1080";
		}
		elseif($_REQUEST['phaseLengthMin'] == '20h')
		{
			$SQL .= " AND g.phaseMinutes >= 1200";
			$SQLCounter .= " AND g.phaseMinutes >= 1200";
		}
		elseif($_REQUEST['phaseLengthMin'] == '22h')
		{
			$SQL .= " AND g.phaseMinutes >= 1320";
			$SQLCounter .= " AND g.phaseMinutes >= 1320";
		}
		elseif($_REQUEST['phaseLengthMin'] == '1d')
		{
			$SQL .= " AND g.phaseMinutes >= 1440";
			$SQLCounter .= " AND g.phaseMinutes >= 1440";
		}
		elseif($_REQUEST['phaseLengthMin'] == '1d1h')
		{
			$SQL .= " AND g.phaseMinutes >= 1500";
			$SQLCounter .= " AND g.phaseMinutes >= 1500";
		}
		elseif($_REQUEST['phaseLengthMin'] == '1d12h')
		{
			$SQL .= " AND g.phaseMinutes >= 2160";
			$SQLCounter .= " AND g.phaseMinutes >= 2160";
		}
		elseif($_REQUEST['phaseLengthMin'] == '2d')
		{
			$SQL .= " AND g.phaseMinutes >= 2880";
			$SQLCounter .= " AND g.phaseMinutes >= 2880";
		}
		elseif($_REQUEST['phaseLengthMin'] == '2d2h')
		{
			$SQL .= " AND g.phaseMinutes >= 3000";
			$SQLCounter .= " AND g.phaseMinutes >= 3000";
		}
		elseif($_REQUEST['phaseLengthMin'] == '3d')
		{
			$SQL .= " AND g.phaseMinutes >= 4320";
			$SQLCounter .= " AND g.phaseMinutes >= 4320";
		}
		elseif($_REQUEST['phaseLengthMin'] == '4d')
		{
			$SQL .= " AND g.phaseMinutes >= 5760";
			$SQLCounter .= " AND g.phaseMinutes >= 5760";
		}
		elseif($_REQUEST['phaseLengthMin'] == '5d')
		{
			$SQL .= " AND g.phaseMinutes >= 7200";
			$SQLCounter .= " AND g.phaseMinutes >= 7200";
		}
		elseif($_REQUEST['phaseLengthMin'] == '6d')
		{
			$SQL .= " AND g.phaseMinutes >= 8640";
			$SQLCounter .= " AND g.phaseMinutes >= 8640";
		}
		elseif($_REQUEST['phaseLengthMin'] == '7d')
		{
			$SQL .= " AND g.phaseMinutes >= 10080";
			$SQLCounter .= " AND g.phaseMinutes >= 10080";
		}
		elseif($_REQUEST['phaseLengthMin'] == '10d')
		{
			$SQL .= " AND g.phaseMinutes >= 14400";
			$SQLCounter .= " AND g.phaseMinutes >= 14400";
		}
	}
	if(isset($_REQUEST['phaseLengthMax']))
	{
		if($_REQUEST['phaseLengthMax'] == '5m')
		{
			$SQL .= " AND g.phaseMinutes <= 5";
			$SQLCounter .= " AND g.phaseMinutes <= 5";
		}
		elseif($_REQUEST['phaseLengthMax'] == '7m')
		{
			$SQL .= " AND g.phaseMinutes <= 7";
			$SQLCounter .= " AND g.phaseMinutes <= 7";
		}
		elseif($_REQUEST['phaseLengthMax'] == '10m')
		{
			$SQL .= " AND g.phaseMinutes <= 10";
			$SQLCounter .= " AND g.phaseMinutes <= 10";
		}
		elseif($_REQUEST['phaseLengthMax'] == '15m')
		{
			$SQL .= " AND g.phaseMinutes <= 15";
			$SQLCounter .= " AND g.phaseMinutes <= 15";
		}
		elseif($_REQUEST['phaseLengthMax'] == '20m')
		{
			$SQL .= " AND g.phaseMinutes <= 20";
			$SQLCounter .= " AND g.phaseMinutes <= 20";
		}
		elseif($_REQUEST['phaseLengthMax'] == '30m')
		{
			$SQL .= " AND g.phaseMinutes <= 30";
			$SQLCounter .= " AND g.phaseMinutes <= 30";
		}
		elseif($_REQUEST['phaseLengthMax'] == '1h')
		{
			$SQL .= " AND g.phaseMinutes <= 60";
			$SQLCounter .= " AND g.phaseMinutes <= 60";
		}
		elseif($_REQUEST['phaseLengthMax'] == '2h')
		{
			$SQL .= " AND g.phaseMinutes <= 120";
			$SQLCounter .= " AND g.phaseMinutes <= 120";
		}
		elseif($_REQUEST['phaseLengthMax'] == '4h')
		{
			$SQL .= " AND g.phaseMinutes <= 240";
			$SQLCounter .= " AND g.phaseMinutes <= 240";
		}
		elseif($_REQUEST['phaseLengthMax'] == '6h')
		{
			$SQL .= " AND g.phaseMinutes <= 360";
			$SQLCounter .= " AND g.phaseMinutes <= 360";
		}
		elseif($_REQUEST['phaseLengthMax'] == '8h')
		{
			$SQL .= " AND g.phaseMinutes <= 480";
			$SQLCounter .= " AND g.phaseMinutes <= 480";
		}
		elseif($_REQUEST['phaseLengthMax'] == '10h')
		{
			$SQL .= " AND g.phaseMinutes <= 600";
			$SQLCounter .= " AND g.phaseMinutes <= 600";
		}
		elseif($_REQUEST['phaseLengthMax'] == '12h')
		{
			$SQL .= " AND g.phaseMinutes <= 720";
			$SQLCounter .= " AND g.phaseMinutes <= 720";
		}
		elseif($_REQUEST['phaseLengthMax'] == '14h')
		{
			$SQL .= " AND g.phaseMinutes <= 840";
			$SQLCounter .= " AND g.phaseMinutes <= 840";
		}
		elseif($_REQUEST['phaseLengthMax'] == '16h')
		{
			$SQL .= " AND g.phaseMinutes <= 960";
			$SQLCounter .= " AND g.phaseMinutes <= 960";
		}
		elseif($_REQUEST['phaseLengthMax'] == '18h')
		{
			$SQL .= " AND g.phaseMinutes <= 1080";
			$SQLCounter .= " AND g.phaseMinutes <= 1080";
		}
		elseif($_REQUEST['phaseLengthMax'] == '20h')
		{
			$SQL .= " AND g.phaseMinutes <= 1200";
			$SQLCounter .= " AND g.phaseMinutes <= 1200";
		}
		elseif($_REQUEST['phaseLengthMax'] == '22h')
		{
			$SQL .= " AND g.phaseMinutes <= 1320";
			$SQLCounter .= " AND g.phaseMinutes <= 1320";
		}
		elseif($_REQUEST['phaseLengthMax'] == '1d')
		{
			$SQL .= " AND g.phaseMinutes <= 1440";
			$SQLCounter .= " AND g.phaseMinutes <= 1440";
		}
		elseif($_REQUEST['phaseLengthMax'] == '1d1h')
		{
			$SQL .= " AND g.phaseMinutes <= 1500";
			$SQLCounter .= " AND g.phaseMinutes <= 1500";
		}
		elseif($_REQUEST['phaseLengthMax'] == '1d12h')
		{
			$SQL .= " AND g.phaseMinutes <= 2160";
			$SQLCounter .= " AND g.phaseMinutes <= 2160";
		}
		elseif($_REQUEST['phaseLengthMax'] == '2d')
		{
			$SQL .= " AND g.phaseMinutes <= 2880";
			$SQLCounter .= " AND g.phaseMinutes <= 2880";
		}
		elseif($_REQUEST['phaseLengthMax'] == '2d2h')
		{
			$SQL .= " AND g.phaseMinutes <= 3000";
			$SQLCounter .= " AND g.phaseMinutes <= 3000";
		}
		elseif($_REQUEST['phaseLengthMax'] == '3d')
		{
			$SQL .= " AND g.phaseMinutes <= 4320";
			$SQLCounter .= " AND g.phaseMinutes <= 4320";
		}
		elseif($_REQUEST['phaseLengthMax'] == '4d')
		{
			$SQL .= " AND g.phaseMinutes <= 5760";
			$SQLCounter .= " AND g.phaseMinutes <= 5760";
		}
		elseif($_REQUEST['phaseLengthMax'] == '5d')
		{
			$SQL .= " AND g.phaseMinutes <= 7200";
			$SQLCounter .= " AND g.phaseMinutes <= 7200";
		}
		elseif($_REQUEST['phaseLengthMax'] == '6d')
		{
			$SQL .= " AND g.phaseMinutes <= 8640";
			$SQLCounter .= " AND g.phaseMinutes <= 8640";
		}
		elseif($_REQUEST['phaseLengthMax'] == '7d')
		{
			$SQL .= " AND g.phaseMinutes <= 10080";
			$SQLCounter .= " AND g.phaseMinutes <= 10080";
		}
	}
	if(isset($_REQUEST['rrMin']))
	{
		if($_REQUEST['rrMin'] == '10')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 10";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 10";
		}
		elseif($_REQUEST['rrMin'] == '20')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 20";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 20";
		}
		elseif($_REQUEST['rrMin'] == '30')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 30";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 30";
		}
		elseif($_REQUEST['rrMin'] == '40')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 40";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 40";
		}
		elseif($_REQUEST['rrMin'] == '50')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 50";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 50";
		}
		elseif($_REQUEST['rrMin'] == '60')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 60";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 60";
		}
		elseif($_REQUEST['rrMin'] == '70')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 70";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 70";
		}
		elseif($_REQUEST['rrMin'] == '80')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 80";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 80";
		}
		elseif($_REQUEST['rrMin'] == '90')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 90";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 90";
		}
		elseif($_REQUEST['rrMin'] == '100')
		{
			$SQL .= " AND g.minimumReliabilityRating >= 100";
			$SQLCounter .= " AND g.minimumReliabilityRating >= 100";
		}
	}
	if(isset($_REQUEST['rrMax']))
	{
		if ($_REQUEST['rrMax'] == '0')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 0";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 0";
		}
		elseif($_REQUEST['rrMax'] == '10')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 10";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 10";
		}
		elseif($_REQUEST['rrMax'] == '20')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 20";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 20";
		}
		elseif($_REQUEST['rrMax'] == '30')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 30";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 30";
		}
		elseif($_REQUEST['rrMax'] == '40')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 40";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 40";
		}
		elseif($_REQUEST['rrMax'] == '50')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 50";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 50";
		}
		elseif($_REQUEST['rrMax'] == '60')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 60";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 60";
		}
		elseif($_REQUEST['rrMax'] == '70')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 70";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 70";
		}
		elseif($_REQUEST['rrMax'] == '80')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 80";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 80";
		}
		elseif($_REQUEST['rrMax'] == '90')
		{
			$SQL .= " AND g.minimumReliabilityRating <= 90";
			$SQLCounter .= " AND g.minimumReliabilityRating <= 90";
		}
	}
	if (isset($_REQUEST['betMin']))
	{
		if((int)$_REQUEST['betMin'] <> 0)
		{
			$SQL .= " AND (SELECT m2.bet FROM wD_Members m2 WHERE m2.gameID = g.id AND m2.bet > 0 LIMIT 1) >= ". (int)$_REQUEST['betMin'];
			$SQLCounter .= " AND (SELECT m2.bet FROM wD_Members m2 WHERE m2.gameID = g.id AND m2.bet > 0 LIMIT 1) >= ". (int)$_REQUEST['betMin'];
		}
	}
	if (isset($_REQUEST['betMax']))
	{
		if((int)$_REQUEST['betMax'] <> 0)
		{
			$SQL .= " AND (SELECT m3.bet FROM wD_Members m3 WHERE m3.gameID = g.id AND m3.bet > 0 LIMIT 1) <= ". (int)$_REQUEST['betMax'];
			$SQLCounter .= " AND (SELECT m3.bet FROM wD_Members m3 WHERE m3.gameID = g.id AND m3.bet > 0 LIMIT 1) <= ". (int)$_REQUEST['betMax'];
		}
	}
	if ($_REQUEST['messageNorm'] <> 'All' || $_REQUEST['messagePub'] <> 'All' || $_REQUEST['messageNon'] <> 'All' || $_REQUEST['messageRule'] <> 'All')
	{
		if(isset($_REQUEST['messageNorm']))
		{
			if($_REQUEST['messageNorm'] <> 'Yes')
			{
				$SQL .= " AND g.pressType <> 'Regular'";
				$SQLCounter .= " AND g.pressType <> 'Regular'";
			}
		}
		if(isset($_REQUEST['messagePub']))
		{
			if($_REQUEST['messagePub'] <> 'Yes')
			{
				$SQL .= " AND g.pressType <> 'PublicPressOnly'";
				$SQLCounter .= " AND g.pressType <> 'PublicPressOnly'";
			}
		}
		if(isset($_REQUEST['messageNon']))
		{
			if($_REQUEST['messageNon'] <> 'Yes')
			{
				$SQL .= " AND g.pressType <> 'NoPress'";
				$SQLCounter .= " AND g.pressType <> 'NoPress'";
			}
		}
		if(isset($_REQUEST['messageRule']))
		{
			if($_REQUEST['messageRule'] <> 'Yes')
			{
				$SQL .= " AND g.pressType <> 'RulebookPress'";
				$SQLCounter .= " AND g.pressType <> 'RulebookPress'";
			}
		}
	}
}

if($User->type['User'] && $tab <> 'My games' && $tab <> 'Search' && $tab <> 'Finished' && $tab <> 'Active')
{
	$SQL = $SQL . " AND g.id NOT IN (SELECT m1.gameID FROM wD_Members m1 WHERE m1.userID = ". $User->id .")";
}

$SQL = $SQL . " ORDER BY ";
if ($sortCol <> 'watchedGames' && $sortCol <> 'processTime' && $sortCol <> 'minimumBet') {$SQL .= "g.";}
$ordering = $sortCol;
if ($sortCol == 'processTime') {$ordering = "(CASE WHEN g.processStatus = 'Paused' THEN (g.pauseTimeRemaining + ".time().") ELSE g.processTime END)";}
elseif ($sortCol == 'minimumBet') {$ordering = "(SELECT m4.bet FROM wD_Members m4 WHERE m4.gameID = g.id AND m4.bet > 0 LIMIT 1)";}
$SQL = $SQL . $ordering." ".$sortType." ";
$SQL = $SQL . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";

if ($tab <> 'Search')
{
	$tabl = $DB->sql_tabl($SQL);
}
elseif (isset($_REQUEST['Submit']))
{
	$tabl = $DB->sql_tabl($SQL);
	list($totalResults) = $DB->sql_row($SQLCounter);
	print "<a name='results'></a>";
}

$maxPage = ceil($totalResults / $resultsPerPage);

if ($totalResults == 0)
{
	print '<b>';
	switch($tab)
	{
		case 'My games':
			print l_t('You are not in any active games, '.
				'<a href="gamelistings.php?gamelistType=New">join a new game</a> '.
				'or <a href="gamecreate.php">create your own new game.</a>');
			break;
		case 'New':
			print l_t('There are currently no new games.').' ';
			if($User->type['User'])
				print l_t('<a href="gamecreate.php">Create your own new game.</a>');
			break;
		case 'Open Positions':
			print l_t('There are currently no active games with open slots.');
			break;
		case 'Active':
			print l_t('There are currently no active games,').' ';
			if($User->type['User'])
				print l_t('<a href="gamelistings.php?gamelistType=New">join a new game</a> '.
				'or <a href="gamecreate.php">create your own new game.</a>');
			break;
		case 'Finished':
			print l_t('There are currently no finished games.');
			break;
		case 'Search':
			if(isset($_REQUEST['Submit'])) {print l_t('No results for the given search.');}
			break;
		default:
			print l_t('No games were found.');
	}
	print '</b>';
}
else
{
	print '<center><b> Showing results '.number_format(min(((($pagenum - 1) * $resultsPerPage)+1),$totalResults)).' to '.number_format(min(($pagenum * $resultsPerPage),$totalResults)).' of '.number_format($totalResults).' total results. </b></center></br>';
	printPageBar($pagenum, $maxPage, $sortCol, $sortType, $sortBar = True);

	print '<div class="gamesList">';
	while( $row = $DB->tabl_hash($tabl) )
	{
		$Variant = libVariant::loadFromVariantID($row['variantID']);
		$G = $Variant->panelGame($row);
		print $G->summary(false);
	}
	print '</div>';

	print '</br>';
	printPageBar($pagenum, $maxPage, $sortCol, $sortType);
}
print '</div>';

function printPageBar($pagenum, $maxPage, $sortCol, $sortType, $sortBar = False)
{
	if ($pagenum > 3)
	{
		printPageButton(1,False);
	}
	if ($pagenum > 4)
	{
		print "...";
	}
	if ($pagenum > 2)
	{
		printPageButton($pagenum-2, False);
	}
	if ($pagenum > 1)
	{
		printPageButton($pagenum-1, False);
	}
	if ($maxPage > 1)
	{
		printPageButton($pagenum, True);
	}
	if ($pagenum < $maxPage)
	{
		printPageButton($pagenum+1, False);
	}
	if ($pagenum < $maxPage-1)
	{
		printPageButton($pagenum+2, False);
	}
	if ($pagenum < $maxPage-3)
	{
		print "...";
	}
	if ($pagenum < $maxPage-2)
	{
		printPageButton($maxPage, False);
	}
	if ($maxPage > 1 && $sortBar)
	{
		print '<span style="float:right;">
			<FORM class="advancedSearch" method="get" action="gamelistings.php#results">
			<b>Sort By:</b>
			<select  class = "advancedSearch" name="sortCol">
				<option'.(($sortCol=='id') ? ' selected="selected"' : '').' value="id">Game ID</option>
				<option'.(($sortCol=='name') ? ' selected="selected"' : '').' value="name">Game Name</option>
				<option'.(($sortCol=='pot') ? ' selected="selected"' : '').' value="pot">Pot Size</option>
				<option'.(($sortCol=='minimumBet') ? ' selected="selected"' : '').' value="minimumBet">Bet</option>
				<option'.(($sortCol=='phaseMinutes') ? ' selected="selected"' : '').' value="phaseMinutes">Phase Length</option>
				<option'.(($sortCol=='minimumReliabilityRating') ? ' selected="selected"' : '').' value="minimumReliabilityRating">Reliability Rating</option>
				<option'.(($sortCol=='watchedGames') ? ' selected="selected"' : '').' value="watchedGames">Spectator Count</option>
				<option'.(($sortCol=='turn') ? ' selected="selected"' : '').' value="turn">Game Turn</option>
				<option'.(($sortCol=='processTime') ? ' selected="selected"' : '').' value="processTime">Time to Next Phase</option>
			</select>
			<select class = "advancedSearch" name="sortType">
				<option'.(($sortType=='asc') ? ' selected="selected"' : '').' value="asc">Ascending</option>
				<option'.(($sortType=='desc') ? ' selected="selected"' : '').' value="desc">Descending</option>
			</select>';
			foreach($_REQUEST as $key => $value)
			{
				if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3') == false && strpos('x'.$key,'__utm')== false && $key!="pagenum" && $key!="sortCol" && $key!="sortType")
				{
					print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
			}
			print ' ';
			print '<input type="submit" class="form-submit" name="Submit" value="Refresh" /></form>
			</span>';
		}
}

function printPageButton($pagenum, $currPage)
{
	if ($currPage)
	{
		print '<div class="curr-page">'.$pagenum.'</div>';
	}
	else
	{
		print '<div style="display:inline-block; margin:3px;">';
		print '<FORM method="get" action=gamelistings.php#results>';
		foreach($_REQUEST as $key => $value)
		{
			if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3')== false && strpos('x'.$key,'__utm')== false && $key!="pagenum")
			{
				print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
		}
		print '<input type="submit" name="pagenum" class="form-submit" value='.$pagenum.' /></form></div>';
	}
}

libHTML::$footerIncludes[] = l_j('help.js');
libHTML::footer();
?>
