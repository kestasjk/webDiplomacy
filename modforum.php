<?php

/**
 * @package Base
 */
require_once('header.php');
require_once('modforum/libPager.php');
require_once('modforum/libMessage.php');

$User->clearNotification('ModForum');

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

// End Modforumtabs	
	
$postboxopen = false;
$viewthread = false;

if ( isset($_REQUEST['threadID']) )
	$_REQUEST['viewthread'] = $_REQUEST['threadID'];

if( $User->type['User'] AND isset($_REQUEST['postboxopen'])) {
	$postboxopen = (bool) $_REQUEST['postboxopen'];

} elseif (isset($_REQUEST['viewthread'])) {
	$viewthread = (int) $_REQUEST['viewthread'];

} elseif (isset($_SESSION['viewthread'])) {
	$viewthread = $_SESSION['viewthread'];
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

// forceReply (Yes, No)
$forceReply = (isset($_REQUEST['forceReply'])) ? $_REQUEST['forceReply'] : ''; 
switch($forceReply) {
	case 'Yes':
	case 'No': break;
	default: $sc = '';
}

$forceUserIDs = array();
if (isset($_REQUEST['forceUserIDs']))
{
	$forceUserIDs = explode(',', $_REQUEST['forceUserIDs']);
	foreach ($forceUserIDs as $key => $value)
		$forceUserIDs[$key] = (int)$value;
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
	$actiontargetthread = $_REQUEST['actiontargetthread'];

	list($status)=$DB->sql_row("SELECT status FROM wD_ModForumMessages WHERE id = ".$actiontargetthread);
	$newstatus = $_REQUEST['toggleStatus'];

	if ($newstatus != $status)
	{
		$DB->sql_put("UPDATE wD_ModForumMessages SET status='".$newstatus."' WHERE id = ".$actiontargetthread);		

		// switch tabs if thread should still be viewed
		if($actiontargetthread == $viewthread)
			$tab = $_SESSION['modForumTab'] = $newstatus;
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
if(isset($_REQUEST['newmessage']) AND $User->type['User']
AND ($_REQUEST['newmessage'] != "") ) {
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
						'No',
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
							(isset($_REQUEST['ReplyAdmin']) ? 'Yes' : 'No')
							);

					$_SESSION['lastPostText']=$new['message'];
					$_SESSION['lastPostTime']=time();
					$_SESSION['lastPostType']='ThreadReply';

					$messageproblem="Reply posted sucessfully.";
					$new['message']=""; $new['subject']="";
					
					if ($threadDetails['assigned'] == 0 
							&& $User->type['Moderator'] 
							&& !isset($_REQUEST['ReplyAdmin']) 
							&& strpos($threadDetails['userType'],'Moderator')===false)
						$DB->sql_put('UPDATE wD_ModForumMessages SET assigned = "'.$User->id.'" WHERE id='.$threadDetails['id']);
					
					
					if (count($forceUserIDs) > 0)
					{
						foreach ($forceUserIDs as $forceUserID)
						{
							if ( $forceUserID != '')
							{
								$DB->sql_put('INSERT INTO wD_ForceReply
									SET id = "'.$new['id'].'",
									forceReply="'.$forceReply.'",
									toUserID = "'.$forceUserID.'"');
									
								$DB->sql_put("UPDATE wD_Users 
									SET notifications = CONCAT_WS(',',notifications, 'ForceModMessage') 
									WHERE id = ".$forceUserID);
							}
						}
					}
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

// Participated threads
$cacheUserParticipatedThreadIDsFilename = libCache::dirID('users',$User->id).'/readModThreads.js';

if( file_exists($cacheUserParticipatedThreadIDsFilename) )
{
	print '<script type="text/javascript" src="'.STATICSRV.$cacheUserParticipatedThreadIDsFilename.'?nocache='.rand(0,999999).'"></script>';
	libHTML::$footerScript[]='setModForumParticipatedIcons();';
}

print '
	<script type="text/javascript">
	// Update new message icon for forum posts depending on stored cookie values
	function setModForumMessageIcons() {
		$$(".messageIconForum").map(function (e) {
			var messageID = e.getAttribute("messageID");
			var threadID = e.getAttribute("threadID");
			
			if( isModPostNew(threadID, messageID) )
				e.show();

		});
	}

	function setModForumParticipatedIcons() {
		if( !Object.isUndefined(participatedModThreadIDs) ) {
			$$(".participatedIconForum").map(function (e) {
				var threadID = e.getAttribute("threadID");
				
				if( participatedModThreadIDs.member(threadID) )
					e.show();
			});
		}
	}
	function isModPostNew(threadID, messageID) {
		if( messageID <= User.lastModMessageIDViewed )
			return false;
		
		var lastReadID = readCookie("wD_ModRead_"+threadID);

		if( Object.isUndefined(lastReadID) )
			return true;
		else
			return ( messageID > lastReadID );
	}
	
	// Set a threadID as having been read, up to lastMessageID 
	function readModThread(threadID, lastMessageID) {
		createCookie("wD_ModRead_"+threadID, lastMessageID);
	}
	</script>';
	libHTML::$footerScript[]='setModForumMessageIcons();';

if( $User->type['Guest'] )
	print libHTML::pageTitle('ModForum', 'A place to discuss Mod topics.');
else
	print '<div class="content">';

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
// end of more tabs for admins
	
if (!$User->type['Moderator'])
{
	print '<div class="content-notice">';

	if ($ForumThreads == 0)
	{
		list($threads)= $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE type='ThreadStart'");
		list($posts)= $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE 1");

		print '<p class="notice">
				This is where you post issues you may have with certain users, games and bugs.<br>
				Every thread you post here is confidential and can only be viewed by yourself and the moderators.<br>
				All mods receive an alert when you make a post in this forum. </p><br>
				<p class="notice">To date there have been '.$threads.' threads and a total of '.$posts.' posts made here.</p><br>';
	}

	print '<p class="notice">
			Please make sure to include a gameID in the form of gameID=XXX for an example of your problem.
			</p></div>';
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
	
if( $User->isSilenced() ) {
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

$cacheHTML=libCache::dirName('mod_forum').'/page_'.$forumPager->currentPage.'.html';
if( file_exists($cacheHTML) )
	print $cacheHTML;

$tabl = $DB->sql_tabl("SELECT
	f.id, f.fromUserID, f.timeSent, f.message, f.subject, f.replies,
		u.username as fromusername, u.points as points, f.latestReplySent, IF(s.userID IS NULL,0,1) as online, u.type as userType, 
		f.status as status,
		f.assigned, u2.username as modname, 
		f.gameID, f.requestType
	FROM wD_ModForumMessages f
	INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
	LEFT JOIN wD_Users u2 ON ( f.assigned = u2.id )
	LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
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
	if (!$User->type['Moderator'] && $message['fromUserID'] != $User->id)
		continue;
	
	if (!$User->type['Moderator'])
	{
		list($message['replies']) =  $DB->sql_row("SELECT count(*) FROM wD_ModForumMessages WHERE adminReply='No' AND toID = ".$message['id']);
		list($message['latestReplySent']) = $DB->sql_row("SELECT id FROM wD_ModForumMessages WHERE (toID=".$message['id']." OR id=".$message['id'].") AND adminReply='No' ORDER BY id DESC LIMIT 1");
	}
	
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

	print '<div class="leftRule message-head threadalternate'.$switch.'">

		<a href="profile.php?userID='.$message['fromUserID'].'">'.$message['fromusername'].
			' '.libHTML::loggedOn($message['fromUserID']).
				' ('.$message['points'].' '.libHTML::points().User::typeIcon($message['userType']).')</a>'.
			'<br />
			<strong><em>'.libTime::text($message['timeSent']).'</em></strong>'.$deleteLink.'<br />
		</div>';
	
	
	if ($message['status']== "New")
		print '<div class="message-subject" style="color:#990000;">';
	elseif ($message['status']== "Resolved" || ($message['status']== "Deleted" && $User->type['Moderator'] /* Hide deletion state to requester */))
		print '<div class="message-subject" style="color:#888888;">';
	else
		print '<div class="message-subject">';
	
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
		
	if( $message['gameID'] != null ) {
		print ' <a href="board.php?gameID='.$message['gameID'].'">Link to game</a> ';
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
		$replyToID = $message['id']; // If there are no replies this will ensure the thread is still marked as read
		$replyID = $message['id'];

		if ( $message['replies'] > 50 )
		{
			$threadPager = new pagerThread( $message['replies'],$message['id']);
			$threadPager->pagerBar('threadPager');
		}
		// We are viewing the thread; print replies
		$replytabl = $DB->sql_tabl(
			"SELECT f.id, fromUserID, f.timeSent, f.message, u.points as points, IF(s.userID IS NULL,0,1) as online,
					u.username as fromusername, f.toID, u.type as userType, 
					f.adminReply as adminReply,
					r.forceReply
				FROM wD_ModForumMessages f
				LEFT JOIN wD_ForceReply r ON ( f.id = r.id )
				INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
				LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
				WHERE f.toID=".$message['id']." AND f.type='ThreadReply'
				GROUP BY f.id
				ORDER BY f.timeSent ASC
				".(isset($threadPager)?$threadPager->SQLLimit():''));
		$replyswitch = 2;
		$replyNumber = 0;
		$replyID = 0;
		list($maxReplyID) = $DB->sql_row("SELECT MAX(id) FROM wD_ModForumMessages WHERE toID=".$message['id']." AND type='ThreadReply'");
		while($reply = $DB->tabl_hash($replytabl) )
		{
			$replyToID = $reply['toID'];
			if ( $replyID < $reply['id'] )
				$replyID = $reply['id'];

			if ($reply['adminReply']=='Yes' && !$User->type['Moderator'])
				continue;

			$replyswitch = 3-$replyswitch;//1,2,1,2,1...
			
			print '<div class="reply replyborder'.$replyswitch.' replyalternate'.$replyswitch.'
				'.($replyNumber ? '' : 'reply-top').' userID'.$reply['fromUserID'].'"
				'.($reply['adminReply']=='Yes' ? 'style="background-color:#ffffff;"' : '').'
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

			print '<div class="message-head replyalternate'.$replyswitch.' leftRule"
					'.($reply['adminReply']=='Yes' ? 'style="background-color:#ffffff;"' : '').'>';

			if ($User->type['Moderator'] || $reply['fromUserID'] == $User->id || $reply['fromUserID'] == 5)
				print '<strong><a href="profile.php?userID='.$reply['fromUserID'].'">'.$reply['fromusername'].' '.
					libHTML::loggedOn($reply['fromUserID']).
						' ('.$reply['points'].' '.libHTML::points().User::typeIcon($reply['userType']).')';
			else
				print '<strong><a href="modforum.php">Mod-Team';
			
			print '</a></strong><br />';

			print libHTML::forumMessage($message['id'],$reply['id']);

			print '<em>'.libTime::text($reply['timeSent']).'</em>';

			print '</div>';

			// Embed forced reply.
			if ($reply['forceReply'] != '' && $User->type['Moderator'])
			{
				$forceReplyStatus   = array();
				$forceReplyUsername = array();
				$forceReplyStatus2  = array();
				$forceReplyReadIP   = array();
				$forceReplyReadTime = array();
				
				$forceUsersTab = $DB->sql_tabl(
					"SELECT fr.toUserID, fr.forceReply, u.username, fr.status, fr.readIP, fr.readTime
					FROM wD_ForceReply fr
					LEFT JOIN wD_Users u ON ( fr.toUserID = u.id)
					WHERE fr.id=".$reply['id']);

				while (list($toUserID, $forceReply, $username, $status, $readIP, $readTime) = $DB->tabl_row($forceUsersTab) )
				{
					$forceReplyStatus[$toUserID]   = $forceReply;
					$forceReplyUsername[$toUserID] = $username;	
					$forceReplyStatus2[$toUserID]  = $status;
					$forceReplyReadIP[$toUserID]   = $readIP;
					$forceReplyReadTime[$toUserID] = $readTime;
				}
				
				print "Send to: "; 
				$first=true;
				foreach ($forceReplyStatus as $toUserID => $forceReply)
				{
					if (!$first) print " - "; $first=false;
					print '<a href="profile.php?userID='.$toUserID.'">'.$forceReplyUsername[$toUserID].'</a> ';
					if ($forceReply=='Yes' && $forceReplyStatus2[$toUserID] == 'Sent') 
						print '(Waiting for reply) ';
					elseif ($forceReply=='Yes' && $forceReplyStatus2[$toUserID] == 'Read')
						print '(Waiting for reply / Read, IP='.long2ip($forceReplyReadIP[$toUserID]).', time='.libTime::text($forceReplyReadTime[$toUserID]).') ';
					elseif ($forceReplyStatus2[$toUserID] == 'Read')
						print '(Read, IP='.long2ip($forceReplyReadIP[$toUserID]).', time='.libTime::text($forceReplyReadTime[$toUserID]).') ';
				}
				print '<br>';
			}
			
			print '<div class="message-body replyalternate'.$replyswitch.'" '
					.($reply['adminReply']=='Yes' ? 'style="background-color:#ffffff;"' : '').'>
					<div class="message-contents" fromUserID="'.$reply['fromUserID'].'">'.$reply['message'].'</div>
				</div>

				<div style="clear:both"></div>';
			
			// Embed forced reply.
			if ($reply['forceReply'] != '' && $User->type['Moderator'])
			{
				foreach ($forceReplyStatus as $toUserID => $forceReply)
				{
					if ($forceReply == 'Done' && $User->type['Moderator'])
					{
						
						$forceReplyMessage = $DB->sql_hash(
							"SELECT f.id, fromUserID, f.timeSent, f.message, u.points as points, IF(s.userID IS NULL,0,1) as online,
									u.username as fromusername, f.toID, u.type as userType,
									fr.readIP, fr.readTime, fr.replyIP
								FROM wD_ModForumMessages f
								INNER JOIN wD_Users u ON f.fromUserID = u.id
								LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
								LEFT JOIN wD_ForceReply fr ON ( f.toID = fr.id && fr.toUserID =  f.fromUserID)
								WHERE f.toID=".$reply['id']." && fromUserID=".$toUserID);
						
						if ($forceReplyMessage && $forceReplyMessage['fromUserID'] != '')
						{
						
							if ($forceReplyMessage['id'] > $replyID )
								$replyID = $forceReplyMessage['id'];
						
							print '<div class="reply replyborder1 replyalternate1 reply-top userID'.$forceReplyMessage['fromUserID'].'" style="background-color:#ffffff; width:600px">';

							print '<a name="'.$forceReplyMessage['id'].'"></a>';

							print '<div class="message-head replyalternate1 leftRule" style="background-color:#ffffff;">';

							print '<strong><a href="profile.php?userID='.$forceReplyMessage['fromUserID'].'">'.$forceReplyMessage['fromusername'].' '.
								libHTML::loggedOn($forceReplyMessage['fromUserID']).
								' ('.$forceReplyMessage['points'].' '.libHTML::points().User::typeIcon($forceReplyMessage['userType']).')';
							
							print '</a></strong><br />';

							print libHTML::forumMessage($message['id'],$forceReplyMessage['id']);

							print '<em>'.libTime::text($forceReplyMessage['timeSent']).'</em>';

							print '</div>';

							print '
								<div class="message-body replyalternate'.$replyswitch.'" style="background-color:#ffffff;">
									<div class="message-contents" fromUserID="'.$forceReplyMessage['fromUserID'].'">
										UserID: '.$forceReplyMessage['fromUserID'].'<br>
										Read: IP='.($forceReplyMessage['readIP']   != 0 ? long2ip($forceReplyMessage['readIP']) : '').', time='.($forceReplyMessage['readTime'] != 0 ? libTime::text($forceReplyMessage['readTime']) : '').'<br>
										Reply: IP='.($forceReplyMessage['replyIP'] != 0 ? long2ip($forceReplyMessage['replyIP']): '').', time='.libTime::text($forceReplyMessage['timeSent']).'<br><br>
										'.$forceReplyMessage['message'].'
									</div>
								</div>

								<div style="clear:both"></div>
								</div>';
						}
					}
				}
			}
			
			print '</div>';
			
		}
		unset($replytabl, $replyfirst, $replyswitch);
	}

	// Replies done, now print the footer
	print '<div class="message-foot threadalternate'.$switch.'">';

	// Now we show the Reply and Close Thread box.
	if ( $message['id'] == $viewthread )
	{
		if($User->type['User'] && !isset($postLockedReason) )
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

			if (isset($newstatus) && $User->type['Moderator'])
				print '<p class="notice">Status changed to '.$newstatus.'</p>';
	
			print '<TEXTAREA NAME="newmessage" style="margin-bottom:5px;" ROWS="4">'.$_REQUEST['newmessage'].'</TEXTAREA><br />
					<input type="hidden" value="'.libHTML::formTicket().'" name="formTicket">
					<input type="hidden" name="page" value="'.$forumPager->pageCount.'" />';

			if ($User->type['Moderator'])
			{
				print 'forcePM on user (IDs): 
							<input type="text" size=20 value="" name="forceUserIDs">
						 - user(s) needs to reply: <select name="forceReply">
								<option value="Yes" selected>Yes</option>
								<option value="No" >No</option>
							</select><br>';

				print 'status: <select name="toggleStatus" onchange="this.form.submit();"'.
						(($message['assigned'] == $User->id || strpos($message['userType'],'Moderator')!==false || $User->type['Admin']) ? '' : 'disabled').'>
							<option value="Open"    '.($message['status'] == 'Open'     ? 'selected' : '').'>Open</option>
							<option value="Resolved"'.($message['status'] == 'Resolved' ? 'selected' : '').'>Resolved</option>
							<option value="Bugs"    '.($message['status'] == 'Bugs'     ? 'selected' : '').'>Bugs</option>
							<option value="Sticky"  '.($message['status'] == 'Sticky'   ? 'selected' : '').'>Sticky</option>
							<option value="Deleted"  '.($message['status'] == 'Deleted'   ? 'selected' : '').'>Deleted</option>
						</select>';
				
				if ($User->type['Admin']) 
				{
					print ' - assigned to: <select name="setAssigned" onchange="this.form.submit();"'.
						(strpos($message['userType'],'Moderator')===false ? '' : 'disabled').'>
								<option value="None"         '.($message['assigned'] == ''      ? 'selected' : '').'>None</option>';
					$modsList = $DB->sql_tabl("SELECT id,username FROM wD_Users WHERE type LIKE '%Moderator%'");
					while( list($id, $name) = $DB->tabl_row($modsList) )
						print '<option value="'.$id.'"'.($message['assigned'] == $id ? 'selected' : '').'>'.$name.'</option>';
					print '</select>';
				}
				elseif ( $message['assigned'] == 0 || $message['assigned'] == $User->id )
				{
					print ' - assigned to: <select name="setAssigned" onchange="this.form.submit();"'.
						(strpos($message['userType'],'Moderator')===false ? '' : 'disabled').'>
								<option value="None"         '.($message['assigned'] == ''        ? 'selected' : '').'>None</option>
								<option value="'.$User->id.'"'.($message['assigned'] == $User->id ? 'selected' : '').'>Me</option>
							</select>';
				}
				else
				{
					print ' - assigned to: <select name="setAssigned" disabled>
								<option value="'.$message['assigned'].'" selected>'.$message['modname'].'</option>
							</select>';
				}
				print '<br>';
			}
			
				
		
			if ($User->type['Admin'])
			{
				if( isset($_REQUEST['fromUserID']) && $User->type['Admin'] && $_REQUEST['fromUserID'] != $User->id && (int)$_REQUEST['fromUserID'] != 0)
					$fromUserIDprefill=(int)$_REQUEST['fromUserID'];
				else
					$fromUserIDprefill='';				
				print 'post as userID: <input type="text" size=4 value="'.$fromUserIDprefill.'" name="fromUserID"> (to make PMs or mails accessible in the modforum)<br>';
			}

			print '<br>';
			
			if ($message['modname'] == '' || $message['modname'] == $User->username || $message['fromUserID'] == $User->id || ($User->id == 5 && $tab=='Bugs'))
			{
				print '<input type="submit" ';
				if (strpos($message['userType'],'Moderator')===false && $User->type['Moderator'])
					print 'onclick="return confirm(\'Are you sure you want post this reply visible for the thread-starter too?\');"';
				print 'class="form-submit" value="Post reply" name="Reply">';
				
				if (strpos($message['userType'],'Moderator')===false && $User->type['Moderator'])
					print ' - ';
			}
			
			if (strpos($message['userType'],'Moderator')===false && $User->type['Moderator'])
				print '<input type="submit" class="form-submit" value="Only for admins" name="ReplyAdmin">';			
									
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