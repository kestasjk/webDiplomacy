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
require_once('pager/pagerforum.php');
require_once('lib/message.php');

/*
 * The forum page, unfortunately one of the oldest pieces of code and gradually hacked on
 * without getting packaged up. This has left a mess with quite a few script-wide variables,
 * but this is the basic flow:
 *
 * - Check whether we're viewing the postbox or which topic we're viewing
 * - Determine the correct page to display given the session data and viewtopic data
 * - Check for new threads/replies. Check them for problems and post them.
 * - Post the postbox / pager
 * - Select the threads for this page
 * 		- For each thread check it's selected, if so print the replies to the thread
 * 		- Post a reply box if needed
 * - Once done post the finishing page selector, and save the current time of viewing the forum
 * 	so the user can come back after checking a game and see what's new and what was new before
 * 	he logged on.
 */

/* Is the post box open, which thread are we viewing? */

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

$forumPager = new PagerForum($Misc->ForumThreads);
//$pageCount = $currentPage = ceil(($Misc->ForumThreads+1)/$forumPager->pageCount);

if( !isset($_SESSION['lastSeenForum']) || $_SESSION['lastSeenForum'] < $User->timeLastSessionEnded )
{
	$_SESSION['lastSeenForum']=$User->timeLastSessionEnded;
}


if( !isset($_REQUEST['page']) && isset($_REQUEST['viewthread']) && $viewthread )
{
	unset($orderIndex);
	list($orderIndex) = $DB->sql_row("SELECT b.latestReplySent FROM wD_ForumMessages b WHERE b.id = ".$viewthread);
	if(!isset($orderIndex) || !$orderIndex)
		libHTML::notice('Thread not found', "The thread you requested wasn't found.");

	list($position) = $DB->sql_row(
			"SELECT COUNT(*)-1 FROM wD_ForumMessages a WHERE a.latestReplySent >= ".$orderIndex." AND a.type='ThreadStart'"
		);

	$forumPager->currentPage = $forumPager->pageCount - floor($position/PagerForum::$defaultPostsPerPage);
}


if( !isset($_REQUEST['newmessage']) ) $_REQUEST['newmessage']  = '';
if( !isset($_REQUEST['newsubject']) ) $_REQUEST['newsubject'] = '';

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

		if( isset($_SESSION['lastPostText']) && $_SESSION['lastPostText'] == $new['message'] )
		{
			$messageproblem = "You are posting the same message again, please don't post repeat messages.";
			$postboxopen = !$new['sendtothread'];
		}
		elseif( isset($_SESSION['lastPostTime']) && $_SESSION['lastPostTime'] > (time()-20)
			&& ! ( $new['sendtothread'] && isset($_SESSION['lastPostType']) && $_SESSION['lastPostType']=='ThreadStart' ) )
		{
			$messageproblem = "You are posting too frequently, please slow down.";
			$postboxopen = !$new['sendtothread'];
		}
		else
		{
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

						$new['id'] = Message::send(0,
							$User->id,
							$new['message'],
							$new['subject'],
							'ThreadStart');

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
					"SELECT f.id, f.latestReplySent, 
						f.silenceID,
						silence.userID as silenceUserID,
						silence.postID as silencePostID,
						silence.moderatorUserID as silenceModeratorUserID,
						silence.enabled as silenceEnabled,
						silence.startTime as silenceStartTime,
						silence.length as silenceLength,
						silence.reason as silenceReason
					FROM wD_ForumMessages f 
					LEFT JOIN wD_Silences silence ON ( f.silenceID = silence.id )
					WHERE f.id=".$new['sendtothread']."
						AND f.type='ThreadStart'");

				unset($messageproblem);
				if( $threadDetails['latestReplySent'] < $Misc->ThreadAliveThreshold )
				{
					$messageproblem="The thread you are attempting to reply to is too old, and has expired.";
				}
				elseif( Silence::isSilenced($threadDetails) ) {
					$silence = new Silence($threadDetails);
					
					if( $silence->isEnabled() ) {
						$messageproblem="The thread you are attempting to reply to has been silenced; ".$silence->reason;
					}
					
					unset($silence);
				}
				
				if( isset($threadDetails['id']) && !isset($messageproblem) )
				{
					// It's being sent to an existing, non-silenced / dated thread.
					try
					{
						$new['id'] = Message::send( $new['sendtothread'],
							$User->id,
							$new['message'],
								'',
								'ThreadReply');

						$_SESSION['lastPostText']=$new['message'];
						$_SESSION['lastPostTime']=time();
						$_SESSION['lastPostType']='ThreadReply';

						$messageproblem="Reply posted sucessfully.";
						$new['message']=""; $new['subject']="";
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

libHTML::starthtml();

if( $User->type['Guest'] )
	print libHTML::pageTitle('Forum', 'A place to discuss topics/games with other webDiplomacy players.');
else
	print '<div class="content">';

if(isset($messageproblem) and !$new['sendtothread']) {
	print '<p class="notice"><a name="postbox"></a>'.$messageproblem.'</p>';
	libHTML::pagebreak();
}

print '<div class="forum"><a name="forum"></a>';

print '
	<div id="forumPostbox" style="'.($postboxopen?'':libHTML::$hideStyle).'" class="thread threadalternate1 threadborder1">
	<div style="margin:0;padding:0">
	<div class="message-head">
		<strong>Start a new discussion in the public forum</strong>
		</div>
	<div class="message-subject"><strong>Post a new thread</strong></div>
	<div style="clear:both;"></div>
	</div>
	<div class="hr"></div>
	';
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
		print '
		<div class="message-body threadalternate1 postboxadvice">
				If your post relates to a particular game please include the <strong>URL or ID#</strong>
				of the game.<br />
				If you are posting a <strong>feature request</strong> please check that it isn\'t mentioned in the
				<a href="http://forum.webdiplomacy.net">todo list</a>.<br />
				If you are posting a question please <strong>check the <a href="faq.php">FAQ</a></strong> before posting.<br />
				If your message is long you may need to write a summary message, and add the full message as a reply.
	
		</div>
		<div class="hr" ></div>
	
		<div class="message-body postbox" style="padding-top:0; padding-left:auto; padding-right:auto">
	
			<form class="safeForm" action="forum.php#postbox" method="post"><p>
			<div style="text-align:left; width:80%; margin-left:auto; margin-right:auto; float:middle">
			<strong>Subject:</strong><br />
			<input style="width:100%" maxLength=2000 size=60 name="newsubject" value="'.$_REQUEST['newsubject'].'"><br /><br />
			<strong>Message:</strong><br />
			<TEXTAREA NAME="newmessage" ROWS="6" style="width:100%">'.$_REQUEST['newmessage'].'</TEXTAREA>
			<input type="hidden" name="viewthread" value="0" />
			</div>
			<br />
	
			<input type="submit" class="form-submit" value="Post new thread" name="Post">
			</p></form>
		</div>';
	}

	print '<div class="hr"></div>
	<div class="message-foot threadalternate1">
		<form action="forum.php" method="get" onsubmit="$(\'forumPostbox\').hide(); $(\'forumOpenPostbox\').show(); return false;">
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
			<form action="forum.php#postbox" method="get" onsubmit="$(\'forumPostbox\').show(); $(\'forumOpenPostbox\').hide(); return false;">
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

$cacheHTML=libCache::dirName('forum').'/page_'.$forumPager->currentPage.'.html';
if( file_exists($cacheHTML) )
	print $cacheHTML;

$tabl = $DB->sql_tabl("SELECT
	f.id, f.fromUserID, f.timeSent, f.message, f.subject, f.replies,
		u.username as fromusername, u.points as points, f.latestReplySent, IF(s.userID IS NULL,0,1) as online, u.type as userType, 
		(SELECT COUNT(*) FROM wD_LikePost lp WHERE lp.likeMessageID = f.id) as likeCount, 
		f.silenceID,
		silence.userID as silenceUserID,
		silence.postID as silencePostID,
		silence.moderatorUserID as silenceModeratorUserID,
		silence.enabled as silenceEnabled,
		silence.startTime as silenceStartTime,
		silence.length as silenceLength,
		silence.reason as silenceReason
	FROM wD_ForumMessages f
	INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
	LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
	LEFT JOIN wD_Silences silence ON ( f.silenceID = silence.id )
	WHERE f.type = 'ThreadStart'
	ORDER BY f.latestReplySent DESC
	".$forumPager->SQLLimit());

/*
 * If it's a new post, jump to it
 *
 */
$switch = 2;
while( $message = $DB->tabl_hash($tabl) )
{
	if( Silence::isSilenced($message) )
		$silence = new Silence($message);
	else
		unset($silence);
		
	// Check for Anon posting:
	// ThreadAnon =Link to Anon game in subject -> all will be anon
	// PostAnon  = Link to Anon game in post    -> this one post will be anon.
	$threadAnon = $postAnon = 'No';
	$gameID=preg_replace('/.*gameID[:= _]?([0-9]+).*/i' , '\1' , $message['subject']);
	if ($gameID != $message['subject'])
		list($threadAnon)=$DB->sql_row('SELECT anon FROM wD_Games WHERE phase != "Finished" AND id = '.$gameID);
		
	$gameID=preg_replace('/.*gameID[:= _]?([0-9]+).*/i' , '\1' , $message['message']);
	if ($gameID != $message['message'])
		list($postAnon)=$DB->sql_row('SELECT anon FROM wD_Games WHERE phase != "Finished" AND id = '.$gameID);
		
	if ($threadAnon == 'Yes' || $postAnon=='Yes')
	{
		if (!$User->type['Moderator'])
		{
			$message['fromusername'] = 'Anon';
			$message['fromUserID'] = 0;
			$message['points'] = '??';
		}
		else
			$message['fromusername'] = $message['fromusername'].' (Anon)';
	}
	
	// Check for mutes first, before continuing
	$muteLink='';
	if( $User->type['User'] ) {
		$isThreadMuted = $User->isThreadMuted($message['id']);
		if( isset($_REQUEST['toggleMuteThreadID']) && $_REQUEST['toggleMuteThreadID']==$message['id'] ) {
			$User->toggleThreadMute($message['id']);
			$isThreadMuted = !$isThreadMuted;
		}
				
		if( $isThreadMuted ) continue;
		
		$toggleMuteURL = 'forum.php?toggleMuteThreadID='.$message['id'].'&rand='.rand(1,99999).'#'.$message['id'];
		$muteLink = ' <br /><a title="Mute this thread, hiding it from your forum and home page" class="light likeMessageToggleLink" href="'.$toggleMuteURL.'">'.($isThreadMuted ? 'Un-mute' : 'Mute' ).' thread</a>';
		
		if( $User->type['Admin'] || $User->type['ForumModerator'] ) {
			
			$muteLink .= '<br /><span class="likeMessageToggleLink">Silence: ';
			
			if( isset($silence) && $silence->isEnabled() ) {
				$muteLink .= '<a class="light" href="admincp.php?tab=Control%20Panel&amp;silenceID='.$silence->id.'#disableSilence">Disable silence</a>';
			}
			else
			{
				$muteLink .= '<a class="light" href="admincp.php?tab=Control%20Panel&amp;postID='.$message['id'].'#createThreadSilence">thread</a>, ';
				
				$muteLink .= '<a class="light" href="admincp.php?tab=Control%20Panel&amp;postID='.$message['id'].'&amp;userID='.$message['fromUserID'].'#createUserThreadSilence">user</a>';
			}
			
			$muteLink .= '</span>';
		}
	}
	
	print '<div class="hr userID'.$message['fromUserID'].' threadID'.$message['id'].'"></div>'; // Add the userID and threadID so muted users/threads dont create lines where their threads were

	$switch = 3-$switch; // 1,2,1,2,1,2...

	$messageAnchor = '<a name="'.($new['id'] == $message['id'] ? 'postbox' : $message['id']).'"></a>';

	print '<div class="thread threadID'.$message['id'].' threadborder'.$switch.' threadalternate'.$switch.' userID'.$message['fromUserID'].'">';

	// New or archived posts anchor to the start of the thread
	if ( $User->timeLastSessionEnded < $message['timeSent'] || $message['latestReplySent'] < $Misc->ThreadAliveThreshold )
	{
		print $messageAnchor;
		$messageAnchor = '';
	}

	if ( $message['replies'] == 0 )
	{
		print $messageAnchor;
	}

	print '<div class="leftRule message-head threadalternate'.$switch.'">

		<a href="profile.php?userID='.$message['fromUserID'].'">'.$message['fromusername'].
			' '.libHTML::loggedOn($message['fromUserID']).
				' ('.$message['points'].' '.libHTML::points().User::typeIcon($message['userType']).')</a>'.
			'<br />
			<strong><em>'.libTime::text($message['timeSent']).'</em></strong>'.$muteLink.'<br />
			'.$User->likeMessageToggleLink($message['id'],$message['fromUserID']).libHTML::likeCount($message['likeCount']).
		'</div>';
	
	
	print '<div class="message-subject">';

	print libHTML::forumMessage($message['id'],$message['latestReplySent']);
	print libHTML::forumParticipated($message['id']);

	
	if ( $message['latestReplySent'] < $Misc->ThreadAliveThreshold ) {
		$postLockedReason = "Thread is too old to reply to";
	}
	elseif(isset($silence) && $silence->isEnabled())
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
	
	
	
	print '<strong>'.$message['subject'].'</strong>';

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
					(SELECT COUNT(*) FROM wD_LikePost lp WHERE lp.likeMessageID = f.id) as likeCount, 
					f.silenceID,
					silence.userID as silenceUserID,
					silence.postID as silencePostID,
					silence.moderatorUserID as silenceModeratorUserID,
					silence.enabled as silenceEnabled,
					silence.startTime as silenceStartTime,
					silence.length as silenceLength,
					silence.reason as silenceReason
				FROM wD_ForumMessages f
				INNER JOIN wD_Users u ON f.fromUserID = u.id
				LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
				LEFT JOIN wD_Silences silence ON ( f.silenceID = silence.id )
				WHERE f.toID=".$message['id']." AND f.type='ThreadReply'
				order BY f.timeSent ASC
				".(isset($threadPager)?$threadPager->SQLLimit():''));
		$replyswitch = 2;
		$replyNumber = 0;
		list($maxReplyID) = $DB->sql_row("SELECT MAX(id) FROM wD_ForumMessages WHERE toID=".$message['id']." AND type='ThreadReply'");
		while($reply = $DB->tabl_hash($replytabl) )
		{
			$postAnon = 'No';
			$gameID=preg_replace('/.*gameID[:= _]?([0-9]+).*/i' , '\1' , $reply['message']);
			if ($gameID != $reply['message'])
				list($postAnon)=$DB->sql_row('SELECT anon FROM wD_Games WHERE phase != "Finished" AND id = '.$gameID);
				
			if ($threadAnon == 'Yes' || $postAnon=='Yes')
			{
				if (!$User->type['Moderator'])
				{
					$reply['fromusername'] = 'Anon';
					$reply['fromUserID'] = 0;
					$reply['points'] = '??';
				}
				else
					$reply['fromusername'] = $reply['fromusername'].' (Anon)';
			}
		
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
			libHTML::loggedOn($reply['fromUserID']).
					' ('.$reply['points'].' '.libHTML::points().User::typeIcon($reply['userType']).')</a>'.
				'</strong><br />';

			print libHTML::forumMessage($message['id'],$reply['id']);

			print '<em>'.libTime::text($reply['timeSent']).'</em>';

			print '<br />'.$User->likeMessageToggleLink($reply['id'],$reply['fromUserID']).libHTML::likeCount($reply['likeCount']);
			
			
			if( $User->type['Admin'] || $User->type['ForumModerator'] ) {
				
				if( Silence::isSilenced($reply) )
					$silence = new Silence($reply);
				else
					unset($silence);
				
				print '<br />';
				
				if( isset($silence) && $silence->isEnabled() ) 
					print '<a class="light likeMessageToggleLink" href="admincp.php?tab=Control%20Panel&amp;silenceID='.$silence->id.'#disableSilence">Disable silence</a>';
				else
					print '<a class="light likeMessageToggleLink" href="admincp.php?tab=Control%20Panel&amp;postID='.$reply['id'].'&amp;userID='.$reply['fromUserID'].'#createUserThreadSilence">Silence user</a>';
				
			}
					
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

		print '<br />';
		if (isset($threadPager) && $threadPager->currentPage < $threadPager->pageCount)
		{
			$threadPager->pagerBar('threadPager');
		}
		else
		{
			print '<form action="forum.php#'.$replyID.'" method="get">
							<input type="hidden" name="viewthread" value="'.$message['id'].'" />
							<input type="hidden" name="rand" value="'.rand(0,99999).'" />
							<input type="submit" class="form-submit" value="Refresh"
								title="Refresh your view of this thread to see if there are any new replies" />
					</form>';
		}
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
				'<form class="safeForm" action="./forum.php?newsendtothread='.$viewthread.'&amp;viewthread='.$viewthread.'#postbox" method="post">
				<input type="hidden" name="page" value="1" />
				<p>';

			print '<div class="hrthin"></div>';

			if ( isset($messageproblem) and $new['sendtothread'] )
			{
				print '<p class="notice">'.$messageproblem.'</p>';
			}

			print '<TEXTAREA NAME="newmessage" style="margin-bottom:5px;" ROWS="4">'.$_REQUEST['newmessage'].'</TEXTAREA><br />
					<input type="hidden" value="'.libHTML::formTicket().'" name="formTicket">
					<input type="hidden" name="page" value="'.$forumPager->pageCount.'" />
					<input type="submit" class="form-submit" value="Post reply" name="Reply"></p></form>
					</div>
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
		print '<form action="forum.php#'.$message['id'].'" method="get">
						<input type="hidden" name="viewthread" value="0" />
						<input type="submit" class="form-submit" value="Close" />
				</form>';
	}
	else
	{
		print '<a href="forum.php?viewthread='.$message['id'].'#'.$message['id'].'" '.
			'title="Open this thread to view the replies, or post your own reply">Open</a>';
		/*
		print '<form action="forum.php#'.$message['id'].'" method="get">
						<input type="hidden" name="viewthread" value="'.$message['id'].'" />
						<input type="submit" class="form-submit" value="Open"
							title="Open this thread to view the replies, or post your own reply" />
				</form>';
		*/
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
		libHTML::$footerScript[] = 'readThread('.$replyToID.', '.$replyID.');';
}

libHTML::$footerScript[] = 'makeFormsSafe();';

$_SESSION['lastSeenForum']=time();

libHTML::footer();

?>