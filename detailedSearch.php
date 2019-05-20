<?php

/**
 * @package Base
 */

require_once('header.php');
require_once(l_r('gamesearch/search.php'));
require_once(l_r('pager/pagergame.php'));
require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/game.php'));

$tab = '';
global $DB;

// Ensure non-existant tables are not queried. phpbb forum tables exist only on webdip so their existence should be confirmed before use.
list($serverHasPHPBB) = $DB->sql_row("SELECT count(1) FROM information_schema.tables WHERE table_name = 'phpbb_users'");

if (isset($_REQUEST['tab']))
{
	if ($_REQUEST['tab'] == 'UserSearch') { $tab = 'UserSearch'; }
	else if ($_REQUEST['tab'] == 'GameSearch') { $tab = 'GameSearch'; }
	else if ($_REQUEST['tab'] == 'GamesByUser') { $tab = 'GamesByUser'; }
}

// User Search Objects
class UserResultData
{
	public $userID;
	public $username;
	public $email;
	public $timeJoined;
	public $gameCount;
	public $reliabilityRating;
	public $banned;
	public $mod;
	public $gold;
	public $silver;
	public $bronze;
	public $platinum;
	public $userType;
}

// User Search Variables
$UsersData = array();
$username = '';
$searchType1 = 'Starts';
$type = 'none';
$seeUsername = 'unchecked';
$seePoints = 'unchecked';
$seeJoined = 'unchecked';
$seeGameCount = 'unchecked';
$seeRR = 'unchecked';
$seeMod = 'unchecked';
$seeBanned = 'unchecked';
$seeGold = 'unchecked';
$seeSilver = 'unchecked';
$seeBronze = 'unchecked';
$seeAll = 'unchecked';
$seeNewForumLink = 'unchecked';
$sortCol = 'id';
$sortType = 'asc';

// Use db escape to guard against special characters.
if ( isset($_REQUEST['username']) && $_REQUEST['username'] && strlen($_REQUEST['username']) )
{
	$username = $DB->escape($_REQUEST['username']);
}

if ( isset($_REQUEST['searchType1']))
{
	if ($_REQUEST['searchType1'] == 'Contains') { $searchType1='Contains'; }
	else if ($_REQUEST['searchType1'] == 'Ends') { $searchType1='Ends'; }
}

if ( isset($_REQUEST['seeUsername'])) { $seeUsername='checked'; }
if ( isset($_REQUEST['seePoints'])) { $seePoints='checked'; }
if ( isset($_REQUEST['seeJoined'])) { $seeJoined='checked'; }
if ( isset($_REQUEST['seeGameCount'])) { $seeGameCount='checked'; }
if ( isset($_REQUEST['seeRR'])) { $seeRR='checked'; }
if ( isset($_REQUEST['seeMod'])) { $seeMod='checked'; }
if ( isset($_REQUEST['seeBanned'])) { $seeBanned='checked'; }
if ( isset($_REQUEST['seeGold'])) { $seeGold='checked'; }
if ( isset($_REQUEST['seeSilver'])) { $seeSilver='checked'; }
if ( isset($_REQUEST['seeBronze'])) { $seeBronze='checked'; }
if ( isset($_REQUEST['sortCol']))
{
	if ($_REQUEST['sortCol'] == 'Username') { $sortCol='Username'; }
	else if ($_REQUEST['sortCol'] == 'timeJoined') { $sortCol='timeJoined'; }
	else if ($_REQUEST['sortCol'] == 'reliabilityRating') { $sortCol='reliabilityRating'; }
	else if ($_REQUEST['sortCol'] == 'gameCount') { $sortCol='gameCount'; }
	else if ($_REQUEST['sortCol'] == 'points') { $sortCol='points'; }
}

if ( isset($_REQUEST['sortType'])) { if ($_REQUEST['sortType'] == 'desc') { $sortType='desc'; } }

if ($serverHasPHPBB == 1) {	if ( isset($_REQUEST['seeNewForumLink'])) { $seeNewForumLink='checked'; } }

// If this is checked we want to show all columns.
if ( isset($_REQUEST['seeAll']))
{
	$seeUsername = 'checked';
	$seePoints = 'checked';
	$seeJoined = 'checked';
	$seeGameCount = 'checked';
	$seeRR = 'checked';
	$seeMod = 'checked';
	$seeBanned = 'checked';
	$seeGold = 'checked';
	$seeSilver = 'checked';
	$seeBronze = 'checked';
	if ($serverHasPHPBB == 1) { $seeNewForumLink = 'checked'; }
}

if ( isset($_REQUEST['type']) && $_REQUEST['type'] && strlen($_REQUEST['type']) )
{
	if ($_REQUEST['type'] == 'Banned') {$type = 'Banned';}
	else if ($_REQUEST['type'] == 'Donators') {$type = 'Donator';}
	else if ($_REQUEST['type'] == 'Bronze') {$type = 'DonatorBronze';}
	else if ($_REQUEST['type'] == 'Silver') {$type = 'DonatorSilver';}
	else if ($_REQUEST['type'] == 'Gold') {$type = 'DonatorGold';}
	else if ($_REQUEST['type'] == 'Mod') {$type = 'Moderator';}
	else {$type = 'none';}
}

// Game Search Objects
class GameResultData
{
	public $gameID;
	public $gameName;
	public $pot;
	public $phase; 						//enum('Finished','Pre-game','Diplomacy','Retreats','Builds')
	public $gameOver; 				// enum('No','Won','Drawn')
	public $processStatus; 		// enum('Not-processing','Processing','Crashed','Paused')
	public $hasPassword; 			// is password set?
	public $potType; 					//enum('Winner-takes-all','Points-per-supply-center','Unranked','Sum-of-squares')
	public $minimumBet;
	public $phaseMinutes;
	public $anon; 						// yes/no
	public $pressType; 				//enum('Regular','PublicPressOnly','NoPress','RulebookPress')
	public $directorUserID;
	public $minimumRR;
	public $minimumNMRScore;
	public $drawType;					//enum('draw-votes-public','draw-votes-hidden')
	public $watchedCount;
}

// Game Search Variables
$GamesData = array();
$gamename = '';
$gamename2 = '';
$gamename3 = '';
$searchTypeg1 = 'Starts';
$searchTypeg2 = 'Starts';
$searchTypeg3 = 'Starts';
$sortColg = 'id';
$seeGamename='unchecked';
$seePot='unchecked';
$seeInviteCode='unchecked';
$seePotType='unchecked';
$seeJoinable='unchecked';
$seePhaseLength='unchecked';
$seeAnon='unchecked';
$seePressType='unchecked';
$seeDirector='unchecked';
$seeMinRR='unchecked';
$seeDrawType='unchecked';
$seeVariant = 'unchecked';
$seeWatchedCount = 'unchecked';
$showOnlyJoinable = 'unchecked';
$seeGameOver = 'unchecked';

if ( isset($_REQUEST['gamename']) && $_REQUEST['gamename'] && strlen($_REQUEST['gamename']) ) { $gamename = $DB->escape($_REQUEST['gamename']); }
if ( isset($_REQUEST['gamename2']) && $_REQUEST['gamename2'] && strlen($_REQUEST['gamename2']) ) { $gamename2 = $DB->escape($_REQUEST['gamename2']); }
if ( isset($_REQUEST['gamename3']) && $_REQUEST['gamename3'] && strlen($_REQUEST['gamename3']) ) { $gamename3 = $DB->escape($_REQUEST['gamename3']); }

if ( isset($_REQUEST['searchTypeg1']))
{
	if ($_REQUEST['searchTypeg1'] == 'Contains') { $searchTypeg1='Contains'; }
	else if ($_REQUEST['searchTypeg1'] == 'Ends') { $searchTypeg1='Ends'; }
}
if ( isset($_REQUEST['searchTypeg2']))
{
	if ($_REQUEST['searchTypeg2'] == 'Contains') { $searchTypeg2='Contains'; }
	else if ($_REQUEST['searchTypeg2'] == 'Ends') { $searchTypeg2='Ends'; }
}

if ( isset($_REQUEST['searchTypeg3']))
{
	if ($_REQUEST['searchTypeg3'] == 'Contains') { $searchTypeg3='Contains'; }
	else if ($_REQUEST['searchTypeg3'] == 'Ends') { $searchTypeg3='Ends'; }
}
if ( isset($_REQUEST['sortColg']))
{
	if ($_REQUEST['sortColg'] == 'gameName') { $sortColg='gameName'; }
	else if ($_REQUEST['sortColg'] == 'pot') { $sortColg='pot'; }
	else if ($_REQUEST['sortColg'] == 'phaseMinutes') { $sortColg='phaseMinutes'; }
	else if ($_REQUEST['sortColg'] == 'watchedGames') { $sortColg='watchedGames'; }
}

if ( isset($_REQUEST['seeGamename'])) { $seeGamename='checked'; }
if ( isset($_REQUEST['seePot'])) { $seePot='checked'; }
if ( isset($_REQUEST['seeInviteCode'])) { $seeInviteCode='checked'; }
if ( isset($_REQUEST['seePotType'])) { $seePotType='checked'; }
if ( isset($_REQUEST['seeJoinable'])) { $seeJoinable='checked'; }
if ( isset($_REQUEST['seePhaseLength'])) { $seePhaseLength='checked'; }
if ( isset($_REQUEST['seeAnon'])) { $seeAnon='checked'; }
if ( isset($_REQUEST['seePressType'])) { $seePressType='checked'; }
if ( isset($_REQUEST['seeDirector'])) { $seeDirector='checked'; }
if ( isset($_REQUEST['seeMinRR'])) { $seeMinRR='checked'; }
if ( isset($_REQUEST['seeDrawType'])) { $seeDrawType='checked'; }
if ( isset($_REQUEST['seeVariant'])) { $seeVariant='checked'; }
if ( isset($_REQUEST['seeGameOver'])) { $seeGameOver='checked'; }
if ( isset($_REQUEST['seeWatchedCount'])) { $seeWatchedCount='checked'; }

if ( isset($_REQUEST['showOnlyJoinable'])) { $showOnlyJoinable='checked'; }

// If this is checked we want to show all columns.
if ( isset($_REQUEST['seeAll']))
{
	$seeGamename='checked';
	$seePot='checked';
	$seeInviteCode='checked';
	$seePotType='checked';
	$seeJoinable='checked';
	$seePhaseLength='checked';
	$seeAnon='checked';
	$seePressType='checked';
	$seeDirector='checked';
	$seeMinRR='checked';
	$seeDrawType='checked';
	$seeVariant = 'checked';
	$seeGameOver = 'checked';
	$seeWatchedCount = 'checked';
}

// Game by User variables
$paramUserID = 0;
$username2 = '';
$checkAgainstMe = 'unchecked';
$pagenum = 1;

if ( isset($_REQUEST['paramUserID']) ) {	$paramUserID=(int)$_REQUEST['paramUserID']; }
if ( isset($_REQUEST['username2']) && $_REQUEST['username2'] && strlen($_REQUEST['username2']) )
{
	$username2 = $DB->escape($_REQUEST['username2']);
}
if ( isset($_REQUEST['checkAgainstMe'])) { $checkAgainstMe='checked'; }

// Paging variables
$maxPage = 0;
$resultsPerPage = 50;

if ( isset($_REQUEST['pagenum'])) { $pagenum=(int)$_REQUEST['pagenum']; }
if ( isset($_REQUEST['resultsPerPage']))
{
	if ($_REQUEST['resultsPerPage'] == '25') { $resultsPerPage=25; }
	else if ($_REQUEST['resultsPerPage'] == '100') { $resultsPerPage=100; }
	else if ($_REQUEST['resultsPerPage'] == '200') { $resultsPerPage=200; }
	else if ($_REQUEST['resultsPerPage'] == '1000') { $resultsPerPage=1000; }
}

// Print the header and standard php for the site that is required on every page.
libHTML::starthtml();
print libHTML::pageTitle(l_t('Advanced Search'),l_t('Advanced search options for users or games.'));
?>

<?php
// Collapsible search criteria for user search keeps page readable based on search type user wants.
print '<button class="SearchCollapsible">User Search Options (expand)</button>';
print '<div class="advancedSearchContent">';

// Print a form for selecting which users to check
print '<FORM class="advancedSearch" method="get" action="detailedSearch.php#tableLocation">
		<INPUT type="hidden" name="tab" value="UserSearch" />

		<p>Username: <INPUT class="advancedSearch" type="text" name="username"  value="'. $username .'" size="20" />
		<select  class = "advancedSearch" name="searchType1">
			<option selected="selected" value="Starts">Starts With</option>
			<option value="Contains">Contains</option>
			<option value="Ends">Ends with</option>
		</select>
		</p>
		<p>Find Users of Type:
		<select  class = "advancedSearch" name="type">
			<option selected="selected" value="none">None</option>
			<option value="Donators">All Donator</option>
			<option value="Bronze">Bronze Donator</option>
			<option value="Silver">Silver Donator</option>
			<option value="Gold">Gold Donator</option>
			<option value="Banned">Banned</option>
			<option value="Mod">Moderators</option>
		</select></p>

		<p><strong>Columns in Result:</strong></br>
		<input class="advancedSearch" type="checkbox" name="seeUsername" value="seeUsername" checked="checked">Username
		<input class="advancedSearch" type="checkbox" name="seePoints" value="seePoints" checked="checked">Points
		<input class="advancedSearch" type="checkbox" name="seeJoined" value="seeJoined">Time Joined
		<input class="advancedSearch" type="checkbox" name="seeGameCount" value="seeGameCount">Game Count
		<input class="advancedSearch" type="checkbox" name="seeRR" value="seeRR" checked="checked">RR';
		if ($serverHasPHPBB == 1)
		{
			print '<input class="advancedSearch" type="checkbox" name="seeNewForumLink" value="seeNewForumLink" checked="checked">New Forum link';
		}
		print'</br></br> <strong>Yes/No columns:</strong></br>
		<input class="advancedSearch" type="checkbox" name="seeMod" value="seeMod">IsMod
		<input class="advancedSearch" type="checkbox" name="seeBanned" value="seeBanned">IsBanned
		<input class="advancedSearch" type="checkbox" name="seeGold" value="seeGold">IsGold
		<input class="advancedSearch" type="checkbox" name="seeSilver" value="seeSilver">IsSilver
		<input class="advancedSearch" type="checkbox" name="seeBronze" value="seeBronze">IsBronze
		</br></br>
		<input class="advancedSearch" type="checkbox" name="seeAll" value="seeAll">See All (pulls all columns)
		</br> </br>
		<strong>Sorting:</strong>
		</br>
		<select  class = "advancedSearch" name="sortCol">
			<option selected="selected" value="id">User ID</option>
			<option value="Username">Username</option>
			<option value="timeJoined">Time Joined</option>
			<option value="RR">RR</option>
			<option value="gameCount">Game Count</option>
			<option value="points">Points</option>
		</select>
		<select  class = "advancedSearch" name="sortType">
			<option selected="selected" value="asc">Ascending</option>
			<option value="desc">Descending</option>
		</select>
		</br></br>
		<strong># of results to show per page</strong>
		</br>
		<select  class = "advancedSearch" name="resultsPerPage">
			<option value="25">25</option>
			<option selected="selected" value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
			<option value="1000">1000</option>
		</select>
		</p>

		<input type="submit" name="Submit" class="green-Submit" value="Check" /></form>
		</br>';
print '</div>';

print '</br></br>';

// Collapsible search criteria for game search keeps page readable based on search type user wants.
print '<button class="SearchCollapsible">Game Search Options (expand)</button>';
print '<div class="advancedSearchContent">';
print '<FORM class="advancedSearch" method="get" action="detailedSearch.php#tableLocation">
		<INPUT type="hidden" name="tab" value="GameSearch" />

		<p>Game Name:
		<select  class = "advancedSearch" name="searchTypeg1">
			<option selected="selected" value="Starts">Starts With</option>
			<option value="Contains">Contains</option>
			<option value="Ends">Ends with</option>
		</select>
		<INPUT class="advancedSearch" type="text" name="gamename"  value="'. $gamename .'" size="20" />
		</br>
		Or
		</br>
		Game Name:
		<select  class = "advancedSearch" name="searchTypeg2">
			<option selected="selected" value="Starts">Starts With</option>
			<option value="Contains">Contains</option>
			<option value="Ends">Ends with</option>
		</select>
		<INPUT class="advancedSearch" type="text" name="gamename2"  value="'. $gamename2 .'" size="20" />
		</br>
		Or
		</br>
		Game Name:
		<select  class = "advancedSearch" name="searchTypeg3">
			<option selected="selected" value="Starts">Starts With</option>
			<option value="Contains">Contains</option>
			<option value="Ends">Ends with</option>
		</select>
		<INPUT class="advancedSearch" type="text" name="gamename3"  value="'. $gamename3 .'" size="20" />
		</br></br>
		<input class="advancedSearch" type="checkbox" name="showOnlyJoinable" value="showOnlyJoinable" >Show only joinable public games?
		</p>

		<p>
		<strong>Columns in Result:</strong></br>
		<input class="advancedSearch" type="checkbox" name="seeGamename" value="seeGamename" checked="checked">Game Name
		<input class="advancedSearch" type="checkbox" name="seeGameOver" value="seeGameOver" checked="checked">Game Over
		<input class="advancedSearch" type="checkbox" name="seePot" value="seePot"  checked="checked">Pot
		<input class="advancedSearch" type="checkbox" name="seeInviteCode" value="seeInviteCode">Invite Code
		<input class="advancedSearch" type="checkbox" name="seePotType" value="seePotType">Pot Type
		<input class="advancedSearch" type="checkbox" name="seeJoinable" value="seeJoinable" checked="checked">Joinable
		<input class="advancedSearch" type="checkbox" name="seePhaseLength" value="seePhaseLength">Phase Length
		<input class="advancedSearch" type="checkbox" name="seeAnon" value="seeAnon">Anon
		<input class="advancedSearch" type="checkbox" name="seePressType" value="seePressType">Press Type
		<input class="advancedSearch" type="checkbox" name="seeDirector" value="seeDirector">Director
		<input class="advancedSearch" type="checkbox" name="seeMinRR" value="seeMinRR">Min RR
		<input class="advancedSearch" type="checkbox" name="seeDrawType" value="seeDrawType">Draw Type
		<input class="advancedSearch" type="checkbox" name="seeVariant" value="seeVariant" checked="checked">Variant
		<input class="advancedSearch" type="checkbox" name="seeWatchedCount" value="seeWatchedCount" checked="checked">Spectator Count

		</br></br>
		<input class="advancedSearch" type="checkbox" name="seeAll" value="seeAll">See All (pulls all columns)
		</p>
		<p>
		<strong>Sorting:</strong>
		</br>
		<select  class = "advancedSearch" name="sortColg">
			<option selected="selected" value="id">Game ID</option>
			<option value="gameName">Game Name</option>
			<option value="pot">Pot</option>
			<option value="phaseMinutes">Phase Length</option>
			<option value="watchedGames">Number of Spectators</option>
		</select>

		<select  class = "advancedSearch" name="sortType">
			<option selected="selected" value="asc">Ascending</option>
			<option value="desc">Descending</option>
		</select>
		</br></br>
		<strong># of results to show per page</strong>
		</br>
		<select  class = "advancedSearch" name="resultsPerPage">
			<option value="25">25</option>
			<option selected="selected" value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
			<option value="1000">1000</option>
		</select>
		</p>

		<input type="submit" name="Submit" class="green-Submit" value="Check" /></form>
		</br>';
print '</div>';

print '</br></br>';

// Collapsible search criteria for searching games by user keeps page readable based on search type user wants.
print '<button class="SearchCollapsible">Search Games by User (expand)</button>';
print '<div class="advancedSearchContent">';
print '<FORM class="advancedSearch" method="get" action="detailedSearch.php#tableLocation">
		<INPUT type="hidden" name="tab" value="GamesByUser" />

		<p>Username:
		<INPUT class="advancedSearch" type="text" name="username2"  value="'. $username2 .'" size="20"/>
		User ID:
		<INPUT class="advancedSearch" type="text" name="paramUserID"  value="'. $paramUserID .'" size="20"/>
		</br></br>
		<input class="advancedSearch" type="checkbox" name="checkAgainstMe" value="checkAgainstMe">Show games the user and I have in common
		</p>

		<p>
		<strong>Columns in Result:</strong></br>
		<input class="advancedSearch" type="checkbox" name="seeGamename" value="seeGamename" checked="checked">Game Name
		<input class="advancedSearch" type="checkbox" name="seeGameOver" value="seeGameOver" checked="checked">Game Over
		<input class="advancedSearch" type="checkbox" name="seePot" value="seePot"  checked="checked">Pot
		<input class="advancedSearch" type="checkbox" name="seeInviteCode" value="seeInviteCode">Invite Code
		<input class="advancedSearch" type="checkbox" name="seePotType" value="seePotType">Pot Type
		<input class="advancedSearch" type="checkbox" name="seeJoinable" value="seeJoinable" checked="checked">Joinable
		<input class="advancedSearch" type="checkbox" name="seePhaseLength" value="seePhaseLength">Phase Length
		<input class="advancedSearch" type="checkbox" name="seeAnon" value="seeAnon">Anon
		<input class="advancedSearch" type="checkbox" name="seePressType" value="seePressType">Press Type
		<input class="advancedSearch" type="checkbox" name="seeDirector" value="seeDirector">Director
		<input class="advancedSearch" type="checkbox" name="seeMinRR" value="seeMinRR">Min RR
		<input class="advancedSearch" type="checkbox" name="seeDrawType" value="seeDrawType">Draw Type
		<input class="advancedSearch" type="checkbox" name="seeVariant" value="seeVariant" checked="checked">Variant
		<input class="advancedSearch" type="checkbox" name="seeWatchedCount" value="seeWatchedCount" checked="checked">Spectator Count

		</br></br>
		<input class="advancedSearch" type="checkbox" name="seeAll" value="seeAll">See All (pulls all columns)
		</p>
		<p>
		<strong>Sorting:</strong>
		</br>
		<select  class = "advancedSearch" name="sortColg">
			<option selected="selected" value="id">Game ID</option>
			<option value="gameName">Game Name</option>
			<option value="pot">Pot</option>
			<option value="phaseMinutes">Phase Length</option>
			<option value="watchedGames">Number of Spectators</option>
		</select>

		<select  class = "advancedSearch" name="sortType">
			<option selected="selected" value="asc">Ascending</option>
			<option value="desc">Descending</option>
		</select>
		</br></br>
		<strong># of results to show per page</strong>
		</br>
		<select  class = "advancedSearch" name="resultsPerPage">
			<option value="25">25</option>
			<option selected="selected" value="50">50</option>
			<option value="100">100</option>
			<option value="200">200</option>
			<option value="1000">1000</option>
		</select>
		</p>

		<input type="submit" name="Submit" class="green-Submit" value="Check" /></form>
		</br>';
print '</div>';

print '</br></br>';


if ($tab == 'UserSearch')
{
	if ($type != 'none' || $username != '')
	{
		$sql = "SELECT u.id, u.username, u.email, u.timeJoined, u.gameCount, u.reliabilityRating, u.points, u.type
				FROM wD_Users u WHERE u.type not like '%System%' and u.type not like '%Guest%'";

		$sqlCounter = "SELECT count(1)  FROM wD_Users u WHERE u.type not like '%System%' and u.type not like '%Guest%'";

		if ($username)
		{
			$username = strip_tags(html_entity_decode(trim($username)));
			if ($searchType1 == 'Ends')
			{
				$sql = $sql." and trim(u.username) like '%".$username."'";
				$sqlCounter = $sqlCounter." and trim(u.username) like '%".$username."'";
			}
			else if ($searchType1 == 'Contains')
			{
				$sql = $sql." and trim(u.username) like '%".$username."%'";
				$sqlCounter = $sqlCounter." and trim(u.username) like '%".$username."%'";
			}
			else
			{
				$sql = $sql." and trim(u.username) like '".$username."%'";
				$sqlCounter = $sqlCounter." and trim(u.username) like '".$username."%'";
			}
		}

		if ($type && $type != 'none')
		{
			if ($type == 'DonatorBronze')
			{
				$sql = $sql." and u.type like '%". $type."%' and u.type not like '%DonatorSilver%' and u.type not like '%DonatorGold%'";
				$sqlCounter = $sqlCounter." and u.type like '%". $type."%' and u.type not like '%DonatorSilver%' and u.type not like '%DonatorGold%'";
			}
			else if ($type == 'DonatorSilver')
			{
				$sql = $sql." and u.type like '%". $type."%' and u.type not like '%DonatorGold%'";
				$sqlCounter = $sqlCounter." and u.type like '%". $type."%' and u.type not like '%DonatorGold%'";
			}
			else
			{
				$sql = $sql." and u.type like '%". $type."%'";
				$sqlCounter = $sqlCounter." and u.type like '%". $type."%'";
			}
		}

		$sql = $sql . " ORDER BY u.".$sortCol." ".$sortType." ";
		$sql = $sql . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";

		$tablChecked = $DB->sql_tabl($sql);

		/*
		 * Loop through all the users gathered from the query above who joined in the last X days and have already been checked.
		 * If the option to recheck is on, this list will be ignored.
		 */
		while (list($userID, $username, $email, $timeJoined, $gameCount, $reliabilityRating, $points, $userType) = $DB->tabl_row($tablChecked))
		{
			$myUser = new UserResultData();
			$myUser->userID = $userID;
			$myUser->username = $username;
			$myUser->email = $email;
			$myUser->timeJoined = $timeJoined;
			$myUser->gameCount = $gameCount;
			$myUser->points = $points;
			$myUser->reliabilityRating = $reliabilityRating;

			// This object is not used but holds all type data in case future types are added in later for easy use.
			$myUser->userType = $userType;

			// Check for various types possible based on the enum in wD_Users.
			if (strpos($userType, 'Moderator') !== false) { $myUser->mod = true; } else { $myUser->mod = false;}
			if (strpos($userType, 'Banned') !== false) { $myUser->banned = true; } else { $myUser->banned = false;}

			if (strpos($userType, 'DonatorGold') !== false)
			{
				$myUser->gold = true;
				$myUser->silver = false;
				$myUser->bronze = false;
			}
			else if (strpos($userType, 'DonatorSilver') !== false)
			{
				$myUser->gold = false;
				$myUser->silver = true;
				$myUser->bronze = false;
			}
			else if (strpos($userType, 'DonatorBronze') !== false)
			{
				$myUser->gold = false;
				$myUser->silver = false;
				$myUser->bronze = true;
			}
			else
			{
				$myUser->gold = false;
				$myUser->silver = false;
				$myUser->bronze = false;
			}

			if (strpos($userType, 'DonatorPlatinum') !== false) { $myUser->platinum = true; } else { $myUser->platinum = false;}
			$myUser->reliabilityRating = $reliabilityRating;
			array_push($UsersData,$myUser);
		}

		list($totalResults) = $DB->sql_row($sqlCounter);
		$maxPage = ceil($totalResults / $resultsPerPage);
		print '<p class = "modTools"> Showing results '.min(((($pagenum - 1) * $resultsPerPage)+1),$totalResults).' to '.min(($pagenum * $resultsPerPage),$totalResults).' of '.$totalResults.' total results. </br>';
		print "<a name='tableLocation'></a>";
		print "<TABLE class='advancedSearch'>";
		print "<tr>";
		print '<th class= "advancedSearch"';
		if ($sortCol == 'id')
		{
			print 'style="background-color: #006699;"';
		}
		print '>';
		printHeaderLink('UserID', $tab, $sortCol, $sortType, $sortColg);
		print '</th>';

		// Adjust table columns based on user selection.
		if ($seeUsername=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortCol == 'Username')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Username', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($serverHasPHPBB == 1) { if ($seeNewForumLink) { print '<th class= "advancedSearch">New Forum</th>'; } }
		if ($seeJoined=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortCol == 'timeJoined')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Joined On', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seeGameCount=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortCol == 'gameCount')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Games', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seePoints=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortCol == 'points')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Points', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seeRR=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortCol == 'reliabilityRating')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('RR', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seeMod=='checked') { print '<th class= "advancedSearch">IsMod</th>'; }
		if ($seeBanned=='checked') { print '<th class= "advancedSearch">IsBanned</th>'; }
		if ($seeGold=='checked') { print '<th class= "advancedSearch">IsGold</th>'; }
		if ($seeSilver=='checked') { print '<th class= "advancedSearch">IsSilver</th>'; }
		if ($seeBronze=='checked') { print '<th class= "advancedSearch">IsBronze</th>'; }

		print "</tr>";

		foreach ($UsersData as $values)
		{
			print '<TR><TD class= "advancedSearch"><a href="profile.php?userID='.$values->userID.'">'.$values->userID.'</a>';

			// Print the mod or banned icons if the user is one or the other.
			if ($values->mod) { print ' <img src="images/icons/mod.png" title="Moderator/Admin" />'; }
			if ($values->banned) { print ' <img src="images/icons/cross.png"title="Banned" />'; }

			// Only show the highest level of donator status held by a user.
			if ($values->platinum) { print libHTML::platinum(); }
			else if ($values->gold) { print libHTML::gold(); }
			else if ($values->silver) { print libHTML::silver(); }
			else if ($values->bronze) { print libHTML::bronze(); }

			print '</TD>';

			if ($seeUsername=='checked') { print '<TD class= "advancedSearch">'.$values->username.'</TD>'; }

			/*
			 * If the server has a phpbb forum table structure then we can safely query those tables to get the data.
			 * This is a slow design but necessary to safely pull the data.
			 */
			if ($serverHasPHPBB == 1)
			{
				if ($seeNewForumLink)
				{
					list($newForumId) = $DB->sql_row("SELECT user_id FROM `phpbb_users` WHERE webdip_user_id = ".$values->userID);
					if ($newForumId > 0) { print '<TD class= "advancedSearch"><a href="/contrib/phpBB3/memberlist.php?mode=viewprofile&u='.$newForumId.'">New Forum</a></TD>'; }
					else { print '<TD class= "advancedSearch">N/A</TD>'; }
				}
			}

			if ($seeJoined=='checked') { print '<TD class= "advancedSearch">'.gmstrftime("%b %d %Y",$values->timeJoined).'</TD>'; }
			if ($seeGameCount=='checked') { print '<TD class= "advancedSearch">'.$values->gameCount.'</TD>'; }
			if ($seePoints=='checked') { print '<TD class= "advancedSearch">'.$values->points.libHTML::points().'</TD>'; }
			if ($seeRR=='checked') { print '<TD class= "advancedSearch">'.round($values->reliabilityRating,2).'%</TD>'; }

			if ($seeMod=='checked')
			{
				print '<TD class= "advancedSearch">';
				$values->mod ? print 'Yes</TD>' : print 'No</TD>';
			}
			if ($seeBanned=='checked')
			{
				print '<TD class= "advancedSearch">';
				$values->banned ? print 'Yes</TD>' :  print'No</TD>';
			}
			if ($seeGold=='checked')
			{
				print '<TD class= "advancedSearch">';
				$values->gold ? print 'Yes</TD>' :  print'No</TD>';
			}
			if ($seeSilver=='checked')
			{
				print '<TD class= "advancedSearch">';
				$values->silver ? print 'Yes</TD>' : print 'No</TD>';
			}
			if ($seeBronze=='checked')
			{
				print '<TD class= "advancedSearch">';
				$values->bronze ? print 'Yes</TD>' : print 'No</TD>';
			}

			print "</TR>";
		}
		print "</TABLE>";
	}
	else { print '<p class = "advancedSearch">Please fill out username or type.</p>'; }
}

else if ($tab == 'GameSearch')
{
	if ($gamename != '' || $showOnlyJoinable == 'checked')
	{
		$sql = "SELECT g.id, g.name, g.pot,g.phase, g.gameOver, g.processStatus, ( CASE WHEN g.password IS NULL THEN 'False' ELSE 'True' END ) AS password,
				g.potType, g.minimumBet, g.phaseMinutes, g.anon, g.pressType, g.directorUserID, g.minimumReliabilityRating, g.drawType,
				(select count(1) from wD_WatchedGames w where w.gameID = g.id) AS watchedGames
				FROM wD_Games g WHERE 1 = 1";

		$sqlCounter = "SELECT count(1) FROM wD_Games g WHERE 1 = 1";

		$parenAdd = False;

		if ($gamename)
		{
			$gamename = strip_tags(html_entity_decode(trim($gamename)));
			if ($searchTypeg1 == 'Ends')
			{
				$sql = $sql." and ( trim(g.name) like '%".$gamename."'";
				$sqlCounter = $sqlCounter." and ( trim(g.name) like '%".$gamename."'";
			}
			else if ($searchTypeg1 == 'Contains')
			{
				$sql = $sql." and ( trim(g.name) like '%".$gamename."%'";
				$sqlCounter = $sqlCounter." and ( trim(g.name) like '%".$gamename."%'";
			}
			else
			{
				$sql = $sql." and ( trim(g.name) like '".$gamename."%'";
				$sqlCounter = $sqlCounter." and ( trim(g.name) like '".$gamename."%'";
			}
			$parenAdd = True;
		}
		if ($gamename2 && $gamename != '')
		{
			$gamename2 = strip_tags(html_entity_decode(trim($gamename2)));
			if ($searchTypeg2 == 'Ends')
			{
				$sql = $sql." or trim(g.name) like '%".$gamename2."'";
				$sqlCounter = $sqlCounter." or trim(g.name) like '%".$gamename2."'";
			}
			else if ($searchTypeg2 == 'Contains')
			{
				$sql = $sql." or trim(g.name) like '%".$gamename2."%'";
				$sqlCounter = $sqlCounter." or trim(g.name) like '%".$gamename2."%'";
			}
			else
			{
				$sql = $sql." or trim(g.name) like '".$gamename2."%'";
				$sqlCounter = $sqlCounter." or trim(g.name) like '".$gamename2."%'";
			}
		}
		if ($gamename3 && $gamename != '')
		{
			$gamename3 = strip_tags(html_entity_decode(trim($gamename3)));
			if ($searchTypeg3 == 'Ends')
			{
				$sql = $sql." or trim(g.name) like '%".$gamename3."'";
				$sqlCounter = $sqlCounter." or trim(g.name) like '%".$gamename3."'";
			}
			else if ($searchTypeg3 == 'Contains')
			{
				$sql = $sql." or trim(g.name) like '%".$gamename3."%'";
				$sqlCounter = $sqlCounter." or trim(g.name) like '%".$gamename3."%'";
			}
			else
			{
				$sql = $sql." or trim(g.name) like '".$gamename3."%'";
				$sqlCounter = $sqlCounter." or trim(g.name) like '".$gamename3."%'";
			}
		}

		if ($parenAdd)
		{
			$sql = $sql." ) ";
			$sqlCounter = $sqlCounter." ) ";
		}

		if ($showOnlyJoinable == 'checked')
		{
			$sql = $sql." and g.minimumBet is not null and g.password is null and g.gameOver = 'No' and g.phase <> 'Pre-game' ";
			$sqlCounter = $sqlCounter." and g.minimumBet is not null and g.password is null and g.gameOver = 'No' and g.phase <> 'Pre-game' ";
		}

		if ($sortColg == 'watchedGames')
		{
			$sql = $sql . " ORDER BY watchedGames ".$sortType." ";
			$sql = $sql . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";

		}
		elseif ($sortColg == 'gameName')
		{
			$sql = $sql . " ORDER BY g.name ".$sortType." ";
			$sql = $sql . " LIMIT ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";
		}
		else
		{
			$sql = $sql . " ORDER BY g.".$sortColg." ".$sortType." ";
			$sql = $sql . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";
		}

		$tablChecked = $DB->sql_tabl($sql);

		while (list($gameID, $gameName, $pot, $phase, $gameOver, $processStatus, $password, $potType, $minimumBet, $phaseMinutes, $anon,
		$pressType, $directorUserID, $minimumRR, $drawType, $watchedCount) = $DB->tabl_row($tablChecked))
		{
			$myGame = new GameResultData();
			$myGame->gameID = $gameID;
			$myGame->gameName = $gameName;
			$myGame->pot = $pot;
			$myGame->phase = $phase;
			$myGame->gameOver = $gameOver;
			if ($password == 'True' ) {$myGame->password = true; } else {$myGame->password = false; };
			$myGame->potType = $potType;
			$myGame->minimumBet = $minimumBet;
			$myGame->phaseMinutes = $phaseMinutes;
			$myGame->anon = $anon;
			$myGame->pressType = $pressType;
			$myGame->directorUserID = $directorUserID;
			$myGame->minimumRR = $minimumRR;
			$myGame->drawType = $drawType;
			$myGame->watchedCount = $watchedCount;
			array_push($GamesData,$myGame);
		}

		list($totalResults) = $DB->sql_row($sqlCounter);
		$maxPage = ceil($totalResults / $resultsPerPage);
		print '<p class = "modTools"> Showing results '.min(((($pagenum - 1) * $resultsPerPage)+1),$totalResults).' to '.min(($pagenum * $resultsPerPage),$totalResults).' of '.$totalResults.' total results. </br>';

		printGameResults($seeVariant, $seeGamename, $seeGameOver, $seePot, $seeInviteCode, $seePotType, $seeJoinable, $seePhaseLength,
		$seeAnon, $seePressType, $seeDirector, $seeMinRR, $seeDrawType, $seeWatchedCount, $GamesData, $tab, $sortCol, $sortType, $sortColg);
	}
	else { print '<p class = advancedSearch> Please enter a value in the first Game search option or check show only joinable games</p>';}
}

else if ($tab == 'GamesByUser')
{
	$IsUserValid = 0;
	if ($username2 != '')
	{
		$username2 = strip_tags(html_entity_decode(trim($username2)));

		list($userIDResult) = $DB->sql_row("SELECT id FROM wD_Users WHERE username = '".$username2."'");
		if ($userIDResult > 0)
		{
			$paramUserID = $userIDResult;
			$IsUserValid = 1;
		}
	}

	else if ($paramUserID == 0)
	{
		list($IsUserValid) = $DB->sql_row("SELECT count(1) FROM wD_Users WHERE id = ".$User->id);
		$paramUserID = $User->id;
	}
	else { list($IsUserValid) = $DB->sql_row("SELECT count(1) FROM wD_Users WHERE id = ".$paramUserID); }

	//User Check here if user is not blank
	if ($IsUserValid == 1)
	{
		$sql = "SELECT g.id, g.name, g.pot,g.phase, g.gameOver, g.processStatus, ( CASE WHEN g.password IS NULL THEN 'False' ELSE 'True' END ) AS password,
				g.potType, g.minimumBet, g.phaseMinutes, g.anon, g.pressType, g.directorUserID, g.minimumReliabilityRating, g.drawType,
				(select count(1) from wD_WatchedGames w where w.gameID = g.id) AS watchedGames
				FROM wD_Games g inner join wD_Members m on m.gameID = g.id WHERE g.phase = 'Finished' and  m.userID = ".$paramUserID." ";

		$sqlCounter = "SELECT count(1) FROM wD_Games g inner join wD_Members m on m.gameID = g.id WHERE g.phase = 'Finished' and m.userID = ".$paramUserID." ";
		list($checkedUsername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id = ".$paramUserID);

		if ($paramUserID == $User->id ) { $userMessage =  "Showing my completed games.</p>";}
		else if ($checkAgainstMe == 'checked')
		{
			$sql = $sql." and ((select count(1) from wD_Members m where m.userID = ".$User->id." and m.gameID = g.id ) > 0) ";
			$sqlCounter = $sqlCounter." and ((select count(1) from wD_Members m where m.userID = ".$User->id." and m.gameID = g.id ) > 0) ";
			$userMessage = "Showing <a href='profile.php?userID=".$paramUserID."'>".$checkedUsername."'s</a> completed games against me.</p>";
		}
		else { $userMessage =  "Showing <a href='profile.php?userID=".$paramUserID."'>".$checkedUsername."'s</a> completed games.</p>";}

		if ($showOnlyJoinable == 'checked')
		{
			$sql = $sql." and g.minimumBet is not null and g.password is null and g.gameOver = 'No' ";
			$sqlCounter = $sqlCounter." and g.minimumBet is not null and g.password is null and g.gameOver = 'No' ";
		}

		if ($sortColg == 'watchedGames')
		{
			$sql = $sql . " ORDER BY watchedGames ".$sortType." ";
			$sql = $sql . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";
		}
		elseif ($sortColg == 'gameName')
		{
			$sql = $sql . " ORDER BY g.name ".$sortType." ";
			$sql = $sql . " LIMIT ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";
		}
		else
		{
			$sql = $sql . " ORDER BY g.".$sortColg." ".$sortType." ";
			$sql = $sql . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";
		}

		$tablChecked = $DB->sql_tabl($sql);

		while (list($gameID, $gameName, $pot, $phase, $gameOver, $processStatus, $password, $potType, $minimumBet, $phaseMinutes, $anon,
		$pressType, $directorUserID, $minimumRR, $drawType, $watchedCount) = $DB->tabl_row($tablChecked))
		{
			$myGame = new GameResultData();
			$myGame->gameID = $gameID;
			$myGame->gameName = $gameName;
			$myGame->pot = $pot;
			$myGame->phase = $phase;
			$myGame->gameOver = $gameOver;
			if ($password == 'True' ) {$myGame->password = true; } else {$myGame->password = false; };
			$myGame->potType = $potType;
			$myGame->minimumBet = $minimumBet;
			$myGame->phaseMinutes = $phaseMinutes;
			$myGame->anon = $anon;
			$myGame->pressType = $pressType;
			$myGame->directorUserID = $directorUserID;
			$myGame->minimumRR = $minimumRR;
			$myGame->drawType = $drawType;
			$myGame->watchedCount = $watchedCount;
			array_push($GamesData,$myGame);
		}

		list($totalResults) = $DB->sql_row($sqlCounter);
		$maxPage = ceil($totalResults / $resultsPerPage);
		print '<p class = "modTools">Showing results '.min(((($pagenum - 1) * $resultsPerPage)+1),$totalResults).' to '.min(($pagenum * $resultsPerPage),$totalResults).' of '.$totalResults.' total results.</br>';
		print $userMessage;

		printGameResults($seeVariant, $seeGamename, $seeGameOver, $seePot, $seeInviteCode, $seePotType, $seeJoinable, $seePhaseLength,
		$seeAnon, $seePressType, $seeDirector, $seeMinRR, $seeDrawType, $seeWatchedCount, $GamesData, $tab, $sortCol, $sortType, $sortColg);

	}
	else { print '<p class = advancedSearch> The user you entered is not valid. Please enter a valid user or a User ID of 0 to see your own games.</p>';}
}

if (isset($_REQUEST['tab']))
{
	print '</br>';

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
}

/*
 * This function will take in all the variables related to user choice on seeing columns in output, and the $GamesData
 * variable holding all the games in the result set and print them out to an html table. This is used by both the game
 * search and the search games by user search.
 */
function printGameResults($seeVariant, $seeGamename, $seeGameOver, $seePot, $seeInviteCode, $seePotType, $seeJoinable, $seePhaseLength,
$seeAnon, $seePressType, $seeDirector, $seeMinRR, $seeDrawType, $seeWatchedCount, $GamesData, $tab, $sortCol, $sortType, $sortColg)
{
	print "<a name='tableLocation'></a>";
	print "<TABLE class='advancedSearch'>";
		print "<tr>";
		print '<th class= "advancedSearch"';
		if ($sortColg == 'id')
		{
			print 'style="background-color: #006699;"';
		}
		print '>';
		printHeaderLink('Game ID', $tab, $sortCol, $sortType, $sortColg);
		print '</th>';

		if ($seeVariant=='checked') { print '<th class= "advancedSearch">Variant</th>'; }
		if ($seeGamename=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortColg == 'gameName')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Name', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seeGameOver=='checked') { print '<th class= "advancedSearch">Game Over?</th>'; }
		if ($seePot=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortColg == 'pot')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Pot', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seeInviteCode=='checked') { print '<th class= "advancedSearch">Invite Only</th>'; }
		if ($seePotType=='checked') { print '<th class= "advancedSearch">Pot Type</th>'; }
		if ($seeJoinable=='checked') { print '<th class= "advancedSearch">Open?</th>'; }
		if ($seePhaseLength=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortColg == 'phaseMinutes')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Length', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}
		if ($seeAnon=='checked') { print '<th class= "advancedSearch">Anon</th>'; }
		if ($seePressType=='checked') { print '<th class= "advancedSearch">Press Type</th>'; }
		if ($seeDirector=='checked') { print '<th class= "advancedSearch">Game Director</th>'; }
		if ($seeMinRR=='checked') { print '<th class= "advancedSearch">Min RR</th>'; }
		if ($seeDrawType=='checked') { print '<th class= "advancedSearch">Draw Type</th>'; }
		if ($seeWatchedCount=='checked')
		{
			print '<th class= "advancedSearch"';
			if ($sortColg == 'watchedGames')
			{
				print 'style="background-color: #006699;"';
			}
			print '>';
			printHeaderLink('Spectators', $tab, $sortCol, $sortType, $sortColg);
			print '</th>';
		}

		print "</tr>";

		foreach ($GamesData as $values)
		{
			$Variant=libVariant::loadFromGameID($values->gameID);

			print '<TR><TD class= "advancedSearch"><a href="board.php?gameID='.$values->gameID.'">'.$values->gameID.'</a></TD>';
			if ($seeVariant=='checked') {print '<TD class= "advancedSearch">'.$Variant->link().'</a></TD>'; }
			if ($seeGamename=='checked') { print '<TD class= "advancedSearch">'.$values->gameName.'</TD>'; }
			if ($seeGameOver=='checked') { print '<TD class= "advancedSearch">'.$values->gameOver.'</TD>'; }
			if ($seePot=='checked') { print '<TD class= "advancedSearch">'.$values->pot.libHTML::points().'</TD>'; }
			if ($seeInviteCode=='checked')
			{
				print '<TD class= "advancedSearch">';
				$values->password ? print 'Yes</TD>' : print 'No</TD>';
			}
			if ($seePotType=='checked') { print '<TD class= "advancedSearch">'.$values->potType.'</TD>'; }
			if ($seeJoinable=='checked')
			{
				if ($values->minimumBet > 0) { print '<TD class= "advancedSearch">'.$values->minimumBet.' '.libHTML::points().'</TD>'; }
				else { print '<TD class= "advancedSearch">No</TD>'; }
			}
			if ($seePhaseLength=='checked') { print '<TD class= "advancedSearch">'.libTime::timeLengthText($values->phaseMinutes*60).'</TD>'; }
			if ($seeAnon=='checked') { print '<TD class= "advancedSearch">'.$values->anon.'</TD>'; }
			if ($seePressType=='checked') { print '<TD class= "advancedSearch">'.$values->pressType.'</TD>'; }

			if ($seeDirector=='checked')
			{
				if ($values->directorUserID) { print '<TD class= "advancedSearch"><a href="profile.php?userID='.$values->directorUserID.'">'.$values->directorUserID.'</a></TD>'; }
				else { print '<TD class= "advancedSearch">N/A</TD>'; }
			}
			if ($seeMinRR=='checked') { print '<TD class= "advancedSearch">'.$values->minimumRR.'</TD>'; }
			if ($seeDrawType=='checked') { print '<TD class= "advancedSearch">'.$values->drawType.'</TD>'; }
			if ($seeWatchedCount=='checked') { print '<TD class= "advancedSearch">'.$values->watchedCount.'</TD>'; }

			print "</TR>";
		}
		print "</TABLE>";
}

//This function prints out a button that will take you to the page of the number you feed into it. It will look white is $currPage is set to True.
function printPageButton($pagenum, $currPage)
{
	if ($currPage)
	{
		print '<div class="curr-page">'.$pagenum.'</div>';
	}
	else
	{
		print '<div style="display:inline-block; margin:3px;">';
		print '<FORM method="get" action=detailedSearch.php#tableLocation>';
		foreach($_REQUEST as $key => $value)
		{
			if(strpos('x'.$key,'wD') == false && $key!="pagenum")
			{
				print '<input type="hidden" name="'.$key.'" value='.$value.'>';
			}
		}
		print '<input type="submit" name="pagenum" class="form-submit" value='.$pagenum.' /></form></div>';
	}
}

function printHeaderLink($header, $tab, $sortCol, $sortType, $sortColg)
{
	print '<FORM method="get" action=detailedSearch.php#tableLocation>';
	foreach($_REQUEST as $key => $value)
	{
		if(strpos('x'.$key,'wD') == false && $key!="sortCol" && $key!="sortColg" && $key!="sortType" && $key!="pagenum")
		{
			print '<input type="hidden" name="'.$key.'" value='.$value.'>';
		}
	}
	$convert = array("UserID"=>"id","Username"=>"Username","Joined On"=>"timeJoined","RR"=>"reliabilityRating","Games"=>"gameCount","Points"=>"points", "Game ID"=>"id", "Name"=>"gameName", "Pot"=>"pot", "Length"=>"phaseMinutes", "Spectators"=>"watchedGames");
	if($tab == 'UserSearch')
	{
		if ($convert[$header] == $sortCol)
		{
			if ($sortType == 'asc')
			{
				print '<input type="hidden" name="sortType" value=desc>';
				print '<button type="submit" name="sortCol" value='.$convert[$header].' class="advancedSearchHeader"';
				print '>'.$header.' &#9652</button></form>';
			}
			else{
				print '<input type="hidden" name="sortType" value=asc>';
				print '<button type="submit" name="sortCol" value='.$convert[$header].' class="advancedSearchHeader"';
				print '>'.$header.' &#9662</button></form>';
			}
		}
		else{
			print '<input type="hidden" name="sortType" value=asc>';
			print '<button type="submit" name="sortCol" value='.$convert[$header].' class="advancedSearchHeader"';
			print '>'.$header.'</button></form>';
		}
	}
	else{
		if ($convert[$header] == $sortColg)
		{
			if ($sortType == 'asc')
			{
				print '<input type="hidden" name="sortType" value=desc>';
				print '<button type="submit" name="sortColg" value='.$convert[$header].' class="advancedSearchHeader"';
				print '>'.$header.' &#9652</button></form>';
			}
			else{
				print '<input type="hidden" name="sortType" value=asc>';
				print '<button type="submit" name="sortColg" value='.$convert[$header].' class="advancedSearchHeader"';
				print '>'.$header.' &#9662</button></form>';
			}
		}
		else{
			print '<input type="hidden" name="sortType" value=asc>';
			print '<button type="submit" name="sortColg" value='.$convert[$header].' class="advancedSearchHeader"';
			print '>'.$header.'</button></form>';
		}
	}
}

print '</div>';
?>

<script>
var coll = document.getElementsByClassName("SearchCollapsible");
var searchCounter;

for (searchCounter = 0; searchCounter < coll.length; searchCounter++) {
  coll[searchCounter].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
		if (content.style.display === "block") { content.style.display = "none"; }
		else { content.style.display = "block"; }
  });
}
</script>

<?php
libHTML::footer();
?>
