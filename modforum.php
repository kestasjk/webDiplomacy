<?php

/**
 * @package Base
 */
require_once('header.php');

// If we have switched to another user switch back while in the mod forum to avoid confusion
if( isset($User) && defined('AdminUserSwitch') && AdminUserSwitch != $User->id)
	$User = new User(AdminUserSwitch);

require_once('modforum/libPager.php');
require_once('modforum/libMessage.php');

// Set different tabs for admins to see...
$tabs = array(
	'Open'     =>array (l_t('Unresolved reports'),' AND (status = "New" OR status = "Open")' ),
	'Assigned' =>array (l_t('My reports'),' AND status = "Open" AND (assigned = "'.$User->id.'" OR fromUserID = "'.$User->id.'")' ),
	'Resolved' =>array (l_t('Resolved reports'),' AND status = "Resolved"'),
	'Bugs'     =>array (l_t('Bugs to take care of'),' AND status = "Bugs"'),
	'Sticky'   =>array (l_t('Internal discussions'),' AND status = "Sticky"'),
	'Deleted'   =>array (l_t('Deleted reports'),' AND status = "Deleted"'),
);

$tab = 'Open';
$tabNames = array_keys($tabs);

if( isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], $tabNames) )
	$tab = $_SESSION['modForumTab'] = $_REQUEST['tab'];
elseif( isset($_SESSION['modForumTab']) && in_array($_SESSION['modForumTab'], $tabNames) )
	$tab = $_SESSION['modForumTab'];
else
	$tab = 'Open';

// End Modforumtabs	
	
$postboxopen = true;
$viewthread = false;

if ( isset($_REQUEST['threadID']) )
	$_REQUEST['viewthread'] = (int) $_REQUEST['threadID'];

if( $User->type['User'] AND isset($_REQUEST['postboxopen'])) {
	$postboxopen = (bool) $_REQUEST['postboxopen'];

} elseif (isset($_REQUEST['viewthread'])) {
	$viewthread = (int) $_REQUEST['viewthread'];

} elseif (isset($_SESSION['viewthread'])) {
	$viewthread = (int) $_SESSION['viewthread'];
}

if( !$viewthread) $viewthread=false;

if ($User->type['Moderator'])
	list($ForumThreads) = $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE type='ThreadStart' ". $tabs[$tab][1] );
else
	list($ForumThreads) = $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE type='ThreadStart' AND fromUserID='".$User->id."'");
$forumPager = new PagerForum($ForumThreads);

if( !isset($_SESSION['lastSeenModForum']) || $_SESSION['lastSeenModForum'] < $User->timeLastSessionEnded )
{
	$_SESSION['lastSeenModForum']=$User->timeLastSessionEnded;
}

if( !isset($_REQUEST['page']) && isset($_REQUEST['viewthread']) && $viewthread )
{
	unset($orderIndex);
	list($orderIndex, $threadStatus) = $DB->sql_row("SELECT b.latestReplySent, status FROM wD_ModForumMessages b WHERE b.id = ".$viewthread);
	if(!isset($orderIndex) || !$orderIndex)
		libHTML::notice('Thread not found', "The thread you requested wasn't found.");

	switch ($threadStatus) {
		case 'Resolved': $tab = 'Resolved'; break;
		case 'Bugs'    : $tab = 'Bugs'; break;
		case 'Sticky'  : $tab = 'Sticky'; break;
		case 'Deleted' : $tab = 'Deleted'; break;
		default        : $tab = 'Open'; break;	
	}
	
	list($position) = $DB->sql_row(
		"SELECT COUNT(*)-1 FROM wD_ModForumMessages a WHERE a.latestReplySent >= ".$orderIndex." AND a.type='ThreadStart' ". ($User->type['Moderator'] ? $tabs[$tab][1] : '')
	);

	$forumPager->currentPage = $forumPager->pageCount - floor($position/PagerForum::$defaultPostsPerPage);
}

if (isset($_REQUEST['toggleStatus']) && isset($_REQUEST['actiontargetthread']) && $User->type['Moderator'])
{
	$actiontargetthread = (int)$_REQUEST['actiontargetthread'];

	list($status)=$DB->sql_row("SELECT status FROM wD_ModForumMessages WHERE id = ".$actiontargetthread);
	$newstatus = $_REQUEST['toggleStatus'];

	if ($newstatus != $status)
	{
		$DB->sql_put("UPDATE wD_ModForumMessages SET status='".$newstatus."' WHERE id = ".$actiontargetthread);
		if( $newstatus == "Deleted" )
			$DB->sql_put("UPDATE wD_ModForumMessages SET isUserMustReply = 0, assigned=".$User->id." WHERE id = ".$actiontargetthread);
	}
}

if (isset($_REQUEST['setAssigned']) && $User->type['Moderator'])
{
	$DB->sql_put("UPDATE wD_ModForumMessages SET assigned='".(int)$_REQUEST['setAssigned']."' WHERE id = ".$viewthread);
}

if( !isset($_REQUEST['newmessage']) ) $_REQUEST['newmessage']  = '';
if( !isset($_REQUEST['newsubject']) ) $_REQUEST['newsubject'] = '';

$requestTypes = array(
	'Pause/Unpause request',
	'Rules broken',
	'Forum issue',
	'Other'
);

$new = array('message' => "", 'subject' => "", 'id' => -1);
if( $User->type['User'] && isset($_REQUEST['ThankMod']) && $viewthread )
{
	$DB->sql_put("UPDATE wD_ModForumMessages SET isThanked = 1 WHERE id = ".$viewthread." AND fromUserId = ".$User->id );
}

if(isset($_REQUEST['newmessage']) AND $User->type['User'] AND ($_REQUEST['newmessage'] != "") ) {
	// We're being asked to send a message.

	$new['message'] = $DB->msg_escape($_REQUEST['newmessage']);

	if( isset($_REQUEST['newsubject']) )
	{
		$new['subject'] = $DB->escape($_REQUEST['newsubject']);
	}

	$new['sendtothread'] = $viewthread;

	if( isset($_SESSION['lastPostText']) && $_SESSION['lastPostText'] == $new['message'] && !$User->type['Moderator'])
	{
		$messageproblem = "You are posting the same message again, please don't post repeat messages.";
		$postboxopen = !$new['sendtothread'];
	}
	elseif( isset($_SESSION['lastPostTime']) && $_SESSION['lastPostTime'] > (time()-20) && !$User->type['Moderator']
		&& ! ( $new['sendtothread'] && isset($_SESSION['lastPostType']) && $_SESSION['lastPostType']=='ThreadStart' ) )
	{
		$messageproblem = "You are posting too frequently, please slow down.";
		$postboxopen = !$new['sendtothread'];
	}
	else
	{		
		if( isset($_REQUEST['fromUserID']) && $User->type['Admin'] && (int)$_REQUEST['fromUserID'] > 4)
			$fromUserID=(int)$_REQUEST['fromUserID'];
		else
			$fromUserID=$User->id;
	
		if(!$new['sendtothread']) // New thread to the forum
		{
			if ( 4 <= substr_count($new['message'], '<br />') )
			{
				$messageproblem = "Too many lines in this message; ".
					"please write a summary of the message in less than 4 ".
					"lines and write the rest of the message as a response.";
				$postboxopen = true;
			}
			elseif( 500 < strlen($new['message']) )
			{
				$messageproblem = "Too many characters in this message; ".
					"please write a summary of the message in less than 500 ".
					"characters and write the rest of the message as a response.";
				$postboxopen = true;
			}
			elseif( empty($new['subject']) )
			{
				$messageproblem = "You haven't given a subject.";
				$postboxopen = true;
			}
			elseif( strlen($new['subject'])>=90 )
			{
				$messageproblem = "Subject is too long, please keep it within 90 characters.";
				$postboxopen = true;
			}
			else
			{
				try
				{
					$subjectWords = explode(' ', $new['subject']);
					foreach( $subjectWords as $subjectWord )
						if( strlen($subjectWord)> 25 )
							throw new Exception("A word in the subject, '".$subjectWord."' is longer than 25 ".
								"characters, please choose a subject with normal words.");

					$requestType = '';
					if( in_array($_REQUEST['newrequesttype'], $requestTypes, true) )
					{
						$requestType = $_REQUEST['newrequesttype'];
					}

					$new['id'] = ModForumMessage::send(0,
						$fromUserID,
						$new['message'],
						$new['subject'],
						'ThreadStart',
						isset($_REQUEST['forceReply']),
						$requestType,
						(isset($_REQUEST['newgameid']) ? (int)$_REQUEST['newgameid'] : null)
					);

					$_SESSION['lastPostText']=$new['message'];
					$_SESSION['lastPostTime']=time();
					$_SESSION['lastPostType']='ThreadStart';

					$messageproblem = "Thread posted sucessfully.";
					$new['message'] = "";
					$new['subject'] = "";
					$postboxopen = false;

					$viewthread = $new['id'];
				}
				catch(Exception $e)
				{
					$messageproblem=$e->getMessage();
					$postboxopen = true;
				}
			}
		}
		else
		{
			// To a thread
			$threadDetails = $DB->sql_hash(
				"SELECT f.id, f.latestReplySent, f.assigned, u.type as userType
				FROM wD_ModForumMessages f 
				INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
				WHERE f.id=".$new['sendtothread']."
					AND f.type='ThreadStart'");

			unset($messageproblem);
			
			if( isset($threadDetails['id']) && !isset($messageproblem) )
			{
				// It's being sent to an existing, non-silenced / dated thread.
				try
				{

					$new['id'] = ModForumMessage::send( $new['sendtothread'],
						$fromUserID,
						$new['message'],
							'',
							'ThreadReply',
							isset($_REQUEST['forceReply'])
							);

					$_SESSION['lastPostText']=$new['message'];
					$_SESSION['lastPostTime']=time();
					$_SESSION['lastPostType']='ThreadReply';

					$messageproblem="Reply posted sucessfully.";
					$new['message']=""; $new['subject']="";
					
					if ($threadDetails['assigned'] == 0 
							&& $User->type['Moderator'] 
							&& strpos($threadDetails['userType'],'Moderator')===false)
						$DB->sql_put('UPDATE wD_ModForumMessages SET assigned = "'.$User->id.'" WHERE id='.$threadDetails['id']);
					
				}
				catch(Exception $e)
				{
					$messageproblem=$e->getMessage();
				}
			}
			else
			{
				$messageproblem="The thread you attempted to reply to doesn't exist.";
			}
			
			unset($threadDetails);
		}
	}

	if ( isset($messageproblem) and $new['id'] != -1 )
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

$_SESSION['viewthread'] = $viewthread;
$_SESSION['lastSeenModForum']=time();

libHTML::starthtml();

/*
SELECT * FROM wD_ModForumMessages WHERE type = 'ThreadStart' AND fromUserID = 10 AND (isUserRead = 0 OR (isUserReplied = 0 AND isUserMustReply = 1))
SELECT * FROM wD_ModForumMessages WHERE type = 'ThreadStart' AND assigned = 10 AND isModRead = 0
*/
print libHTML::pageTitle(l_t('Moderator / Help forum'),l_t('Get help with any problems you are having by contacting the moderator team.'));

print '<p>
		Requests/queries you post here are confidential and can only be viewed by yourself and the moderators.<br /><br />
		
		You will be redirected here as soon as a moderator responds to your request. If a moderator has requested 
		a response you must reply before continuing to other pages.<br /><br />

		Please remember moderators are volunteers and may not be able to respond immediately; thanks for your patience.
	</p>';
print '<div class="hr"></div>';
print '<h4>Moderator team stats / activity:</h4>';
$modStatsTabl = $DB->sql_tabl("SELECT 
	COALESCE(u.id,0) id, 
	COALESCE(u.username,'Unassigned') username, 
	COALESCE(u.timeJoined,0) timeJoined, 
	COALESCE(u.timeLastSessionEnded, 0) timeLastSessionEnded, 
	COALESCE(u.type,'Moderator') type, 
	COALESCE(u.points, 100) points, 
	countTotal,
	countOpen, 
	countResolved, 
	countThanked, 
	countModRead, 
	countModReplies, 
	countRead, 
	countReplies, 
	countMustReply,
	latestReplySentTime, 
	countMessages
	FROM (
		SELECT fm.assigned,
			COUNT(*) countTotal,
			SUM(IF(status = 'Resolved', 0, 1)) countOpen, 
			SUM(IF(status = 'Resolved', 1, 0)) countResolved, 
			SUM(isThanked) countThanked, 
			SUM(isModRead) countModRead, 
			SUM(isModReplied) countModReplies, 
			SUM(isUserRead) countRead, 
			SUM(isUserReplied) countReplies, 
			SUM(isUserMustReply) countMustReply,
			MAX(COALESCE(latestReplySentTime,0)) latestReplySentTime, 
			SUM(replies) countMessages
		FROM wD_ModForumMessages fm
		WHERE fm.type='ThreadStart'
		GROUP BY fm.assigned
	) fm
	LEFT JOIN wD_Users u ON u.id = fm.assigned
	ORDER BY timeLastSessionEnded DESC");
print '<table class="hof">
	<tr class="hof">
	<th class="hof">Username</th>
	<th class="hof">Joined</th>
	<th class="hof">Last seen</th>
	<th class="hof">Threads</th>
	<th class="hof">Open</th>
	<th class="hof">Resolved</th>
	<th class="hof">Thanked</th>
	<th class="hof">Replies</th>
	<th class="hof">Latest reply</th>
</tr>';
while($row = $DB->tabl_hash($modStatsTabl))
{
	print '<tr class="hof">';
	print '<td class="hof">'.
		User::profile_link_static($row['username'], $row['id'], $row['type'], $row['points']).
		'</td>';
	print '<td class="hof">'.($row['timeJoined'] == 0 ? "N/A" : date('Y-m-d',$row['timeJoined'])).'</td>';
	print '<td class="hof">'.($row['timeLastSessionEnded'] == 0 ? "N/A" : date('Y-m-d',$row['timeLastSessionEnded'])).'</td>';
	print '<td class="hof">'.$row['countTotal'].'</td>';
	print '<td class="hof">'.$row['countOpen'].'</td>';
	print '<td class="hof">'.$row['countResolved'].'</td>';
	print '<td class="hof">'.$row['countThanked'].'</td>';
	print '<td class="hof">'.$row['countMessages'].'</td>';
	print '<td class="hof">'.($row['latestReplySentTime'] == 0 ? "N/A" : date('Y-m-d',$row['latestReplySentTime'])).'</td>';
	print '</tr>';
}
print '</table>';

print '<div class="hr"></div>';

// More tabs for admins
if( $User->type['Moderator'] )
{
	print '<div class="gamelistings-tabs">';
	foreach($tabs as $tabChoice=>$tabParams)
	{
		list ($tabTitle, $sql) = $tabParams;
		print '<a title="'.$tabTitle.'" alt="'.l_t($tabChoice).'" href="modforum.php?tab='.$tabChoice;
		
		print ($tab == $tabChoice ? '" class="current"' : '"');
		
		print '>'.l_t($tabChoice).' ';
		
		if ($tab != $tabChoice)
		{
			$tabl = $DB->sql_tabl("SELECT id, latestReplySent FROM wD_ModForumMessages WHERE type = 'ThreadStart' ".$sql." ORDER BY latestReplySent DESC LIMIT 5");
			while( list ($id, $latestReplySent) = $DB->tabl_row($tabl) )
			print ' <img style="display:none;" class="messageIconForum" threadID="'.$id.'" messageID="'.$latestReplySent.'"'.
					'src="'.l_s('images/icons/mail.png').'" alt="'.l_t('New').'" title="'.l_t('Unread messages!').'">';
		}
		print '</a>';
	}
	print '</div>';
}
	
if(isset($messageproblem) and !$new['sendtothread']) {
	print '<p class="notice"><a name="postbox"></a>'.$messageproblem.'</p>';
	libHTML::pagebreak();
}

print '<div class="forum"><a name="forum"></a>';

print '
	<div id="forumPostbox" style="'.($postboxopen?'':libHTML::$hideStyle).'" class="thread threadalternate1 threadborder1">
	<div style="margin:0;padding:0">
	<div class="message-head">
		<strong>Start a new discussion in the mod forum</strong>
		</div>
	<div class="message-subject"><strong>Post a new thread</strong></div>
	<div style="clear:both;"></div>
	</div>
	<div class="hr"></div>';
	
if( $User->isSilenced() )
{
	print '<div class="message-body postbox" style="padding-top:0; padding-left:auto; padding-right:auto">';
	
	print '<p>Cannot post due to a temporary silence:'.$User->getActiveSilence()->toString().'</p>
			<div class="hr"></div>
			<p>Please see <a class="light" href="rules.php#silenceInfo">our silenced section</a>
			for info on how to dispute it or get the length reduced.</p>';
	
	print '</div>';
}
else
{
	if( isset($_REQUEST['fromGameID']) ) 
	{
		// User is posted a thread referencing a game
		$gameSelection = '<input type="hidden" name="newgameid" value="'.$_REQUEST['fromGameID'].'" /> #'.$_REQUEST['fromGameID'].'<br /><br />';
	}
	else
	{
		$gameSelection = '<select name="newgameid">';
		// Let the user choose a game they are in that they are referencing.
		$tabl = $DB->sql_tabl("SELECT g.id, g.name FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ".$User->id." AND g.gameOver='No' ORDER BY name");
		$gameSelection .= '<option value=""></option>';
		while($row = $DB->tabl_hash($tabl))
		{
			$gameSelection .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
		}
		$gameSelection .= '</select><br />';
	}
	$requestOptions = '';
	foreach($requestTypes as $requestType)
	{
		$requestOptions .= '<option name="'.$requestType.'">'.$requestType.'</a>';
	}
	print '
	<div class="message-body threadalternate1 postboxadvice"><br />
		'.l_t('If you are posting a question please <strong>check the <a href="faq.php">FAQ</a></strong> before posting.').'<br />
		'.l_t('If your message is long you may need to write a summary message, and add the full message as a reply.').'	
	</div>
	<div class="hr" ></div>

	<div class="message-body postbox" style="padding-top:0; padding-left:auto; padding-right:auto">

		<form class="safeForm" action="modforum.php#postbox" method="post"><p>
		<div style="text-align:left; width:80%; margin-left:auto; margin-right:auto; float:middle">
		<strong>Subject:</strong><br />
		<input style="width:100%" maxLength=2000 size=60 name="newsubject" value="'.$_REQUEST['newsubject'].'"><br /><br />
		<strong>Game:</strong></br />
		'.$gameSelection.'</br />
		<strong>Request type:</strong></br />
		<select name="newrequesttype">
			<option name=""></option>
			'.$requestOptions.'
		</select></br />
		<strong>Message:</strong><br />
		<TEXTAREA NAME="newmessage" ROWS="6" style="width:100%">'.$_REQUEST['newmessage'].'</TEXTAREA>
		<input type="hidden" name="viewthread" value="0" />
		</div>
		<br />

		<input type="submit" class="form-submit" value="Post new thread" name="Post">
		'.($User->type['Admin']?' - UserID: <input type="text" size=4 value="" name="fromUserID">':'').'
		</p></form>
	</div>';
}

print '<div class="hr"></div>
<div class="message-foot threadalternate1">
	<form action="modforum.php" method="get" onsubmit="$(\'forumPostbox\').hide(); $(\'forumOpenPostbox\').show(); return false;">
		<input type="hidden" name="postboxopen" value="0" />
		<input type="submit" class="form-submit" value="Cancel" />
	</form>
</div>
</div>';

print '<div>';
print $forumPager->html();

if($User->type['User'] )
{
	print '<div id="forumOpenPostbox" style="'.($postboxopen?libHTML::$hideStyle:'').'" >
		<form action="modforum.php#postbox" method="get" onsubmit="$(\'forumPostbox\').show(); $(\'forumOpenPostbox\').hide(); return false;">
		<p style="padding:5px;">
			<input type="hidden" name="postboxopen" value="1" />
			<input type="hidden" name="page" value="'.$forumPager->pageCount.'" />
			<input type="submit" class="form-submit" value="New thread" />
		</p>
	</form>
	</div>';
}
print '<div style="clear:both;"> </div>
	</div>
	';

$tabl = $DB->sql_tabl("SELECT
	f.id, f.fromUserID, f.timeSent, f.message, f.subject, f.replies,
		u.username as fromusername, u.points as points, f.latestReplySent, u.type as userType, 
		f.status as status,
		f.assigned, u2.username as modname, 
		f.gameID, f.requestType, f.gameTurn, f.isUserRead, f.isModRead, f.isUserReplied, f.isModReplied, f.isUserMustReply, f.isThanked
	FROM wD_ModForumMessages f
	INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
	LEFT JOIN wD_Users u2 ON ( f.assigned = u2.id )
	WHERE f.type = 'ThreadStart'
	".($User->type['Moderator'] ? $tabs[$tab][1] : " AND fromUserID = '".$User->id."'")."
	ORDER BY f.latestReplySent DESC
	".$forumPager->SQLLimit());

/*
 * If it's a new post, jump to it
 *
 */
$switch = 2;
while( $message = $DB->tabl_hash($tabl) )
{
	print '<div class="hr userID'.$message['fromUserID'].' threadID'.$message['id'].'"></div>'; // Add the userID and threadID so muted users/threads dont create lines where their threads were

	$switch = 3-$switch; // 1,2,1,2,1,2...

	$messageAnchor = '<a name="'.($new['id'] == $message['id'] ? 'postbox' : $message['id']).'"></a>';

	print '<div class="thread threadID'.$message['id'].' threadborder'.$switch.' threadalternate'.$switch.' userID'.$message['fromUserID'].'">';

	// New or archived posts anchor to the start of the thread
	if ( $User->timeLastSessionEnded < $message['timeSent'] )
	{
		print $messageAnchor;
		$messageAnchor = '';
	}

	if ( $message['replies'] == 0 )
	{
		print $messageAnchor;
	}

	// Check for mutes first, before continuing
	$deleteLink='';
	if ($User->type['Moderator'] && $tab != 'Deleted' && ($message['assigned'] == $User->id || $message['assigned'] == 0 || strpos($message['userType'],'Moderator')!==false || $User->type['Admin']))
	{
		$deleteURL = 'modforum.php?actiontargetthread='.$message['id'].'&amp;toggleStatus=Deleted';
		$deleteLink = ' <br /><a title="Move this thread to trash section" class="light likeMessageToggleLink" href="'.$deleteURL.'">Delete thread</a>';
	}

	// Check if this thread needs an unread / alert icon
	$newMessageText = '';
	$newAlertText = '';
	if( !$message['isModRead'] && $User->type['Moderator'] && (!$message['assigned'] || $message['assigned']==$User->id) )
		$newMessageText = "Unread user message";
	else if( !$message['isUserRead'] && $User->type['User'] && $message['fromUserID']==$User->id )
		$newMessageText = "Unread mod response";
	if( $User->type['Moderator'] && !$message['assigned'] )
		$newAlertText = "Request unassigned";
	else if ( $message['isUserMustReply'] && $message['fromUserID']==$User->id && !$message['isUserReplied'] )
		$newAlertText = "Reply required";
	
		
	print '<div class="leftRule message-head threadalternate'.$switch.'">

		<a href="profile.php?userID='.$message['fromUserID'].'">'.$message['fromusername'].
			' '.libHTML::loggedOn($message['fromUserID']).
				' ('.$message['points'].' '.libHTML::points().User::typeIcon($message['userType']).')</a>'.
			'<br />
			<strong><em>'.libTime::text($message['timeSent']).'</em></strong>'.$deleteLink.'<br />
			'.($newMessageText != '' ? '<img src="'.l_s('images/icons/mail.png').'" alt="'.l_t($newMessageText).'" title="'.l_t($newMessageText).'" /> ' : '')
			.($newAlertText != '' ? '<img src="'.l_s('images/icons/alert.png').'" alt="'.l_t($newAlertText).'" title="'.l_t($newAlertText).'" /> ' : '').'
		</div>';
	
	
	if ($message['status']== "New")
		print '<div class="message-subject" style="color:#990000;">';
	elseif ($message['status']== "Resolved" || ($message['status']== "Deleted" && $User->type['Moderator'] /* Hide deletion state to requester */))
		print '<div class="message-subject" style="color:#888888;">';
	else
		print '<div class="message-subject">';
	
	if( $message['isThanked'])
	{
		print '<img src="images/icons/star.png" title="The requester says +1 / thanks" alt="The requester says +1 / thanks" /> ';
	}

	print libHTML::forumMessage($message['id'],$message['latestReplySent']);
	print libHTML::forumParticipated($message['id']);

	
	if (isset($silence) && $silence->isEnabled())
	{
		$postLockedReason = "This thread has been locked; ".$silence->reason;
	}
	elseif( $User->isSilenced() )
	{
		$postLockedReason = "This account has been silenced; ".$User->getActiveSilence()->reason;
	}
	else
	{
		unset($postLockedReason);
	}
	
	if( isset($postLockedReason) ) {
		print '<img src="images/icons/lock.png" title="'.$postLockedReason.'" /> ';
	}
	
	if ($message['status']== "Resolved")
		print '<strong>'.$message['subject'].' (resolved)</strong>';
	elseif ($message['status']== "New")
		print '<strong>'.$message['subject'].' (new)</strong>';
	elseif ($message['status']== "Deleted" && $User->type['Moderator'])
		print '<strong>'.$message['subject'].' (deleted)</strong>';
	else
		print '<strong>'.$message['subject'].'</strong>';

	if( $message['requestType'] != null ) {
		print ' <strong>Type:</strong> '.$message['requestType'];
	}
		
	if ($message['modname'] != "")
		print '<strong> - assigned'.($User->type['Moderator'] ? ' to: '.$message['modname'] : '').'</strong>';
	elseif($message['status']!= "New" && strpos($message['userType'],'Moderator')===false)
		print '<strong> - in internal discussion.</strong>';
	
	print '</div>
		
		<div class="message-body threadalternate'.$switch.'">
			<div class="message-contents" fromUserID="'.$message['fromUserID'].'">
				'.$message['message'].'
			</div>
		</div>
	<div style="clear:both;"></div>';

	if( $message['id'] == $viewthread )
	{
		if( $message['gameID'] != null )
		{
			if( $User->type['Moderator'] )
			{
				// If we're a moderator print extra info about the game and user
				require_once(l_r('objects/game.php'));
				require_once(l_r('gamepanel/game.php'));
				$Variant=libVariant::loadFromGameID($message['gameID']);
				$G = $Variant->panelGame($message['gameID']);
				print $G->summary(false);
				
				print ' <strong>Posted during turn:</strong> '.$message['gameTurn'].' - '.$Variant->turnAsDate($message['gameTurn']);
			}
			else
				print ' <a href="board.php?gameID='.$message['gameID'].'">Link to game</a> ';
		}

		$replyToID = $message['id']; // If there are no replies this will ensure the thread is still marked as read
		$replyID = $message['id'];

		if ( $message['replies'] > 50 )
		{
			$threadPager = new pagerThread( $message['replies'],$message['id']);
			$threadPager->pagerBar('threadPager');
		}
		// We are viewing the thread; print replies
		$replytabl = $DB->sql_tabl(
			"SELECT f.id, fromUserID, f.timeSent, f.message, u.points as points, 
					u.username as fromusername, f.toID, u.type as userType, 
					f.isUserRead, f.isModRead, f.isUserReplied, f.isModReplied, f.isUserMustReply
				FROM wD_ModForumMessages f
				INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
				WHERE f.toID=".$message['id']." AND f.type='ThreadReply'
				GROUP BY f.id
				ORDER BY f.timeSent ASC
				".(isset($threadPager)?$threadPager->SQLLimit():''));

		if( $message['assigned'] && $User->id == $message['assigned'] && $message['isModRead'] == 0 )
		{
			$DB->sql_put("UPDATE wD_ModForumMessages SET isModRead=1 WHERE id=".$message['id']." OR toID=".$message['id']);
		}
		else if( $message['fromUserID'] && $User->id == $message['fromUserID'] && $message['isUserRead'] == 0 )
		{
			$DB->sql_put("UPDATE wD_ModForumMessages SET isUserRead=1 WHERE id=".$message['id']." OR toID=".$message['id']);
		}

		$replyswitch = 2;
		$replyNumber = 0;
		$replyID = 0;
		list($maxReplyID) = $DB->sql_row("SELECT MAX(id) FROM wD_ModForumMessages WHERE toID=".$message['id']." AND type='ThreadReply'");
		while($reply = $DB->tabl_hash($replytabl) )
		{
			$replyToID = $reply['toID'];
			if ( $replyID < $reply['id'] )
				$replyID = $reply['id'];

			$replyswitch = 3-$replyswitch;//1,2,1,2,1...
			
			print '<div class="reply replyborder'.$replyswitch.' replyalternate'.$replyswitch.'
				'.($replyNumber ? '' : 'reply-top').' userID'.$reply['fromUserID'].'"
				>';
			$replyNumber++;

			print '<a name="'.$reply['id'].'"></a>';

			if ( $new['id'] == $reply['id'] )
			{
				print '<a name="postbox"></a>';
				$messageAnchor = '';
			}
			elseif ( $User->timeLastSessionEnded < $reply['timeSent'] )
			{
				print $messageAnchor;
				$messageAnchor = '';
			}
			elseif ( $reply['id'] == $maxReplyID )
			{
				print $messageAnchor;
				$messageAnchor = '';
			}

			print '<div class="message-head replyalternate'.$replyswitch.' leftRule">';

			print '<strong><a href="profile.php?userID='.$reply['fromUserID'].'">'.$reply['fromusername'].' '.
					libHTML::loggedOn($reply['fromUserID']).' ('.$reply['points'].' '.libHTML::points().User::typeIcon($reply['userType']).')';
			
			print '</a></strong><br />';

			print libHTML::forumMessage($message['id'],$reply['id']);

			// Check if this reply needs an unread / alert icon
			$newReplyMessageText = '';
			$newReplyAlertText = '';
			if( !$reply['isModRead'] && $User->type['Moderator'] && (!$message['assigned'] || $message['assigned']==$User->id) )
				$newReplyMessageText = "Unread user message";
			else if( !$reply['isUserRead'] && $User->type['User'] && $reply['fromUserID']==$User->id )
				$newReplyMessageText = "Unread mod response";
			if( $User->type['Moderator'] && !$message['assigned'] )
				$newReplyAlertText = "Request unassigned";
			else if ( $reply['isUserMustReply'] && $reply['fromUserID']==$User->id && !$reply['isUserReplied'] )
				$newReplyAlertText = "Reply required";
			print '<em>'.libTime::text($reply['timeSent']).'</em><br />
			'.($newReplyMessageText != '' ? '<img src="'.l_s('images/icons/mail.png').'" alt="'.l_t($newReplyMessageText).'" title="'.l_t($newReplyMessageText).'" /> ' : '')
			.($newReplyAlertText != '' ? '<img src="'.l_s('images/icons/alert.png').'" alt="'.l_t($newReplyAlertText).'" title="'.l_t($newReplyAlertText).'" /> ' : '');

			print '</div>';

			print '<div class="message-body replyalternate'.$replyswitch.'" >
					<div class="message-contents" fromUserID="'.$reply['fromUserID'].'">'.$reply['message'].'</div>
				</div>

				<div style="clear:both"></div>';
			
			print '</div>';
			
		}
		unset($replytabl, $replyfirst, $replyswitch);
	}

	// Replies done, now print the footer
	print '<div class="message-foot threadalternate'.$switch.'">';

	// Now we show the Reply and Close Thread box.
	if ( $message['id'] == $viewthread )
	{
		if($User->type['User'] && (!isset($postLockedReason) || $newReplyAlertText == "Reply required") )
		{
			print '<div class="postbox">'.
				( $new['id'] != (-1) ? '' : '<a name="postbox"></a>').
				'<form class="safeForm" action="./modforum.php?newsendtothread='.$viewthread.'&amp;viewthread='.$viewthread.'&amp;actiontargetthread='.$viewthread.'#postbox" method="post">
				<input type="hidden" name="page" value="1" />
				<p>';

			print '<div class="hrthin"></div>';

			if ( isset($messageproblem) and $new['sendtothread'] )
			{
				print '<p class="notice">'.$messageproblem.'</p>';
			}

			if( $newAlertText == "Reply required" )
			{
				print '<p class="notice">A moderator has requested you reply to this thread before you can continue.</p>';
			}

			if (isset($newstatus) && $User->type['Moderator'])
				print '<p class="notice">Status changed to '.$newstatus.'</p>';
	
			print '<TEXTAREA NAME="newmessage" style="margin-bottom:5px;" ROWS="4">'.$_REQUEST['newmessage'].'</TEXTAREA><br />
					<input type="hidden" value="'.libHTML::formTicket().'" name="formTicket">
					<input type="hidden" name="page" value="'.$forumPager->pageCount.'" />';

			if ($User->type['Moderator'])
			{
				print 'Status: <select name="toggleStatus" onchange="this.form.submit();"'.
						(($message['assigned'] == $User->id || strpos($message['userType'],'Moderator')!==false || $User->type['Admin']) ? '' : 'disabled').'>
							<option value="Open"    '.($message['status'] == 'Open'     ? 'selected' : '').'>Open</option>
							<option value="Resolved"'.($message['status'] == 'Resolved' ? 'selected' : '').'>Resolved</option>
							<option value="Bugs"    '.($message['status'] == 'Bugs'     ? 'selected' : '').'>Bugs</option>
							<option value="Sticky"  '.($message['status'] == 'Sticky'   ? 'selected' : '').'>Sticky</option>
							<option value="Deleted"  '.($message['status'] == 'Deleted'   ? 'selected' : '').'>Deleted</option>
						</select>';
				
				if ($User->type['Admin']) 
				{
					print ' - Assigned to: <select name="setAssigned" onchange="this.form.submit();"'.
						(strpos($message['userType'],'Moderator')===false ? '' : 'disabled').'>
								<option value="None"         '.($message['assigned'] == ''      ? 'selected' : '').'>None</option>';
					$modsList = $DB->sql_tabl("SELECT id,username FROM wD_Users WHERE type LIKE '%Moderator%'");
					while( list($id, $name) = $DB->tabl_row($modsList) )
						print '<option value="'.$id.'"'.($message['assigned'] == $id ? 'selected' : '').'>'.$name.'</option>';
					print '</select>';
				}
				elseif ( $message['assigned'] == 0 || $message['assigned'] == $User->id )
				{
					print ' - Assigned to: <select name="setAssigned" onchange="this.form.submit();"'.
						(strpos($message['userType'],'Moderator')===false ? '' : 'disabled').'>
								<option value="None"         '.($message['assigned'] == ''        ? 'selected' : '').'>None</option>
								<option value="'.$User->id.'"'.($message['assigned'] == $User->id ? 'selected' : '').'>Me</option>
							</select>';
				}
				else
				{
					print ' - Assigned to: <select name="setAssigned" disabled>
								<option value="'.$message['assigned'].'" selected>'.$message['modname'].'</option>
							</select>';
				}
				print '<br>';
			}

			print '<br>';
			
			if ($message['modname'] == '' || $message['modname'] == $User->username || $message['fromUserID'] == $User->id )
			{
				print '<input type="submit" class="form-submit" value="Post reply" name="Reply">';
				if( $User->type['Moderator'] )
				{
					print '<input type="checkbox" value="Force reply" name="forceReply"> Force reply';
				}
				else if ( $message['isThanked'] == 0 )
				{
					print ' <input type="submit" class="form-submit" value="Send a +1 / Thanks" name="ThankMod" value="ThankMod">';
				}
			}		
									
			print '</p></form></div>
					<div class="hrthin"></div>';
		} else {
			print '<br />';
		}
	}

	print '<div class="message-foot-notification threadalternate'.$switch.'">
			<em><strong>'.$message['replies'].'</strong> '.($message['replies']==1?'reply':'replies').'</em>
			</div>';

	if ( $message['id'] == $viewthread )
	{
		print '<form action="modforum.php#'.$message['id'].'" method="get">
						<input type="hidden" name="viewthread" value="0" />
						<input type="submit" class="form-submit" value="Close" />
				</form>';
	}
	else
	{
		print '<a href="modforum.php?viewthread='.$message['id'].'#'.$message['id'].'" '.
			'title="Open this thread to view the replies, or post your own reply">Open</a>';
	}

	print "</div>
		</div>";
}

print '<div class="hr"></div>';


print '<div>';
print $forumPager->html('bottom');

print '<div><a href="#forum">Back to top</a><a name="bottom"></a></div>';

print '<div style="clear:both;"> </div>
		</div>';

print '</div>';
print '</div>';

if( $User->type['User'] )
{
	if( isset($replyToID) )
		libHTML::$footerScript[] = 'readModThread('.$replyToID.', '.$replyID.');';
}


libHTML::$footerScript[] = 'makeFormsSafe();';

libHTML::footer();

?>