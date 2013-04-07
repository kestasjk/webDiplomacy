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
	<li class="formlisttitle">E-mail address</li>
	<li class="formlistfield"><input type="text" name="userForm[email]" size="50" value="<?php
		if ( isset($_REQUEST['userForm']['email'] ) )
		{
			print $_REQUEST['userForm']['email'];
		}
		elseif( isset($User->email) )
		{
			print $User->email;
		}
		?>" <?php if ( isset($_REQUEST['emailToken']) ) print 'readonly '; ?> /></li>
	<li class="formlistdesc">Your e-mail address; this will <strong>not</strong> be spammed or given out to anyone.</li>

	<li class="formlisttitle">Hide e-mail address:</li>
	<li class="formlistfield">
		<input type="radio" name="userForm[hideEmail]" value="Yes" <?php if($User->hideEmail=='Yes') print "checked"; ?>>Yes
		<input type="radio" name="userForm[hideEmail]" value="No" <?php if($User->hideEmail=='No') print "checked"; ?>>No
	</li>
	<li class="formlistdesc">
		Select whether or not you would like other users to be able
		to see your e-mail address. If you choose to show your e-mail it
		will be embedded into an image to prevent spam bots from picking it up,
		so only humans can see it even if you choose to show it.
	</li>

	<li class="formlisttitle">Password:</li>
	<li class="formlistfield">
		<input type="password" name="userForm[password]" maxlength=30>
	</li>
	<li class="formlistdesc">
		Your webDiplomacy password.
	</li>

	<li class="formlisttitle">Password again:</li>
	<li class="formlistfield">
		<input type="password" name="userForm[passwordcheck]" maxlength=30>
	</li>
	<li class="formlistdesc">
		Re-enter your webDiplomacy password, to make sure there are no typos.
	</li>

	<li class="formlisttitle">Home page:</li>
	<li class="formlistfield">
		<input type="text" size=50 name="userForm[homepage]" value="<?php print $User->homepage; ?>" maxlength=150>
	</li>
	<li class="formlistdesc">
		<?php if ( !$User->type['User'] ) print '<strong>(Optional)</strong>: '; ?>
		Your blog, personal website or favourite website.
	</li>

	<li class="formlisttitle">Comment:</li>
	<li class="formlistfield">
		<TEXTAREA NAME="userForm[comment]" ROWS="3" COLS="50"><?php
			print str_replace('<br />', "\n", $User->comment);
		?></textarea>
	</li>
	<li class="formlistdesc">
		<?php if ( !$User->type['User'] ) print '<strong>(Optional)</strong>: '; ?>
		A comment you would like to make in your profile. eg Your AIM username or ICQ number.
	</li>
	
	<li class="formlisttitle">Show countryname (useful for colorblind people.):</li>
	<li class="formlistfield">
		<strong>In global chat:</strong>
		<input type="radio" name="userForm[showCountryNames]" value="Yes" <?php if($User->showCountryNames=='Yes') print "checked"; ?>>Yes
		<input type="radio" name="userForm[showCountryNames]" value="No"  <?php if($User->showCountryNames=='No')  print "checked"; ?>>No
	</li>
	<li class="formlistfield">
		<strong>On the map:</strong>
		<input type="radio" name="userForm[showCountryNamesMap]" value="Yes" <?php if($User->showCountryNamesMap=='Yes') print "checked"; ?>>Yes
		<input type="radio" name="userForm[showCountryNamesMap]" value="No"  <?php if($User->showCountryNamesMap=='No')  print "checked"; ?>>No
	</li>
	<li class="formlistdesc">
		Instead of colored chatmessages print the countryname in front of the text and use only black text.
		Print the countryname on the map.
	</li>

	<li class="formlisttitle">Color vision deficiency setting:</li>
	<li class="formlistfield">
		<select name="userForm[colorCorrect]">
			<option value='Off'        <?php if($User->colorCorrect=='Off')         print "selected"; ?>>Off</option>
			<option value='Protanope'  <?php if($User->colorCorrect=='Protanope')   print "selected"; ?>>Protanope</option>
			<option value='Deuteranope'<?php if($User->colorCorrect=='Deuteranope') print "selected"; ?>>Deuteranope</option>
			<option value='Tritanope'  <?php if($User->colorCorrect=='Tritanope')   print "selected"; ?>>Tritanope</option>
		</select>
	</li>
	<li class="formlistdesc">
		Does enhance the colors of the maps for different types of color blindness. (Does not work for the Haven variant, sorry)
	</li>
	
<?php

if( $User->type['User'] ) {
	// If the user is registered show the list of muted users/countries:

// Patch to block Users	
	$BlockedUsers = array();
	foreach($User->getBlockUsers() as $blockUserID) {
		$BlockedUsers[] = new User($blockUserID);
	}
	if( count($BlockedUsers) > 0 ) {
		print '<li class="formlisttitle">Blocked users:</li>';
		print '<li class="formlistdesc">The users which you blocked are unable to join your games and you can\'t join their games.</li>';
		print '<li class="formlistfield"><ul>';
		foreach ($BlockedUsers as $BlockedUser) {
			print '<li>'.$BlockedUser->profile_link().' '.libHTML::blocked("profile.php?userID=".$BlockedUser->id.'&toggleBlock=on&rand='.rand(0,99999).'#block').'</li>';
		}
		print '</ul></li>';
	}
	
// End Patch
	$MutedUsers = array();
	foreach($User->getMuteUsers() as $muteUserID) {
		$MutedUsers[] = new User($muteUserID);
	}
	if( count($MutedUsers) > 0 ) {
		print '<li class="formlisttitle">Muted users:</li>';
		print '<li class="formlistdesc">The users which you muted, and are unable to send you messages.</li>';
		print '<li class="formlistfield"><ul>';
		foreach ($MutedUsers as $MutedUser) {
			print '<li>'.$MutedUser->profile_link().' '.libHTML::muted("profile.php?userID=".$MutedUser->id.'&toggleMute=on&rand='.rand(0,99999).'#mute').'</li>';
		}
		print '</ul></li>';
	}

	$MutedGames = array();
	foreach($User->getMuteCountries() as $muteGamePair) {
		list($gameID, $muteCountryID) = $muteGamePair;
		if( !isset($MutedGames[$gameID])) $MutedGames[$gameID] = array();
		$MutedGames[$gameID][] = $muteCountryID;
	}
	if( count($MutedGames) > 0 ) {
		print '<li class="formlisttitle">Muted countries:</li>';
		print '<li class="formlistdesc">The countries which you muted, and are unable to send you messages.</li>';
		print '<li class="formlistfield"><ul>';
		$LoadedVariants = array();
		foreach ($MutedGames as $gameID=>$mutedCountries) {
			list($variantID) = $DB->sql_row("SELECT variantID FROM wD_Games WHERE id=".$gameID);
			if( !isset($LoadedVariants[$variantID]))
				$LoadedVariants[$variantID] = libVariant::loadFromVariantID($variantID);
			$Game = $LoadedVariants[$variantID]->Game($gameID);
			print '<li>'.$Game->name.'<ul>';
			foreach($mutedCountries as $mutedCountryID) {
				print '<li>'.$Game->Members->ByCountryID[$mutedCountryID]->country.' '.
				libHTML::muted("board.php?gameID=".$Game->id."&msgCountryID=".$mutedCountryID."&toggleMute=".$mutedCountryID."&rand=".rand(0,99999).'#chatboxanchor').'</li>';
			}
			print '</ul></li>';
		}
		print '</ul></li>';
	}
	
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
	
	if( count($mutedThreads) > 0 ) {
		print '<li class="formlisttitle"><a name="threadmutes"></a>Muted threads:</li>';
		print '<li class="formlistdesc">The threads which you muted.</li>';
		
		$unmuteThreadID=0;
		if( isset($_GET['unmuteThreadID']) ) {
			
			$unmuteThreadID = (int)$_GET['unmuteThreadID'];
			$User->toggleThreadMute($unmuteThreadID);
			
			print '<li class="formlistfield"><strong>Thread <a class="light" href="forum.php?threadID='.$unmuteThreadID.'#'.$unmuteThreadID.
				'">#'.$unmuteThreadID.'</a> unmuted.</strong></li>';
		}
		
		print '<li class="formlistfield"><ul>';
		
		foreach ($mutedThreads as $mutedThread) {
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
	// Include the code for the countryswitch...
	require_once('locales/English/countryswitch.php');
}

/*
 * This is done in PHP because Eclipse complains about HTML syntax errors otherwise
 * because the starting <form><ul> is elsewhere
 */
print '</ul>

<div class="hr"></div>

<input type="submit" class="form-submit notice" value="Update">
</form>';


?>