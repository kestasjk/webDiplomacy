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

require_once(l_r('gamesearch/search.php'));
require_once(l_r('pager/pagergame.php'));
require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/game.php'));

libHTML::starthtml();

print '<div class="content">';

if ( isset($_REQUEST['find']) )
{
	libAuth::resourceLimiter('search games',5);

	if(isset($_REQUEST['find']['game']))
		$game = $DB->escape($_REQUEST['find']['game']);

	if(isset($_REQUEST['find']['member']))
		$member = $DB->escape($_REQUEST['find']['member']);

	if (isset($_REQUEST['find']['type']) && $_REQUEST['find']['type'] == 'id' )
	{
		$useID = true;
		$game = (int)$game;
		$member = (int)$member;
	}
	else
	{
		$useID = false;
	}

	$tabl = $DB->sql_tabl(
		'SELECT g.id, g.variantID FROM wD_Games g
			INNER JOIN wD_Members m ON ( m.gameID = g.id )
			INNER JOIN wD_Users u ON ( u.id = m.userID )
		WHERE '.($useID ? 'g.id = '.$game.' AND m.id = '.$member : 'g.name = "'.$game.'" AND u.username = "'.$member.'"' ));
	while(list($id, $variantID)=$DB->tabl_row($tabl))
	{
		$Variant=libVariant::loadFromVariantID($variantID);
		$Game = $Variant->Game($id);
		print $Game->summary();

	}
}

function printAndFindTab()
{
	global $User, $Misc, $DB;

	$tabs = array();

	if($User->type['User'])
		$tabs['My games']=l_t("Active games which you have joined");

	$tabs['New']=l_t("Games which haven't yet started");
	$tabs['Joinable']=l_t("Public games which have open spaces");
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

	print '<div class="gamelistings-tabsNew">';

	if($User->type['User'] )
	{
		list($GamesNew) = $DB->sql_row("SELECT COUNT(phase) FROM wD_Games WHERE phase = 'Pre-game'");
		list($GamesActive) = $DB->sql_row("SELECT COUNT(phase) FROM wD_Games WHERE phase = 'Diplomacy' OR phase = 'Retreats' OR phase = 'Builds'");
		list($GamesOpen) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games g WHERE g.minimumBet is not null and g.password is null and g.gameOver = 'No' 
		and g.phase in ('Diplomacy','Retreats','Builds')");
	}
	else
	{
		list($GamesNew) = $DB->sql_row("SELECT COUNT(phase) FROM wD_Games WHERE phase = 'Pre-game'");
		list($GamesActive) = $DB->sql_row("SELECT COUNT(phase) FROM wD_Games WHERE phase = 'Diplomacy' OR phase = 'Retreats' OR phase = 'Builds'");
		list($GamesOpen) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games g WHERE g.minimumBet is not null and g.password is null and g.gameOver = 'No' 
		and g.phase in ('Diplomacy','Retreats','Builds')");
	}

	foreach($tabs as $tabChoice=>$tabTitle)
	{
		print '<a title="'.$tabTitle.'" href="gamelistings.php?page-games=1&amp;gamelistType='.$tabChoice;

		if ( $tab == $tabChoice )
		{
			if ( !isset($_REQUEST['searchOn']) )
				print '&amp;searchOn=on';

			print '" class="gamelistings-tabsNewActive"';
		}
		else
			print '"';

		print '>'.l_t($tabChoice);

		switch($tabChoice)
		{
			case 'New':
			print ' ('.$GamesNew.')';
				if ( $tab == $tabChoice ) {
					print (isset($_REQUEST['searchOn']) ? ' [click for default]' : ' [click for search]');
				}

			break;
			case 'Joinable':
			print ' ('.$GamesOpen.')';
				if ( $tab == $tabChoice ) {
					print (isset($_REQUEST['searchOn']) ? ' [click for default]' : ' [click for search]');
				}

			break;
			case 'Active':
			print ' ('.$GamesActive.')';
				if ( $tab == $tabChoice ) {
					print (isset($_REQUEST['searchOn']) ? ' [click for default]' : ' [click for search]');
				}

			break;
			case 'Finished':
				if ( $tab == $tabChoice ) {
					print (isset($_REQUEST['searchOn']) ? ' [click for default]' : ' [click for search]');
				}
			break;
		}

		print '</a> ';
	}

	print '</div>';

	return $tab;
}

$tab = printAndFindTab();

$search = new search($tab);

if ( $tab != 'My games' && $tab != 'Search' )
	$Pager = new PagerGames('gamelistings.php', $Misc->{'Games'.$tab});
else
	$Pager = new PagerGames('gamelistings.php');

$Pager->addArgs('gamelistType='.$tab);

if ( $tab=='Search' or isset($_REQUEST['searchOn']) or isset($_REQUEST['search']) )
{
	$Pager->addArgs('searchOn=on');

	if ( isset($_REQUEST['search']) )
	{
		$_SESSION['search-gamelistings.php']=$_REQUEST['search'];
		$search->filterInput($_REQUEST['search']);
	}
	elseif( isset($_SESSION['search-gamelistings.php']) )
		$search->filterInput($_SESSION['search-gamelistings.php']);

	print '<div style="margin:30px">';
	print '<form action="gamelistings.php?gamelistType='.$tab.'&amp;page=1#top" method="post">';

	$search->formHTML();

	print '</form>';
	print '<p><a href="gamelistings.php?page=1" class="light">'.l_t('Close search').'</a></p>';
	print '</div>';

	libHTML::pagebreak();

	print $Pager->pagerBar('top', '<h4>'.l_t('Results:').'</h4>');

	$gameCount = $search->printGamesList($Pager);

	if( $gameCount == 0 )
	{
		print '<p class="notice">';

		if( $Pager->currentPage > 1 )
			print l_t('The set of returned games has finished; use the search tab to find specific games.');
		else
			print l_t('No games found for the given search parameters, try broadening your search.');

		print '</p>';
	}
}
else
{
	libHTML::pagebreak();

	print $Pager->pagerBar('top');

	$gameCount = $search->printGamesList($Pager);

	if( $gameCount == 0 )
	{
		print '<p class="notice">';

		if( $Pager->currentPage > 1 )
		{
			print l_t('The set of returned games has finished; use the search tab to find specific games.');
		}
		else
		{
			switch($tab)
			{
				case 'My games':
					print l_t('You are not in any active games, select the "New" tab '.
						'to view games that you can join, or if you can\'t find a game you want '.
						'to join select "New game" in the menu to create your own.');
					break;
				case 'New':
					print l_t('No new games on the server.').' ';
					if($User->type['User'])
						print l_t('Select "New game" in the menu to create your own.');
					break;
				case 'Joinable':
					print l_t('No active games with open slots on the server.');
					break;
				case 'Active':
					print l_t('No active games on the server.').' ';
					if($User->type['User'])
						print l_t('Click the "New" tab to look for new games, '.
							'or select "New game" in the menu to create your own.');
					break;
				case 'Finished':
					print l_t('No finished games on the server.');
					break;

				default:
					print l_t('No games were found.');
			}
		}

		print '</p>';
	}
}

if ( $gameCount > 1 )
	print $Pager->pagerBar('bottom','<a href="#top">'.l_t('Back to top').'</a>');
else
	print '<a name="bottom"></a>';

print '</div>';
libHTML::footer();

?>
