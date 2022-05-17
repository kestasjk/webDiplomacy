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
require_once('objects/groupUser.php');
require_once('lib/message.php');
require_once('objects/notice.php');

//createGroup=on&type=Family&joinSelf=on

$groupId = -1;
if( $User->type['User']) 
{
	
	// Check for create group commands:
	if( isset($_REQUEST['createGroup']) && isset($_REQUEST['groupType']) && isset($_REQUEST['groupName']) && isset($_REQUEST['groupDescription']) && (!isset($_REQUEST['groupId']) || strlen($_REQUEST['groupId'])==0) )
	{
		libAuth::formToken_Valid();
		try
		{
			$groupId = Group::create($_REQUEST['groupType'], $_REQUEST['groupName'], $_REQUEST['groupDescription'], isset($_REQUEST['groupGameReference']) ? $_REQUEST['groupGameReference'] : '');
		}
		catch (Exception $e)
		{
			libHTML::error(l_t("Could not create new relationship group: ". $e->getMessage()));
		}
	}
}

if ( isset($_REQUEST['groupId']) && intval($_REQUEST['groupId'])>0 )
{
	$groupId = (int)$_REQUEST['groupId'];
}

if( $groupId === -1 )
{
	// No group specified; show an overview page for this user.

	// Ensure user records don't get locked by this query;
	$DB->sql_put("COMMIT");
	$DB->sql_put("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");  // https://stackoverflow.com/a/918092
	$groupUsers = Group::getUsers("gr.isActive = 1 AND (gr.ownerUserId = ". $User->id ." OR g.userId = ".$User->id.")");
	$DB->sql_put("COMMIT"); // This will revert back to READ COMMITTED.

	$groupUsersSorted = array(
		'Declared' => array('Verified'=>array(), 'Unverified'=>array(), 'Denied'=>array()),
		'Suspicions' => array('Verified'=>array(), 'Unverified'=>array(), 'Denied'=>array()),
		'MySuspicions' => array('Verified'=>array(), 'Unverified'=>array(), 'Denied'=>array()),
	);
	foreach($groupUsers as $groupUser)
	{
		if( $groupUser->groupType != 'Unknown' )
		{
			$relationType = 'Declared';
		}
		else if( $groupUser->userId == $User->id )
		{
			$relationType = 'Suspicions';
		}
		else
		{
			$relationType = 'MySuspicions';
		}

		if( $groupUser->isVerified() )
		{
			$verifiedType = 'Verified';
		}
		else if( $groupUser->isDenied() )
		{
			$verifiedType = 'Denied';
		}
		else
		{
			$verifiedType = 'Unverified';
		}
		$groupUsersSorted[$relationType][$verifiedType][] = $groupUser;
	}
	
	libHTML::starthtml();

	print libHTML::pageTitle('Your User Relationships',l_t('View and manage the links created between accounts that disclose outside relationships to players.'));

	print '<div>';
	print '<p>This page lets you view and manage your user relationships; confirm or deny relationships other users have created, and view the '.
		'relationships you have created for yourself and others.</p>';

	print '<div class = "profile_title">Terminology</div>';
	print '<div class = "profile_content">';
		print '<p><ul>
		<li><strong>Relationship:</strong> A connection between two users of the site that exists outside of the site, or otherwise causes an account to potentially be biased towards a certain player for reasons outside of the game.</li>
		<li><strong>Declared:</strong> A relationship that the user has acknowledged by verifying themselves.</li>
		<li><strong>Verified:</strong> A relationship that has been verified either by being declared, or through moderators investigations.</li>
		<li><strong>Unverified:</strong> A relationship that may or may not be true; currently not enough information by itself.</li>
		<li><strong>Denied relationship:</strong> A relationship that has been established that it does not exist. Either a false suspicion a moderator has investigated,
			or a mistaken link that has been denied.</li>
		<li><strong>Suspicion:</strong> A relationship created by someone not in the relationship, including others suspected of having a relationship, 
			based on the behavior of the players in a game. Can be assigned a strength based on the strength of the suspicion.</li>
		<li><strong>User/Creator/Moderator Rating:</strong> A strength assigned to a relationship: Ranges from -100 for completely deny to 100 for very strong/suspect. [Denied=-100, Doubt=-50, None=0, Weak=33, Mid=66, Strong=100]</li>
		<li><strong>Active/Inactive:</strong> A relationship can be made inactive by the creator or by a moderator, for if the relationship is not worth considering further, or has ceased.</li>
		<li><strong>Type:</strong> The nature of the relationship; ranging from accounts being run by the same person to a distant community / organizational relationship.</li>
		<li><strong>Type: Person/Family/Work/School/Other:</strong> Types of declared relationships where the relation type is known. School indicates the relationship 
			revolves around school, Person means the users involved are the same person, Other means known but not listed, etc..</li>
		<li><strong>Type: Unknown:</strong> An unknown relationship, where there is a suspicion but the actual nature of the relationship is unknown. All suspicions start
			as Unknown relationships, but moderators can change the type if the related users acknowledge and declare it.</li>
		</ul>
		</p>';
	print '</div>';
		
	print '<div class="hr"></div>';
	
	print '<h3>Declared relationships:</h3>';
	print '<div class = "profile_title">Verified - '.count($groupUsersSorted['Declared']['Verified']).' - <em>Relationships which have been verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Declared']['Verified']);
	print '</div>';

	print '<div class = "profile_title">Unverified - '.count($groupUsersSorted['Declared']['Unverified']).' - <em>Relationships which are not acknowledged/verified and are unresolved.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Declared']['Unverified']);
	print '</div>';
	print '<div class = "profile_title">Denied - '.count($groupUsersSorted['Declared']['Denied']).' - <em>Relationships which have been determined invalid.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Declared']['Denied']);
	print '</div>';

	print '<h3>Suspicions of a relationship between you and others:</h3>';
	print '<div class = "profile_title"><li>Verified - '.count($groupUsersSorted['Suspicions']['Verified']).' - <em>Suspicions which have been verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Suspicions']['Verified']);
	print '</div>';
	print '<div class = "profile_title">Unverified - '.count($groupUsersSorted['Suspicions']['Unverified']).' - <em>Suspicions which you have not verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Suspicions']['Unverified']);
	print '</div>';
	print '<div class = "profile_title">Denied - '.count($groupUsersSorted['Suspicions']['Denied']).' - <em>Relationships which have been determined invalid.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Suspicions']['Denied']);
	print '</div>';
	print '</ul>';

	print '<h3>Suspicions of a relationship between others, created by you:</h3>';
	print '<div class = "profile_title">Verified - '.count($groupUsersSorted['MySuspicions']['Verified']).' - <em>Your suspicions which have been verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['MySuspicions']['Verified']);
	print '</div>';
	print '<div class = "profile_title">Unverified - '.count($groupUsersSorted['MySuspicions']['Unverified']).' - <em>Your suspicions which have not been verified/acknowledged</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['MySuspicions']['Unverified']);
	print '</div>';
	print '<div class = "profile_title">Denied - '.count($groupUsersSorted['MySuspicions']['Denied']).' - <em>Your suspicions which have been determined invalid</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['MySuspicions']['Denied']);
	print '</div>';

	print '</div>';
	print '</div>';
	?>

	<script type="text/javascript">
	   var coll = document.getElementsByClassName("profile_title");
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
}

function TryPostReply($groupId)
{
	global $User, $DB;

	if( !isset($_REQUEST['newmessage']) ) $_REQUEST['newmessage']  = '';
	if( !isset($_REQUEST['newsubject']) ) $_REQUEST['newsubject'] = '';

	$new = array('message' => "", 'subject' => "", 'id' => -1);
	if(isset($_REQUEST['newmessage']) AND $User->type['User']
		AND ($_REQUEST['newmessage'] != "") ) {
		// We're being asked to send a message.

		$new['message'] = $DB->msg_escape($_REQUEST['newmessage']);

		$new['sendtothread'] = $groupId;

		try
		{
			libAuth::formToken_Valid();
			
			$new['id'] = Message::send( $new['sendtothread'],
				$User->id,
				$new['message'],
					'',
					'GroupDiscussion');
			header("Location: " . $_SERVER['REQUEST_URI'] . '&reply=success');
		}
		catch(Exception $e)
		{
			$new['messageproblem']=$e->getMessage();
		}

		if ( isset($new['messageproblem']) and $new['id'] != -1 )
		{
			$_REQUEST['newmessage'] = '';
			$_REQUEST['newsubject'] = '';
		}
	}
	else
	{
		/*
		* This isn't very secure, it could potentially lead to XSS attacks, but it
		* is the easiest way to un-escape a failed post without having to use a
		* UTF-8 library to replace strings
		*/
		$_REQUEST['newmessage'] = '';
		$_REQUEST['newsubject'] = '';
	}

	return $new;
}

function OutputDiscussionThread($groupId, $new)
{
	global $DB, $User;

	if( isset($new['messageproblem']) ) $messageproblem = $new['messageproblem'];

?>
<div class="thread threadID1538794 threadborder1 threadalternate1 userID23277">
	<!--<div class="leftRule message-head threadalternate1">
	<a href="profile.php?userID=23277">Octavious  (1712 <img src="images/icons/points.png" alt="D" title="webDiplomacy points">)</a><br>
		<strong><em><span class="timestamp" unixtime="1517330928">31 Jan 2018</span></em></strong> <br><a title="Mute this thread, hiding it from your forum and home page" class="light likeMessageToggleLink" href="forum.php?toggleMuteThreadID=1538794&amp;rand=88241#1538794">Mute thread</a><br>
		<a id="likeMessageToggleLink1538794" href="#" title="Give a mark of approval for this post" class="light likeMessageToggleLink" onclick="likeMessageToggle(25560,1538794,'25560_1538794_be8904232222db72a7628bef0ead3f3b'); return false;">+1</a></div><div class="message-subject"><a style="display:none;" class="messageIconForum" threadid="1538794" messageid="1539514" href="forum.php?threadID=1538794#1539514"><img src="images/icons/mail.png" alt="New" title="Unread messages!"></a> <a style="display:none;" class="participatedIconForum" threadid="1538794" href="forum.php?threadID=1538794#1538794"><img src="images/icons/star.png" alt="Participated" title="You have participated in this thread."></a> <strong>Occasionally at the top thread</strong>
	</div>-->

<div class="message-body threadalternate1">
	<div class="message-contents">
		Use this thread to clarify any details regarding this relationship, discuss the reasoning / validity behind the relationship, 
		and to discuss with the mod team.<br /><br />
	</div>
</div>
<div style="clear:both;"></div>
<?php
	// We are viewing the thread; print replies
	$replytabl = $DB->sql_tabl(
		"SELECT f.id, fromUserID, f.timeSent, f.message, u.points as points, 
				u.username as fromusername, f.toID, u.type as userType
			FROM wD_ForumMessages f
			INNER JOIN wD_Users u ON f.fromUserID = u.id
			WHERE f.toID=".$groupId." AND f.type='GroupDiscussion'
			order BY f.timeSent ASC");
	$replyswitch = 2;
	$replyNumber = 0;
	list($maxReplyID) = $DB->sql_row("SELECT MAX(id) FROM wD_ForumMessages WHERE toID=".$groupId." AND type='GroupDiscussion'");
	while($reply = $DB->tabl_hash($replytabl) )
	{
		$replyToID = $reply['toID'];
		$replyID = $reply['id'];

		$replyswitch = 3-$replyswitch;//1,2,1,2,1...
		
		print '<div class="reply replyborder'.$replyswitch.' replyalternate'.$replyswitch.'
			'.($replyNumber ? '' : 'reply-top').' userID'.$reply['fromUserID'].'">';
		$replyNumber++;

		print '<a name="'.$reply['id'].'"></a>';

		if ( $new['id'] == $reply['id'] )
		{
			print '<a name="postbox"></a>';
		}

		print '<div class="message-head replyalternate'.$replyswitch.' leftRule">';

		print '<strong>'.User::profile_link_static($reply['fromusername'], $reply['fromUserID'], $reply['userType'], $reply['points']).
			'</strong><br />';

		print libHTML::forumMessage($groupId,$reply['id']);

		print '<em>'.libTime::text($reply['timeSent']).'</em>';
		
		print '</div>';


		print '
			<div class="message-body replyalternate'.$replyswitch.'">
				<div class="message-contents" fromUserID="'.$reply['fromUserID'].'">
					'.$reply['message'].'
				</div>
			</div>

			<div style="clear:both"></div>
			</div>';
	}
	unset($replytabl, $replyfirst, $replyswitch);

	
	// Replies done, now print the footer
	print '<div class="message-foot threadalternate1">';

	if($User->type['User'] )
	{
		print '<div class="postbox">'.
			( $new['id'] != (-1) ? '' : '<a name="postbox"></a>').
			'<form class="safeForm" action="./group.php?groupId='.$groupId.'#postbox" method="post">
			<p>';
		print '<div class="hrthin"></div>';
		if ( isset($messageproblem) and $new['sendtothread'] )
		{
			print '<p class="notice">'.$messageproblem.'</p>';
		}
		print '<TEXTAREA NAME="newmessage" style="margin-bottom:5px;" ROWS="4">'.$_REQUEST['newmessage'].'</TEXTAREA><br />
				'.libAuth::formTokenHTML().'
				<input type="submit" class="form-submit" value="'.l_t('Post message').'" name="'.l_t('Post').'"></p></form>
				</div>';
	} else {
		print '<br />';
	}
	print '</div>';
	print '</div>';
}

try
{
	$GroupProfile = new Group($groupId);
}
catch (Exception $e)
{
	libHTML::error(l_t("Invalid group ID given."));
}

if( $User->type['User'] )
{
	// Check for modify group commands
	if( isset($_REQUEST['addSelf']) )
	{
		// User wants to join the group themselves
		$GroupProfile->userAdd($User, $User, isset($_REQUEST['groupUserStrength']) ? $_REQUEST['groupUserStrength'] : 100);

		$GroupProfile = new Group($groupId);
	}
	if( isset($_REQUEST['addUserId']) )
	{
		// User wants to add a user to the group
		$addingUser = new User($_REQUEST['addUserId']);
		$GroupProfile->userAdd($User, $addingUser, isset($_REQUEST['groupUserStrength']) ? $_REQUEST['groupUserStrength'] : 66);

		$GroupProfile = new Group($groupId);
	}
	/*
	This is a mandatory field on creation
	if( isset($_REQUEST['groupDescription']) && strlen($_REQUEST['groupDescription']) > 0 ) // Prevent overwriting description with blank when adding user from usercp
	{
		$GroupProfile->userSetDescription($User, $_REQUEST['groupDescription']);
		
		$GroupProfile = new Group($groupId);
	}*/
	if( isset($_REQUEST['moderatorNotes']) )
	{
		$GroupProfile->userSetModNotes($User, $_REQUEST['moderatorNotes']);
		
		$GroupProfile = new Group($groupId);
	}
	if( isset($_REQUEST['deactivate']) )
	{
		$GroupProfile->userSetActive($User, 0);

		$GroupProfile = new Group($groupId);
	}
	if( isset($_REQUEST['activate']) )
	{
		$GroupProfile->userSetActive($User, 1);

		$GroupProfile = new Group($groupId);
	}

	$weightsUpdated = false;
	foreach($GroupProfile->GroupUsers as $groupUser)
	{
		if( isset($_REQUEST['userWeighting'.$groupUser->userId]) )
		{
			$GroupProfile->userUpdateUserWeighting($User, $groupUser, $_REQUEST['userWeighting'.$groupUser->userId]);
			$weightsUpdated = true;
		}
		if( isset($_REQUEST['modWeighting'.$groupUser->userId]) )
		{
			$GroupProfile->userUpdateModWeighting($User, $groupUser, $_REQUEST['modWeighting'.$groupUser->userId]);
			$weightsUpdated = true;
		}
		if( isset($_REQUEST['ownerWeighting'.$groupUser->userId]) )
		{
			$GroupProfile->userUpdateOwnerWeighting($User, $groupUser, $_REQUEST['ownerWeighting'.$groupUser->userId]);
			$weightsUpdated = true;
		}
	}
	if( $weightsUpdated )
	{
		$GroupProfile = new Group($groupId);
	}
}

// This will check $User, perms etc:, call before starting output:	
if ( $GroupProfile->canUserComment($User) )
{
	$new = TryPostReply($GroupProfile->id);
}

libHTML::starthtml();

print libHTML::pageTitle('User Relationship Panel: #'.$GroupProfile->id.' '.$GroupProfile->name,l_t('View and manage the links created between accounts that disclose outside relationships to players.'));

print '<div>';
print '<div class = "profile-show-floating" style="margin-left:2.5%">';

// Profile Information
print '<div class = "profile-show-inside-left" style="width:45%">';
print '<div class = "comment_title" style="width:90%">';
print '<strong>Relationship Group Information:</strong> </div>';
	print '<p><ul class="profile">';

	print '<p><strong>Relationship Type:</strong> '.$GroupProfile->type.'</p>';
	print '<p><strong>Status:</strong> '.($GroupProfile->isActive ? 'Active' : 'Inactive').'</p>';



	if( $GroupProfile->ownerUserId == $User->id || $User->type['Moderator'] )
	{
		print '<form>'.
		'<input type="hidden" name="groupId" value="'.$GroupProfile->id.'" />';
		if( $GroupProfile->isActive )
		{
			print '<input type="hidden" name="deactivate" value="on" />'.
				'<input type="Submit" class="form-submit" value="Deactivate relationship" />';
		}
		else
		{
			print '<input type="hidden" name="activate" value="on" />'.
				'<input type="Submit" class="form-submit" value="Reactivate relationship" />';
		}
		print libAuth::formTokenHTML().
		'</form>';
	}

	print '</li></ul></p>';

print '</div>';

print '<div class="profile-show-inside" style="width:45%">';
print '<div class = "comment_title" style="width:90%">';
print '<strong>Creator Info / Explanation:</strong> </div>';

$owner = new User($GroupProfile->ownerUserId);

print '<p><ul>';
print '<li><strong>Creator:</strong> '.$owner->profile_link().'</li></br>';
print '<li><strong>Created:</strong> '.libTime::text($GroupProfile->timeCreated).'</li></ul></p>';

print '<p class="profileComment">"'.$GroupProfile->description.'"</p>';

print '</div></br>';
print '</div>';

print '</div>';

print '<div class="hr"></div>';
// Show moderator information
if ( $User->type['Moderator'] )
{	
	print '<div class = "profile-show">';

	print '<div class = "profile_title"> Moderator Info</div>';
	print '<div class = "profile_content_show">';

			$modActions=array();

			$modActions[] = libHTML::admincpType('Group',$GroupProfile->id);
			$modActions[] = libHTML::admincp('groupChangeOwner',array('groupId'=>$GroupProfile->id), 'Change group owner');
			$modActions[] = libHTML::admincp('groupChangeOwner',array('groupId'=>$GroupProfile->id), 'Change group type');

			$modActions[] = '<a href="admincp.php?tab=Multi-accounts&aGroupID='.$GroupProfile->id.'" class="light">Enter multi-account finder</a>';

			if($modActions)
			{
				print '<p class="notice">'.implode(' - ', $modActions).'</p>';
			}
			print '</div>';
			
	print '<div class = "profile_content_show">';
	print '<div class = "comment_title" style="width:90%">';
	
	print 'Notes: </div>';
	print '<div class = "comment_content">';
	print '<form><input type="hidden" name="groupId" value="'.$GroupProfile->id.'" />';
	print '<textarea name="moderatorNotes" ROWS=4>'.$GroupProfile->moderatorNotes.'</textarea>';
	print '<input type="Submit" class="form-submit" value="Update notes" />';
	print libAuth::formTokenHTML();
	print '</form>';
	print '</div>';
	
	print '</div></div>';
	print '<div class="hr"></div>';
}

print '<form>';
print '<input type="hidden" name="groupId" value="'.$GroupProfile->id.'" />';
print '<table class="rrInfo">';
print $GroupProfile->outputUserTable($User);
print '</table>';
print '<input id="submitRatingUpdates" type="Submit" class="form-submit" value="Submit rating updates" />';
print '</form>';
print '</div>';

if ( $GroupProfile->canUserComment($User) )
{
	print '<div class="content">';
	print '<h2>Discussion</h2>';
	OutputDiscussionThread($GroupProfile->id, $new);
}
print '</div>';
/*
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
*/
libHTML::footer();