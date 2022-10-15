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
 */
require_once('header.php');
require_once(l_r('lib/message.php'));
require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/gamehome.php'));

/*
 * A field
 *
 * add(field, index)
 * compare(field1, field2) -> 1 if aligned, 0 if not
 *
 */
libHTML::starthtml(l_t('Home'));

if( !isset($_SESSION['lastSeenHome']) || $_SESSION['lastSeenHome'] < $User->timeLastSessionEnded )
{
	$_SESSION['lastSeenHome']=$User->timeLastSessionEnded;
}

global $DB;
$gameToggleID = 0;

if(isset($_POST['submit']))
{
	if(isset($_POST['gameToggleName']))
	{
		$gameToggleID = (int)$_POST['gameToggleName'];
	}

	if ($User->type['User'] and $gameToggleID > 0)
	{
		libAuth::formToken_Valid();
		
		$noticesStatus = 5;
		list($noticesStatus) = $DB->sql_row("SELECT hideNotifications FROM wD_Members WHERE userID =".$User->id." and gameID =".$gameToggleID);

		if ($noticesStatus == 0)
		{
			$DB->sql_put("UPDATE wD_Members SET hideNotifications = 1 WHERE userID =".$User->id." and gameID =".$gameToggleID);
		}
		else if ($noticesStatus == 1)
		{
			$DB->sql_put("UPDATE wD_Members SET hideNotifications = 0 WHERE userID =".$User->id." and gameID =".$gameToggleID);
		}
	}
}

require_once('lib/home.php');

if( !$User->type['User'] )
{
	print '<div class = "introToDiplomacy"><div class="content-notice" style="text-align:center">'.libHome::globalInfo().'</div></div>';
	print libHTML::pageTitle(l_t('Welcome to webDiplomacy!'),l_t('A multiplayer web implementation of the popular turn-based strategy game Diplomacy.'));
	//print '<div class="content">';
	require_once(l_r('locales/English/welcome.php'));
	print '</div></div>';

	require_once(l_r('locales/English/intro.php'));
	print '</div>';
}
elseif( isset($_REQUEST['notices']) )
{
	$User->clearNotification('PrivateMessage');

	print '<div class="content"><a href="index.php" class="light">&lt; '.l_t('Back').'</a></div>';

	print '<div class="content-bare content-home-header">';
	print '<table class="homeTable"><tr>';

	notice::$noticesPage=true;
	if( !isset(Config::$customForumURL) ) 
	{
		print '<td class="homeNoticesPMs">';
		print '<div class="homeHeader">'.l_t('Private messages').'</a></div>';
		print libHome::NoticePMs();
		print '</td>';
		print '<td class="homeSplit"></td>';
	}
	print '<td class="homeNoticesGame">';
	print '<div class="homeHeader">'.l_t('Game messages').'</a></div>';
	print libHome::NoticeGame();
	print '</td>';

	print '</tr></table>';
	print '</div>';
	print '</div>';
}
else
{
	
	print '<div class="content-bare content-home-header">';// content-follow-on">';
	
	// home page tutorial
	if (isset($_COOKIE['wD-Tutorial-Index'])) 
	{
		$tutorialMessage = l_t(
			'To help you become familiar with webDiplomacy, we prepared a few brief presentations
			for you that you will find on different pages of the site. You will only see these once apiece, but if you
			ever want to review what you read, you can check out our <a href="faq.php" target="_blank">Frequently Asked Questions</a>. 
			If you are new to the game of Diplomacy, we also recommend our <a href="intro.php" target="_blank">Intro to Diplomacy</a> 
			and our <a href="rules.php" target="_blank">site rules</a>.
			<br><br>
			Even if you are familiar with the game of Diplomacy, webDiplomacy does some things a bit differently than other sites or
			face to face Diplomacy communities. We developed our own scoring system, the Ghost Ratings, which you can read about <a
			href="https://sites.google.com/view/webdipinfo/ghost-ratings" target="_blank">here</a>, in addition to points that you can
			use to cover the bet to enter games. We also developed a reliability rating to gauge how committed and reliable a player
			is to entering orders prior to deadlines. Whether you decide to be a casual Diplomacy player or a very dedicated player,
			it is important to enter orders prior to the deadlines so that the game is fair and balanced for everyone, and so that
			games are not delayed while players are replaced. Some games require a minimum reliability rating, so it is important to
			keep it high! You can read more about reliability rating <a href="userprofile.php?userID='.$User->id.'" 
			target="_blank">here</a>. 
			<br><br>
			You are currently on the home screen. This is the landing page that you will see when you log into the site. While you can
			access any Diplomacy games you are playing from any page on the site when you have orders to submit or a message to read, 
			you can see all of your games in one place here. When you join games, they will appear on the right side of the screen
			under "My Games." Since you are not in any games right now, it will be empty. You can also see games that you "spectate" here,
			which are games that you are not playing in but just want to watch, as well as tournaments that you join when you are more experienced.
			In the center of the page, you will see if a phase has changed in any of your games, a notice if you were defeated, drew with your opponents, 
			or won a game you are in, or miss a phase deadline and need to catch up. On the left side of the page, you can see upcoming live games. 
			Live games are games that finish quickly, with phase lengths ranging between 5 and 30 minutes. Below upcoming live games, you can see the 
			latest posts on our forum, a place you can go to ask questions, find high quality games with other players, read Diplomacy advice from 
			some of the best players in the world, join tournaments, and play some forum games. 
			<br><br>
			To reach out to the moderators for help with your account, submit a message to the  <a href="modforum.php" target="_blank">moderator forum</a>. 
			Thank you for joining the site, and have fun!'
		);
	
		libHTML::help('Home', $tutorialMessage);

		unset($_COOKIE['wD-Tutorial-Index']);
		setcookie('wD-Tutorial-Index', '', ['expires'=>time()-3600,'samesite'=>'Lax']);
	}

	print '<table class="homeTable"><tr>';

	print '<td class="homeMessages">';

	$liveGames = libHome::upcomingLiveGames();
	if ($liveGames != '') 
	{
		print '<div class="homeHeader">'.l_t('Joinable live games').' <a href="gamelistings.php?gamelistType=Search&phaseLengthMax=30m&messageNorm=Yes&messagePub=Yes&messageNon=Yes&messageRule=Yes&Submit=Search#results">'.libHTML::link().'</a></div>';
		print $liveGames;
	}

	if( isset(Config::$customForumURL) ) 
	{ 
		print '<div class="homeHeader">'.l_t('Forum').' <a href="/contrib/phpBB3/">'.libHTML::link().'</a></div>';
		if( file_exists(libCache::dirName('forum').'/home-forum.html') )
		{
			print file_get_contents(libCache::dirName('forum').'/home-forum.html');
			$diff = (time() - filemtime(libCache::dirName('forum').'/home-forum.html'));
			if( $diff > 60*5 ) 
			{
				unlink(libCache::dirName('forum').'/home-forum.html');
			}
		}
		else
		{
			$buf_home_forum=libHome::forumNewExtern();
			file_put_contents(libCache::dirName('forum').'/home-forum.html', $buf_home_forum);
			print $buf_home_forum;
		}
	}
	else 
	{ 
		print '<div class="homeHeader">'.l_t('Forum').' <a href="forum.php">'.libHTML::link().'</a></div>';
		if( file_exists(libCache::dirName('forum').'/home-forum.html') )
			print file_get_contents(libCache::dirName('forum').'/home-forum.html');
		else
		{
			$buf_home_forum=libHome::forumNew();
			file_put_contents(libCache::dirName('forum').'/home-forum.html', $buf_home_forum);
			print $buf_home_forum;
		}
	}
	print '</td>';

	print '<td class="homeSplit"></td>';

	print '<td class="homeGameNotices">';

	print '<div class="homeHeader">'.l_t('Notices').' <a href="index.php?notices=on">'.libHTML::link().'</a></div>';
	print libHome::Notice();
	print '</td>';

	print '<td class="homeSplit"></td>';

	print '<td class="homeGamesStats">';
	print '<div class="homeHeader">'.l_t('My games').' <a href="gamelistings.php?page=1&gamelistType=My games">'.libHTML::link().'</a></div>';
	print libHome::gameNotifyBlock();
	print '<div class="homeHeader">'.l_t('Defeated games').'</div>';
	print libHome::gameDefeatedNotifyBlock();
	print '<div class="homeHeader">'.l_t('Spectated games').'</div>';
	print libHome::gameWatchBlock();

	$sql = "select distinct t.id, t.name, t.status from wD_Tournaments t inner join wD_TournamentParticipants s on s.tournamentID = t.id 
	where t.status <> 'Finished' and ( s.userID =".$User->id." or t.directorID = ".$User->id." or t.coDirectorID = ".$User->id.")";
	$sqlCounter = "select count(distinct t.id) from wD_Tournaments t inner join wD_TournamentParticipants s on s.tournamentID = t.id 
	where t.status <> 'Finished' and ( s.userID =".$User->id." or t.directorID = ".$User->id." or t.coDirectorID = ".$User->id.")";

	$tablChecked = $DB->sql_tabl($sql);
	list($resultsParticipating) = $DB->sql_row($sqlCounter);

	if ($resultsParticipating > 0)
	{
		print '<div class="homeHeader">'.l_t('My Tournaments').' <a href="tournaments.php?tab=Participating">'.libHTML::link().'</a></div>';

		while (list($id, $name, $status) = $DB->tabl_row($tablChecked))
		{
			print '<div class = "gamePanelHome"> 
			<h3 class = "tournamentCenter">'.$name.'</h3><div class = "tournamentCenter">';
			if ($status != 'PreStart')
			{
				print '<a  class = "tournamentCenter" href="tournamentScoring.php?tournamentID='.$id.'">Scoring and Participants</a></br>';
				if($status != 'Registration')
				{
					print '</br><a class = "tournamentCenter" href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'">Tournament Games</a></br>';
				}
			}
			print'</br></div></div>';
		}
	}

	$sql = "select t.id, t.name, t.status from wD_Tournaments t inner join wD_TournamentSpectators s on s.tournamentID = t.id where t.status <> 'Finished' and s.userID =".$User->id;
	$sqlCounter = "select count(1) from wD_Tournaments t inner join wD_TournamentSpectators s on s.tournamentID = t.id where t.status <> 'Finished' and s.userID =".$User->id;

	$tablChecked = $DB->sql_tabl($sql);
	list($resultsSpectating) = $DB->sql_row($sqlCounter);

	if ($resultsSpectating > 0)
	{
		print '<div class="homeHeader">'.l_t('Spectated Tournaments').' <a href="tournaments.php?tab=Spectating">'.libHTML::link().'</a></div>';

		while (list($id, $name, $status) = $DB->tabl_row($tablChecked))
		{
			print '<div class = "gamePanelHome"> 
			<h3 class = "tournamentCenter">'.$name.'</h3><div class = "tournamentCenter">';
			if ($status != 'PreStart')
			{
				print '<a class = "tournamentCenter" href="tournamentScoring.php?tournamentID='.$id.'">Scoring and Participants</a></br>';
				if($status != 'Registration')
				{
					print '</br><a class = "tournamentCenter" href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'">Tournament Games</a></br>';
				}
			}
			print'</br></div></div>';
		}
	}

	print '</td>
	</tr></table>';

	print '</div>';
	print '</div>';
}

libHTML::$footerIncludes = [l_j('home.js'), l_j('help.js')];
libHTML::$footerScript[] = l_jf('homeGameHighlighter').'();';

$_SESSION['lastSeenHome']=time();

libHTML::footer();

?>
