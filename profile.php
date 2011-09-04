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

require_once('gamesearch/search.php');
require_once('pager/pagergame.php');
require_once('objects/game.php');
require_once('gamepanel/game.php');

if ( isset($_REQUEST['userID']) && intval($_REQUEST['userID'])>0 )
{
	$userID = (int)$_REQUEST['userID'];
}
elseif( isset($_REQUEST['searchUser']) )
{
	libAuth::resourceLimiter('user search',5);

	if( !is_array($_REQUEST['searchUser']) )
		throw new Exception("Invalid search data submitted.");

	$searchUser = $_REQUEST['searchUser'];

	$searchUserValid=array();
	if ( isset($searchUser['id']) && $searchUser['id'] && strlen($searchUser['id']) )
		$searchUserValid['id'] = (int)$searchUser['id'];

	if ( isset($searchUser['username']) && $searchUser['username'] && strlen($searchUser['username']) )
		$searchUserValid['username'] = $DB->escape($searchUser['username']);

	if ( isset($searchUser['email']) && $searchUser['email'] && strlen($searchUser['email']) )
		$searchUserValid['email'] = $DB->escape($searchUser['email']);

	unset($searchUser);

	$whereSQL=array();
	foreach($searchUserValid as $searchFieldName=>$searchFieldValue)
	{
		if( $searchFieldName == 'id' )
			$whereSQL[] = $searchFieldName." = ".$searchFieldValue;
		else
			$whereSQL[] = $searchFieldName." LIKE '".$searchFieldValue."'";
	}

	$userID=false;

	if( count($whereSQL) )
	{
		list($foundUserID) = $DB->sql_row("SELECT id FROM wD_Users WHERE ".implode(' OR ', $whereSQL)." LIMIT 1");
		if( !isset($foundUserID) || !$foundUserID )
		{
			$searchReturn = 'No users found matching the given search parameters.';
		}
		else
		{
			$searchReturn = 'Matching user found!';
			$userID=$foundUserID;
		}
	}
}
else
{
	$userID = false;
}

if ( !$userID )
{
	libHTML::starthtml('Search for user');

	print libHTML::pageTitle('Search for user','Search for a user using either their ID, username, e-mail address, or any combination of the three.');
	?>

	<?php if( isset($searchReturn) ) print '<p class="notice">'.$searchReturn.'</p>'; ?>

	<form action="profile.php" method="post">
	<ul class="formlist">

		<li class="formlisttitle">ID number:</li>
		<li class="formlistfield">
			<input type="text" name="searchUser[id]" value="" size="10">
		</li>
		<li class="formlistdesc">
			The user's ID number.
		</li>

		<li class="formlisttitle">Username:</li>
		<li class="formlistfield">
			<input type="text" name="searchUser[username]" value="" size="30">
		</li>
		<li class="formlistdesc">
			The user's username (This isn't case sensitive, but otherwise it must match exactly.)
		</li>

		<li class="formlisttitle">E-mail address:</li>
		<li class="formlistfield">
			<input type="text" name="searchUser[email]" value="" size="50">
		</li>
		<li class="formlistdesc">
			The user's e-mail address (This also isn't case sensitive, but otherwise it must match exactly.)
		</li>
	</ul>

	<div class="hr"></div>

	<p class="notice">
		<input type="submit" class="form-submit" value="Search">
	</p>
	</form>

	</div>
	<?php
	libHTML::footer();
}
else
{
	try
	{
		$UserProfile = new User($userID);
	}
	catch (Exception $e)
	{
		libHTML::error("Invalid user ID given.");
	}
}

if ( ! $UserProfile->type['User'] && !$UserProfile->type['Banned'] )
{
	$message = 'Cannot display profile: The specified account #'.$userID.' is not an active user; ';
	if( $UserProfile->type['System'] )
		$message .= 'it\'s a system account, without a real human using it.';
	elseif( $UserProfile->type['Guest'] )
		$message .= 'it\'s a guest account, used by unregistered people to
			view the server without interacting.';
	else
		$message .= 'in fact I\'m not sure what this account is...';

	foreach($UserProfile->type as $name=>$on)
		$message .= $name.', ';
	libHTML::error($message);
}


libHTML::starthtml();

print '<div class="content">';

if( isset($searchReturn) )
	print '<p class="notice">'.$searchReturn.'</p>';

if ( isset($_REQUEST['detail']) )
{
	print '<p>(<a href="profile.php?userID='.$UserProfile->id.'">Back</a>)</p>';

	switch($_REQUEST['detail'])
	{
		case 'threads':
			$dir=User::cacheDir($UserProfile->id);
			if( file_exists($dir.'/profile_threads.html') )
				print file_get_contents($dir.'/profile_threads.html');
			else
			{
				libAuth::resourceLimiter('view threads',20);

				$tabl = $DB->sql_tabl("SELECT id, subject, message, timeSent FROM wD_ForumMessages
					WHERE fromUserID = ".$UserProfile->id." AND type='ThreadStart'
					ORDER BY timeSent DESC");

				$buf = '<h4>Threads posted:</h4>
					<ul>';
				while(list($id,$subject,$message, $timeSent)=$DB->tabl_row($tabl))
				{
					$buf .= '<li><em>'.libTime::text($timeSent).'</em>:
						<a href="forum.php?threadID='.$id.'">'.$subject.'</a><br />'.
						$message.'</li>';
				}
				$buf .= '</ul>';

				file_put_contents($dir.'/profile_threads.html', $buf);
				print $buf;
			}
			break;

		case 'replies':
			$dir=User::cacheDir($UserProfile->id);
			if( file_exists($dir.'/profile_replies.html') )
				print file_get_contents($dir.'/profile_replies.html');
			else
			{
				libAuth::resourceLimiter('view replies',20);

				$tabl = $DB->sql_tabl("SELECT f.id, a.id, a.subject, f.message, f.timeSent
					FROM wD_ForumMessages f INNER JOIN wD_ForumMessages a ON ( f.toID = a.id )
					WHERE f.fromUserID = ".$UserProfile->id." AND f.type='ThreadReply'
					ORDER BY f.timeSent DESC");

				$buf = '<h4>Replies:</h4>
					<ul>';
				while(list($id,$threadID,$subject, $message, $timeSent)=$DB->tabl_row($tabl))
				{
					$buf .= '<li><em>'.libTime::text($timeSent).'</em>: <a href="forum.php?threadID='.$threadID.'#'.$id.'">Re: '.$subject.'</a><br />'.
						$message.'</li>';
				}
				$buf .= '</ul>';

				file_put_contents($dir.'/profile_replies.html', $buf);
				print $buf;
			}
			break;

		case 'civilDisorders':
			libAuth::resourceLimiter('view civil disorders',5);

			$tabl = $DB->sql_tabl("SELECT g.name, c.countryID, c.turn, c.bet, c.SCCount
				FROM wD_CivilDisorders c INNER JOIN wD_Games g ON ( c.gameID = g.id )
				WHERE c.userID = ".$UserProfile->id);

			print '<h4>Civil disorders:</h4>
				<ul>';
			while(list($name, $countryID, $turn, $bet, $SCCount)=$DB->tabl_row($tabl))
			{
				print '<li>
					Game: <strong>'.$name.'</strong>,
					countryID: <strong>'.$countryID.'</strong>,
					turn: <strong>'.$turn.'</strong>,
					bet: <strong>'.$bet.'</strong>,
					supply centers: <strong>'.$SCCount.'</strong>
					</li>';
			}
			print '</ul>';
			break;

		case 'reports':
			require_once('lib/modnotes.php');
			libModNotes::checkDeleteNote();
			libModNotes::checkInsertNote();
			print libModNotes::reportBoxHTML('User', $UserProfile->id);
			print libModNotes::reportsDisplay('User', $UserProfile->id);
		break;
	}

	print '</div>';
	libHTML::footer();
}

print '<div><div class="rightHalf">
		';

$rankingDetails = $UserProfile->rankingDetails();

$showAnon = ($UserProfile->id == $User->id || $User->type['Moderator']);

print '<ul class="formlist">';

print '<li><strong>Rank:</strong> '.$rankingDetails['rank'].'</li>';

if ( $rankingDetails['position'] < $rankingDetails['rankingPlayers'] )
	print '<li><strong>Position:</strong> '.$rankingDetails['position'].' / '.
		$rankingDetails['rankingPlayers'].' (top '.$rankingDetails['percentile'].'%)</li>';

print '<li><strong>Available points:</strong> '.$UserProfile->points.' '.libHTML::points().'</li>';

print '<li><strong>Points in play:</strong> '.($rankingDetails['worth']-$UserProfile->points-($showAnon ? 0 : $rankingDetails['anon']['points'])).' '.libHTML::points().'</li>';

print '<li><strong>Total points:</strong> '.$rankingDetails['worth'].' '.libHTML::points().'</li>';

if( $UserProfile->type['DonatorPlatinum'] )
	$donatorMarker = libHTML::star().' - <strong>Platinum</strong>';
if( $UserProfile->type['DonatorGold'] )
	$donatorMarker = libHTML::gold().' - <strong>Gold</strong>';
elseif( $UserProfile->type['DonatorSilver'] )
	$donatorMarker = libHTML::silver().' - Silver';
elseif( $UserProfile->type['DonatorBronze'] )
	$donatorMarker = libHTML::bronze().' - Bronze';
else
	$donatorMarker = false;

if( $donatorMarker )
	print '<li>&nbsp;</li><li><strong>Donator:</strong> '.$donatorMarker.'</li>';

print '<li>&nbsp;</li>';


list($posts) = $DB->sql_row(
	"SELECT SUM(gameMessagesSent) FROM wD_Members m
	WHERE m.userID = ".$UserProfile->id);
if( is_null($posts) ) $posts=0;
print '<li><strong>Game messages:</strong> '.$posts.'</li>';

print '<li>&nbsp;</li>';
$total = 0;
$includeStatus=array('Won','Drawn','Survived','Defeated','Resigned');
foreach($rankingDetails['stats'] as $name => $status)
{
	if ( !in_array($name, $includeStatus) ) continue;

	$total += $status;
	if (!$showAnon && isset($rankingDetails['anon'][$name]))
		$total -= $rankingDetails['anon'][$name];
}

if( $total )
{
	print '<li><strong>Game stats:</strong> <ul class="gamesublist">';

	foreach($rankingDetails['stats'] as $name => $status)
	{
		if ( !in_array($name, $includeStatus) ) continue;

		print '<li>'.$name.': <strong>'.$status.'</strong>';
		print ' ( '.round(($status/$total)*100).'% )';
		print '</li>';
	}

	print '<li>Total (finished): <strong>'.$total.'</strong></li>';

	foreach($rankingDetails['stats'] as $name => $status)
	{
		if ( in_array($name, $includeStatus) ) continue;

		if (!$showAnon && isset($rankingDetails['anon'][$name]))
			$status -= $rankingDetails['anon'][$name];
		print '<li>'.$name.': <strong>'.$status.'</strong></li>';
	}

	if ( $rankingDetails['takenOver'] )
		print '<li>Left and taken over: <strong>'.$rankingDetails['takenOver'].'</strong>
			(View: <a href="profile.php?detail=civilDisorders&userID='.$UserProfile->id.'">Details</a>)</li>';

	print '</ul></li>';
}
print '</ul></div>';


print "<h2>".$UserProfile->username;
if ( $User->type['User'] && $UserProfile->type['User'] && ! ( $User->id == $UserProfile->id || $UserProfile->type['Moderator'] || $UserProfile->type['Guest'] || $UserProfile->type['Admin'] ) )
{
	$userMuted = $User->isUserMuted($UserProfile->id);

	print '<a name="mute"></a>';
	if( isset($_REQUEST['toggleMute'])) {
		$User->toggleUserMute($UserProfile->id);
		$userMuted = !$userMuted;
	}
	$muteURL = 'profile.php?userID='.$UserProfile->id.'&toggleMute=on&rand='.rand(0,99999).'#mute';
	print ' '.($userMuted ? libHTML::muted($muteURL) : libHTML::unmuted($muteURL));
}
print '</h2>';

// Regular user info starts here:
print '<div class="leftHalf" style="width:50%">';




if( $UserProfile->type['Banned'] )
	print '<p><strong>Banned</strong></p>';

if ( $UserProfile->comment )
	print '<p class="profileComment">"'.$UserProfile->comment.'"</p>';

print '<p><ul class="formlist">';

if ( $UserProfile->type['Moderator'] || $UserProfile->type['Admin'] )
{
	print '<li><strong>Mod/Admin team</strong></li>';
	print '<li>&nbsp;</li>';
}

if ( $UserProfile->online )
	print '<li><strong>Currently logged in.</strong> ('.libHTML::loggedOn($UserProfile->id).')</li>';
else
	print '<li><strong>Last visited:</strong> '.libTime::text($UserProfile->timeLastSessionEnded).'</li>';

list($posts) = $DB->sql_row(
	"SELECT (
		SELECT COUNT(fromUserID) FROM `wD_ForumMessages` WHERE type='ThreadStart' AND fromUserID = ".$UserProfile->id."
		) + (
		SELECT COUNT(fromUserID) FROM `wD_ForumMessages` WHERE type='ThreadReply' AND fromUserID = ".$UserProfile->id."
		)"); // Doing the query this way makes MySQL use the type, fromUserID index
if( is_null($posts) ) $posts=0;
print '<li><strong>Forum posts:</strong> '.$posts.'
	(View: <a href="profile.php?detail=threads&userID='.$UserProfile->id.'">Threads</a>,
		<a href="profile.php?detail=replies&userID='.$UserProfile->id.'">replies</a>)
	</li>';

print '<li>&nbsp;</li>';
print '<li><strong>Joined:</strong> '.$UserProfile->timeJoinedtxt().'</li>';
print '<li><strong>User ID#:</strong> '.$UserProfile->id.'</li>';
if ( $UserProfile->hideEmail == 'No' )
{
	$emailCacheFilename = libCache::dirID('users',$UserProfile->id).'/email.png';
	if( !file_exists($emailCacheFilename) )
	{
		$image = imagecreate( strlen($UserProfile->email) *8, 15);
		$white = imagecolorallocate( $image, 255, 255, 255);
		$black = imagecolorallocate( $image, 0, 0, 0 );

		imagestring( $image, 2, 10, 1, $UserProfile->email, $black );

		imagepng($image, $emailCacheFilename);
	}

	print '<li><strong>E-mail:</strong>
			<img src="'.STATICSRV.$emailCacheFilename.'" alt="[E-mail address image]" title="To protect e-mails from spambots they are embedded in an image" >
		</li>';
}
else
{
	$emailCacheFilename = libCache::dirID('users',$UserProfile->id).'/email.png';

	if( file_exists($emailCacheFilename) )
		unlink($emailCacheFilename);

	if( $User->type['Moderator'] )
	{
		print '<li><strong>E-mail:</strong>
				'.$UserProfile->email.' <em>(hidden for non-mods)</em>
			</li>';
	}
}

if ( $UserProfile->homepage )
{
	print '<li><strong>Home page:</strong> '.$UserProfile->homepage.'</li>';
}

print '<li>&nbsp;</li>';

//print '<li><a href="profile.php?detail=reports&userID='.$UserProfile->id.'" class="light">View/post a moderator report</a></li>';

//print '<li>&nbsp;</li>';

print '</li></ul></p></div><div style="clear:both"></div></div>';


// Start interactive area:

if ( $User->type['Moderator'] && $User->id != $UserProfile->id )
{
	$modActions=array();

	if ( $User->type['Admin'] )
		$modActions[] = '<a href="index.php?auid='.$UserProfile->id.'">Enter this user\'s account</a>';

	$modActions[] = libHTML::admincpType('User',$UserProfile->id);

	if( !$UserProfile->type['Admin'] && ( $User->type['Admin'] || !$UserProfile->type['Moderator'] ) )
		$modActions[] = libHTML::admincp('banUser',array('userID'=>$UserProfile->id), 'Ban user');

	if( !$UserProfile->type['Donator'])
		$modActions[] = libHTML::admincp('makeDonator',array('userID'=>$UserProfile->id), 'Give donator benefits');

	if( $User->type['Admin'] && !$UserProfile->type['Moderator'] )
		$modActions[] = libHTML::admincp('giveModerator',array('userID'=>$UserProfile->id), 'Make moderator');

	if( $User->type['Admin'] && ($UserProfile->type['Moderator'] && !$UserProfile->type['Admin']) )
		$modActions[] = libHTML::admincp('takeModerator',array('userID'=>$UserProfile->id), 'Remove moderator');

	$modActions[] = libHTML::admincp('reportMuteToggle',array('userID'=>$UserProfile->id), ($UserProfile->muteReports=='No'?'Mute':'Unmute').' mod reports');

	$modActions[] = '<a href="admincp.php?tab=Multi-accounts&aUserID='.$UserProfile->id.'" class="light">'.
		'Enter multi-account finder</a>';

	if($modActions)
	{
		print '<div class="hr"></div>';
		print '<p class="notice">';
		print implode(' - ', $modActions);
		print '</p>';
	}
}

if ( $User->type['User'] && $User->id != $UserProfile->id)
{
	print '<div class="hr"></div>';

	print '<a name="messagebox"></a>';

	if ( isset($_REQUEST['message']) && $_REQUEST['message'] )
	{
		if ( ! libHTML::checkTicket() )
		{
			print '<p class="notice">You seem to be sending the same message again, this may happen if you refresh
				the page after sending a message.</p>';
		}
		else
		{
			$UserProfile->sendPM($User, $_REQUEST['message']);

			print '<p class="notice">Private message sent successfully.</p>';
		}
	}

	print '<div style="margin-left:20px"><ul class="formlist">';
	print '<li class="formlisttitle">Send private-message:</li>
		<li class="formlistdesc">Send a message to this user.</li>';

	print '<form action="profile.php?userID='.$UserProfile->id.'#messagebox" method="post">
		<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />
		<textarea name="message" style="width:80%" rows="4"></textarea></li>
		<li class="formlistfield"><input type="submit" class="form-submit" value="Send" /></li>
		</form>
		</ul>
		</div>';
}

libHTML::pagebreak();

$search = new search('Profile');

$profilePager = new PagerGames('profile.php',$total);
$profilePager->addArgs('userID='.$UserProfile->id);

if ( isset($_REQUEST['advanced']) && $User->type['User'] )
{
	print '<a name="search"></a>';
	print '<h3>Search '.$UserProfile->username.'\'s games: (<a href="profile.php?page=1&amp;userID='.$UserProfile->id.'#top" class="light">Close</a>)</h3>';

	$profilePager->addArgs('advanced=on');

	$searched=false;
	if ( isset($_REQUEST['search']) )
	{
		libAuth::resourceLimiter('profile game search',5);

		$searched=true;
		$_SESSION['search-profile.php'] = $_REQUEST['search'];

		$search->filterInput($_SESSION['search-profile.php']);
	}
	elseif( isset($_REQUEST['page']) && isset($_SESSION['search-profile.php']) )
	{
		$searched=true;
		$search->filterInput($_SESSION['search-profile.php']);
	}

	print '<div style="margin:30px">';
	print '<form action="profile.php?userID='.$UserProfile->id.'&advanced=on#top" method="post">';
	print '<input type="hidden" name="page" value="1" />';
	$search->formHTML();
	print '</form>';
	print '<p><a href="profile.php?page=1&amp;userID='.$UserProfile->id.'#top" class="light">Close search</a></p>';
	print '</div>';

	if( $searched )
	{
		print '<div class="hr"></div>';
		print $profilePager->pagerBar('top','<h3>Results:</h3>');

		$gameCount = $search->printGamesList($profilePager);

		if( $gameCount == 0 )
		{
			print '<p class="notice">';

			if( $profilePager->currentPage > 1 )
				print 'No more games found for the given search parameters.';
			else
				print 'No games found for the given search parameters, try broadening your search.';

			print '</p>';
			print '<div class="hr"></div>';
		}
	}
}
else
{
	$searched = true;

	if(isset($_SESSION['search-profile.php']))
		unset($_SESSION['search-profile.php']);

	$leftSide = '<h3>'.$UserProfile->username.'\'s games '.
			( $User->type['User'] ? '(<a href="profile.php?userID='.$UserProfile->id.'&advanced=on#search">Search</a>)' : '' ).
			'</h3>';
	print $profilePager->pagerBar('top', $leftSide);

	$gameCount = $search->printGamesList($profilePager);

	if ( $gameCount == 0 )
	{
		print '<p class="notice">';
		if( $profilePager->currentPage > 1 )
			print 'No more games found for this profile.';
		else
			print 'No games found for this user.';
		print '</p>';

		print '<div class="hr"></div>';
	}
}

if ( $searched && $gameCount > 1 )
	print $profilePager->pagerBar('bottom','<a href="#top">Back to top</a>');
else
	print '<a name="bottom"></a>';

print '</div>';
libHTML::footer();

?>
