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

/**
 * @package Base
 * @subpackage Forms
 */

?>
	<p><strong>E-mail address:</strong></br>
	<input type="text" class = "settings" name="userForm[email]" size="40" value="<?php
		if ( isset($_REQUEST['userForm']['email'] ) )
		{
			print htmlspecialchars($_REQUEST['userForm']['email'], ENT_QUOTES, 'UTF-8');
		}
		elseif( isset($User->email) )
		{
			print htmlspecialchars($User->email, ENT_QUOTES, 'UTF-8');
		}
		?>" <?php if ( isset($_REQUEST['emailToken']) ) print 'readonly '; ?> >
	</p>

	<p><strong>Password:</strong></br>
		<input type="password" name="userForm[password]" maxlength=30 autocomplete="new-password" class = "settings">
	</p>

	<p><strong>Confirm Password:</strong></br>
		<input type="password" name="userForm[passwordcheck]" maxlength=30 autocomplete="new-password" class = "settings">
	</p>
	<form
	<p>
		<div style="float:right"><i>Profile quote visible to others. Consider favorite quotes or links to games.</i></div>
		<strong>Comment:</strong></br>
		<TEXTAREA NAME="userForm[comment]" ROWS="3" COLS="50" class = "settings"><?php
			print str_replace('<br />', "\n", $User->comment ?? '');
		?></textarea>
	</p>

<?php
	

if (isset(Config::$customForumURL))
{
	list($newForumID) = $DB->sql_row("SELECT user_id FROM `phpbb_users` WHERE webdip_user_id = ".$User->id);
	if ($newForumID > 0)
	{
		print '<p class="profileCommentURL"><strong><a href="/contrib/phpBB3/ucp.php?i=179">Forum User Settings</a></strong></p>';
	}
}

if( isset(Config::$enabledOptInFeatures) && Config::$enabledOptInFeatures > 0 )
	{
		// Opt-in features are enabled in the config, so enumerate all the features this user can use
		/*
		This went unused and should be phased out
		$optInFeatures = array(
			0x1 => array(
				'Early Access: show in-development features (may be buggy!)', 
				''
			),
		);
		foreach($optInFeatures as $featureFlag => $featureInfo)
		{
			if( ( $featureFlag & Config::$enabledOptInFeatures ) == 0 ) continue;
			list($header, $description) = $featureInfo;
			print '
			<p>
				<div><li class="settings"><strong>'.$header.':</strong></li>
				<div><i>'.$description.'</i></div>
				<input type="radio" name="userForm[optInFeature_'.$featureFlag.']" value="1" ' . ( ($featureFlag & $User->optInFeatures) > 0 ? "checked" : "") . '>Yes
				<input type="radio" name="userForm[optInFeature_'.$featureFlag.']" value="0" ' . ( ($featureFlag & $User->optInFeatures) == 0 ? "checked" : "") . '>No
			</p>
			';
		}*/
		print '<input type="hidden" name="userForm[optInFeature_1]" value="1" />';
	}

foreach ($User->getOptions()->value as $name=>$val) 
{
	print '<div><li class="settings"><strong>'.UserOptions::$titles[$name].':</strong></li>';
	foreach (UserOptions::$possibleValues[$name] as $possible) 
	{
		print ' <input type="radio" name="userForm['.$name.']" value="'.$possible.'" '. ($val == $possible ? 'checked' :'') . ' > '. $possible;
	}
	print '</div></br>';
}
 	                               
if( $User->type['User'] ) 
{
	if (!isset(Config::$customForumURL))
	{
		// If the user is registered show the list of muted users/countries:
		$MutedUsers = array();
		foreach($User->getMuteUsers() as $muteUserID) 
		{
			$MutedUsers[] = new User($muteUserID);
		}
		if( count($MutedUsers) > 0 ) 
		{
			print '<li class="formlisttitle">Muted users:</li>';
			print '<li class="formlistdesc">The users which you muted, and are unable to send you messages.</li>';
			print '<li class="formlistfield"><ul>';
			foreach ($MutedUsers as $MutedUser) 
			{
				print '<li>'.$MutedUser->username.' '.libHTML::muted("profile.php?userID=".$MutedUser->id.'&toggleMute=on&rand='.rand(0,99999).'#mute').'</li>';
			}
			print '</ul></li>';
		}
	}

	list($muteCountryCount) = $DB->sql_row("select count(distinct c.gameID) from wD_MuteCountry c inner join wD_Games g on g.id = c.gameID where c.userID = ".$User->id);
	$muteCountry = $DB->sql_tabl("select distinct c.gameID, g.name from wD_MuteCountry c inner join wD_Games g on g.id = c.gameID where c.userID = ".$User->id);
	
	if( $muteCountryCount > 0 ) 
	{
		print '<strong>Games with Muted countries:</strong></br>To unmute visit the game and click speaker icon</br></br>'; 

		while (list($gameID, $name) = $DB->tabl_row($muteCountry))
        {		
			print '  <a href="board.php?gameID='.$gameID.'">'.$name.'</a></br>';
		}
		print'</br>';
		// Due to a rather serious flaw in the variant loading system, attempting to load a variant from this page will result in a php error related to serialization as of 
		// the php upgrade to 7.0. 
	} 
	
	if (!isset(Config::$customForumURL))
	{
		$tablMutedThreads = $DB->sql_tabl(
			"SELECT mt.muteThreadID, f.subject, f.replies, fu.username ".
			"FROM wD_MuteThread mt ".
			"INNER JOIN wD_ForumMessages f ON f.id = mt.muteThreadID ".
			"INNER JOIN wD_Users fu ON fu.id = f.fromUserID ".
			"WHERE mt.userID = ".$User->id);
		$mutedThreads = array();
		while( $mutedThread = $DB->tabl_hash($tablMutedThreads))
			$mutedThreads[] = $mutedThread;
		unset($tablMutedThreads);
		
		if( count($mutedThreads) > 0 ) 
		{
			print '<li class="formlisttitle"><a name="threadmutes"></a>Muted threads:</li>';
			print '<li class="formlistdesc">The threads which you muted.</li>';
			
			$unmuteThreadID=0;
			if( isset($_GET['unmuteThreadID']) ) 
			{
				$unmuteThreadID = (int)$_GET['unmuteThreadID'];
				$User->toggleThreadMute($unmuteThreadID);
				
				print '<li class="formlistfield"><strong>Thread <a class="light" href="forum.php?threadID='.$unmuteThreadID.'#'.$unmuteThreadID.
					'">#'.$unmuteThreadID.'</a> unmuted.</strong></li>';
			}
			
			print '<li class="formlistfield"><ul>';
			
			foreach ($mutedThreads as $mutedThread) 
			{
				if( $unmuteThreadID == $mutedThread['muteThreadID']) continue;
				print '<li>'.
					'<a class="light" href="forum.php?threadID='.$mutedThread['muteThreadID'].'#'.$mutedThread['muteThreadID'].'">'.
					$mutedThread['subject'].'</a> '.
					libHTML::muted('usercp.php?unmuteThreadID='.$mutedThread['muteThreadID'].'#threadmutes').'<br />'.
					$mutedThread['username'].' ('.$mutedThread['replies'].' replies)<br />'.
					'</li>';
			}
			print '</ul></li>';
		}
	}
}
/*
 * This is done in PHP because Eclipse complains about HTML syntax errors otherwise
 * because the starting <form><ul> is elsewhere
 */
print '</ul>

<p><input type="submit" class="green-Submit" value="Update"></p>';
print libAuth::formTokenHTML();
print '</form>
</div>';

?>
