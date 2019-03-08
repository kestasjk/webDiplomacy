<?php

/**
 * @package Base
 */

require_once('header.php');

if (!$User->type['Moderator']) { die ('Only admins or mods can run this script'); }


require_once(l_r('gamesearch/search.php'));
require_once(l_r('pager/pagergame.php'));
require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/game.php'));

$tab = '';

if (isset($_REQUEST['tab']))
{
	if ($_REQUEST['tab'] == 'UserSearch')
	{
		$tab = 'UserSearch';
		
	}
	else if ($_REQUEST['tab'] == 'GameSearch')
	{
		$tab = 'GameSearch';
	}
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

$UsersData = array();

// User Search Variables
$username = '';
$type = 'none';
$seeUsername = 'unchecked';
$seePoints = 'unchecked';
$seeJoined = 'unchecked';
$seeGameCount = 'unchecked';
$seeRR = 'unchecked';

// Game Search Objects
class GameResultData
{
    public $gameID;
    public $gameName;
    public $pot;
	public $phase; 				//enum('Finished','Pre-game','Diplomacy','Retreats','Builds')   
	public $gameOver; 			// enum('No','Won','Drawn')  
	public $processStatus; 		// enum('Not-processing','Processing','Crashed','Paused')  
	public $hasPassword; 		// is password set?
	public $potType; 			//enum('Winner-takes-all','Points-per-supply-center','Unranked','Sum-of-squares')
	public $minimumBet;
	public $phaseMinutes;
	public $anon; 				// yes/no 
	public $pressType; 			//enum('Regular','PublicPressOnly','NoPress','RulebookPress')
	public $directorUserID;
	public $minimumRR;
	public $minimumNMRScore;
	public $drawType;			//enum('draw-votes-public','draw-votes-hidden')  
}
		
$GamesData = array();

// Game Search Variables
$gamename = '';

if ( isset($_REQUEST['username']) && $_REQUEST['username'] && strlen($_REQUEST['username']) )
	$username = $DB->escape($_REQUEST['username']);
if ( isset($_REQUEST['seeUsername'])) { $seeUsername='checked'; }
if ( isset($_REQUEST['seePoints'])) { $seePoints='checked'; }
if ( isset($_REQUEST['seeJoined'])) { $seeJoined='checked'; }
if ( isset($_REQUEST['seeGameCount'])) { $seeGameCount='checked'; }
if ( isset($_REQUEST['seeRR'])) { $seeRR='checked'; }
if ( isset($_REQUEST['type']) && $_REQUEST['type'] && strlen($_REQUEST['type']) )
{
	if ($_REQUEST['type'] == 'banned') {$type = 'Banned';}
	else if ($_REQUEST['type'] == 'Donators') {$type = 'Donator';}
	else if ($_REQUEST['type'] == 'Bronze') {$type = 'DonatorBronze';}
	else if ($_REQUEST['type'] == 'Silver') {$type = 'DonatorSilver';}
	else if ($_REQUEST['type'] == 'Gold') {$type = 'DonatorGold';}
	else if ($_REQUEST['type'] == 'Mod') {$type = 'Moderator';}
	else {$type = 'none';}
}

if ( isset($_REQUEST['gamename']) && $_REQUEST['gamename'] && strlen($_REQUEST['gamename']) )
	$gamename = $DB->escape($_REQUEST['gamename']);

libHTML::starthtml();
print libHTML::pageTitle(l_t('Advanced Search'),l_t('Advanced search options for users or games.'));
	?>
	<?php

// Collapsible search criteria for user search keeps page readable based on search type user wants. 
print '<button class="userSearchCollapsible">User Search Options</button>';
print '<div class="advancedSearchContent">';

// Print a form for selecting which users to check
print '<FORM class="advancedSearch" method="get" action="detailedSearch.php">
		<INPUT type="hidden" name="tab" value="UserSearch" />
		
		<p>Username: <INPUT class="advancedSearch" type="text" name="username"  value="'. $username .'" size="20" /></br></p>
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

		<p>Columns in Result:
		<input class="advancedSearch" type="checkbox" name="seeUsername" value="seeUsername"  checked="checked">Username 
		<input class="advancedSearch" type="checkbox" name="seePoints" value="seePoints"  checked="checked">Points
		<input class="advancedSearch" type="checkbox" name="seeJoined" value="seeJoined">Time Joined
		<input class="advancedSearch" type="checkbox" name="seeGameCount" value="seeGameCount">Game Count
		<input class="advancedSearch" type="checkbox" name="seeRR" value="seeRR"  checked="checked">RR </p>
		
        <input class="advancedSearchform-submit" type="submit" name="Submit" class="form-submit" value="Check" /></form>';
print '</div>';

print '</br>';
print '</br>';

print '<button class="gameSearchCollapsible">Game Search Options</button>';
print '<div class="advancedSearchContent">';

// Collapsible search criteria for game search keeps page readable based on search type user wants. 
print '<FORM class="advancedSearch" method="get" action="detailedSearch.php">
		<INPUT type="hidden" name="tab" value="GameSearch" />
		
		<p>Game Name: <INPUT class="advancedSearch" type="text" name="gamename"  value="'. $gamename .'" size="20" /></br></p>
		
        <input class="advancedSearchform-submit" type="submit" name="Submit" class="form-submit" value="Check" /></form>';
print '</div>';

if ($tab == 'UserSearch')
{
	// ADD SEARCH BY USERS IN GAMES
	// ADD OUTPUT FOR BOOLEAN FOR USER TYPES
	// BETTER TESTING PATTERNS? choice of starts with/ends with/contains
	// joined time options, ask PW
	if ($type != 'none' || $username != '')
	{
		$sql = "SELECT u.id, u.username, u.email, u.timeJoined, u.gameCount, u.reliabilityRating, u.points, u.type 
				FROM wD_Users u
				WHERE u.type not like '%System%' and u.type not like '%Guest%'";
		
		if ($username)
		{
			$sql = $sql." and u.username like '". $username."%'";
		}

		if ($type && $type != 'none')
		{
			$sql = $sql." and u.type like '%". $type."%'";
		}
		
		$sql = $sql . " ORDER BY u.id DESC Limit 50;";
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
			if (strpos($userType, 'DonatorGold') !== false) { $myUser->gold = true; } else { $myUser->gold = false;}
			if (strpos($userType, 'DonatorSilver') !== false) { $myUser->silver = true; } else { $myUser->silver = false;}
			if (strpos($userType, 'DonatorBronze') !== false) { $myUser->bronze = true; } else { $myUser->bronze = false;}
			if (strpos($userType, 'DonatorPlatinum') !== false) { $myUser->platinum = true; } else { $myUser->platinum = false;}
			$myUser->reliabilityRating = $reliabilityRating;
			array_push($UsersData,$myUser);
		}

		print "<TABLE class='advancedSearch'>";
		print "<tr>";
		print '<th class= "advancedSearch">UserId:</th>';
		
		// Adjust table columns based on user selection. 
		if ($seeUsername=='checked') { print '<th class= "advancedSearch">Username</th>'; }
		if ($seeJoined=='checked') { print '<th class= "advancedSearch">Joined On</th>'; }
		if ($seeGameCount=='checked') { print '<th class= "advancedSearch">Games</th>'; }
		if ($seePoints=='checked') { print '<th class= "advancedSearch">Points</th>'; }
		if ($seeRR=='checked') { print '<th class= "advancedSearch">RR</th>'; }

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

			// Only print rows asked for by the user. 
			if ($seeUsername=='checked') { print '<TD class= "advancedSearch">'.$values->username.'</TD>'; }
			if ($seeJoined=='checked') { print '<TD class= "advancedSearch">'.gmstrftime("%d %b / %I:%M %p",$values->timeJoined).'</TD>'; }
			if ($seeGameCount=='checked') { print '<TD class= "advancedSearch">'.$values->gameCount.'</TD>'; }
			if ($seePoints=='checked') { print '<TD class= "advancedSearch">'.$values->points.libHTML::points().'</TD>'; }
			if ($seeRR=='checked') { print '<TD class= "advancedSearch">'.round($values->reliabilityRating,2).'%</TD>'; }
			print "</TR>";
		}
		print "</TABLE>";
	} 
	else { if ($username != '') { print '<p class = "advancedSearch">'.$username.' is not valid. Please enter a number between 1 and 1,000.</p>'; } }
}

else if ($tab == 'GameSearch')
{
	if ($gamename != '')
	{
		// change password here to a case to say true or false so we don't expose the password to the client. 
		$sql = "SELECT g.id, g.name, g.pot,g.phase, g.gameOver, g.processStatus, g.password, g.potType, g.minimumBet,g.phaseMinutes,g.anon, 
				g.pressType, g.directorUserID, g.minimumReliabilityRating, g.drawType
				FROM wD_Games g
				WHERE 1 = 1";
		
		if ($gamename)
		{
			$sql = $sql." and g.name like '". $gamename."%'";
		}
		
		$sql = $sql . " ORDER BY g.id DESC Limit 50;";
		$tablChecked = $DB->sql_tabl($sql);

		while (list($gameID, $gameName, $pot, $phase, $gameOver, $processStatus, $password, $potType, $minimumBet, $phaseMinutes, $anon, 
		$pressType, $directorUserID, $minimumRR, $drawType) = $DB->tabl_row($tablChecked))
		{   
			$myGame = new GameResultData();
			$myGame->gameID = $gameID;
			$myGame->gameName = $gameName;
			$myGame->pot = $pot;
			$myGame->phase = $phase;
			$myGame->gameOver = $gameOver;
			if (strlen($password) > 0 ) {$myGame->password = true; } else {$myGame->password = false; };
			$myGame->potType = $potType;
			$myGame->minimumBet = $minimumBet;
			$myGame->phaseMinutes = $phaseMinutes;
			$myGame->anon = $anon;
			$myGame->pressType = $pressType;
			$myGame->directorUserID = $directorUserID;
			$myGame->minimumRR = $minimumRR;
			$myGame->drawType = $drawType;
			array_push($GamesData,$myGame);
		}

		print "<TABLE class='advancedSearch'>";
		print "<tr>";
		print '<th class= "advancedSearch">GameId:</th>';
		print '<th class= "advancedSearch">Name:</th>';

		print "</tr>";
	
		foreach ($GamesData as $values)
		{   
			print '<TR><TD class= "advancedSearch"><a href="board.php?gameID='.$values->gameID.'">'.$values->gameID.'</a>';
			print '<TD class= "advancedSearch">'.$values->gameName.'</TD>';

			print '</TD>';		
			print "</TR>";
		}
		print "</TABLE>";
	}
}

else{
	
}

print '</div>';
?>

<script>
var coll = document.getElementsByClassName("userSearchCollapsible");
var userCounter;

for (userCounter = 0; userCounter < coll.length; userCounter++) {
  coll[userCounter].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}

var coll = document.getElementsByClassName("gameSearchCollapsible");
var gameCounter;

for (gameCounter = 0; gameCounter < coll.length; gameCounter++) {
  coll[gameCounter].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}
</script>
<?php

libHTML::footer();

?>