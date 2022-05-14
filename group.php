<?php
/*
    Copyright (C) 2004-2022 Kestas J. Kuliukas

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
require_once('objects/group.php');

if ( isset($_REQUEST['groupID']) && intval($_REQUEST['groupID'])>0 )
{
	$groupID = (int)$_REQUEST['groupID'];
}
else
{
	libHTML::error("No group ID specified.");
}

try
{
	$GroupProfile = new Group($groupID);
}
catch (Exception $e)
{
	libHTML::error("Invalid group ID given.");
}

libHTML::starthtml();

print '<div class="content">';
print '<div>';
print '<h2 class = "profileUsername">'.$GroupProfile->name.'</h2>';

// Show moderator information
if ( $User->type['Moderator'] )
{	
	print '<div class = "profile-show">';

	print '<div class = "profile_title"> Moderator Info</div>';
	print '<div class = "profile_content_show">';

	if( $User->type['Moderator'] )
	{
			$modActions=array();

			$modActions[] = libHTML::admincpType('Group',$GroupProfile->id);
			$modActions[] = libHTML::admincp('groupChangeOwner',array('groupID'=>$UserProfile->id), 'Change group owner');

			$modActions[] = '<a href="admincp.php?tab=Multi-accounts&aGroupID='.$GroupProfile->id.'" class="light">Enter multi-account finder</a>';

			if($modActions)
			{
				print '<p class="notice">'.implode(' - ', $modActions).'</p>';
			}

			print '</li><li>';
			print libHTML::admincp('createUserSilence',array('userID'=>$UserProfile->id,'reason'=>''),'Silence user');
			print '</li></ul></p>';
			print '</div>';
	}
	print '</div></div></br>';
}

print '<div class = "profile-show-floating">';

// Profile Information
print '<div class = "profile-show-inside-left">';
	print '<strong>Group Information</strong>';
	print '<p><ul class="profile">';

	print '<p><strong>'.$GroupProfile->type.'</strong></p>';

	if ( $GroupProfile->description )
	{
		print '<li><div class = "comment_title" style="width:90%">';
		print '<strong>Group Info:</strong> </div></li>';

		print '<div class = "comment_content">';
		print '<p class="profileComment">"'.$GroupProfile->description.'"</p>';
		print '</div></br>';
	}

	print '<li><strong>Created:</strong> '.$GroupProfile->timeCreated.'</li></br>';
	$owner = new User($GroupProfile->ownerId);
	print '<li><strong>Owner:</strong> '.$owner->profile_link().'</li></br>';

	print '</li></ul></p>';
print '</div></br>';

print '<h3>Members</h3>';
print '<ul>';
// User group members
foreach($GroupProfile->Members as $member)
{
	print $member->showLink();
}
print '</ul>';

// User group comments

if( $GroupProfile->canViewMessages($User) )
{
	require_once('lib/message.php');
	print '<h3>Messages</h3>';
	if( $GroupProfile->canSendMessages($User) )
	{
		try
		{
			$message=notice::sendPMs();
		}
		catch(Exception $e)
		{
			$message=$e->getMessage();
		}	
	}

	if ( $message )
		print '<p class="notice">'.$message.'</p>';

	$tabl=$DB->sql_tabl("SELECT n.*
		FROM wD_Notices n
		WHERE n.fromId=".$GroupProfile->id." AND n.type='Group'
		ORDER BY n.timeSent DESC ");
	while($hash=$DB->tabl_hash($tabl))
	{
		$notices[] = new notice($hash);
	}
	if(!count($notices))
	{
		print '<div class="hr"></div>';
		print '<p class="notice">'.l_t('No group messages yet.').'</p>';
		return;
	}

	print '<div class="hr"></div>';

	foreach($notices as $notice)
	{
		print $notice->viewedSplitter();

		print $notice->html();
	}
}

libHTML::footer();
?>
