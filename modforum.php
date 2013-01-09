<?php

/**
 * @package Base
 */
require_once('header.php');
require_once('pager/pager.php');

$User->clearNotification('ModForum');

class PagerThread extends Pager
{
	public static $defaultPostsPerPage=30;
	public $type='thread';
	function __construct($itemsTotal, $threadID)
	{
		parent::__construct('modforum.php',$itemsTotal,self::$defaultPostsPerPage);
		$this->addArgs('threadID='.$threadID);
	}
	function getCurrentPage($currentPage=1)
	{
		parent::getCurrentPage($this->pageCount);
		if ( $this->currentPage>$this->pageCount )
			$this->currentPage = $this->pageCount;
	}
	function currentPageNumber()
	{
		return parent::currentPageNumber();
		if( $this->currentPage != $this->pageCount )
			return parent::currentPageNumber();
		else
			return '';
	}
}

class PagerForum extends Pager
{
	public static $defaultPostsPerPage=30;
	public $type='forum';
	
	function __construct($itemsTotal)
	{
		parent::__construct('modforum.php',$itemsTotal,self::$defaultPostsPerPage);
	}
	function getCurrentPage($currentPage=1)
	{
		parent::getCurrentPage($this->pageCount);
		if ( $this->currentPage>$this->pageCount )
			$this->currentPage = $this->pageCount;
	}
	function currentPageNumber()
	{
		if( $this->currentPage != $this->pageCount )
			return parent::currentPageNumber();
		else
			return '';
	}
	
	function SQLLimit()
	{
		return ' LIMIT '.($this->pageCount-$this->currentPage)*$this->itemsPerPage.', '.$this->itemsPerPage;
	}
}

class Message
{
	public static function splitWords($text) {
		return $text;
		$words = explode(' ', $text);
		$text=array();
		foreach($words as $word)
		{
			if ( strlen($word) >= 20 )
			{
				$text[] = substr($word,0,20);
				$text[] = substr($word,20,strlen($word));
			}
			else
				$text[] = $word;
		}
		return implode(' ', $text);
	}

	static public function linkify($message)
	{
		$message=self::splitWords($message);

		$patterns = array(
				'/gameID[:= _]?([0-9]+)/i',
				'/userID[:= _]?([0-9]+)/i',
				'#modforum.php.threadID[:= _]?([0-9]+)#i',
				'#/forum.php.*threadID[:= _]?([0-9]+)#i',
				'/((?:[^a-z0-9])|(?:^))([0-9]+) ?(?:(?:D)|(?:points))((?:[^a-z])|(?:$))/i',
			);
		$replacements = array(
				'<a href="board.php?gameID=\1" class="light">gameID=\1</a>',
				'<a href="profile.php?userID=\1" class="light">userID=\1</a>',
				'modforum.php?<a href="modforum.php?threadID=\1#\1" class="light">threadID=\1</a>',
				'/forum.php?<a href="forum.php?threadID=\1#\1" class="light">threadID=\1</a>',
				'\1\2'.libHTML::points().'\3'
			);

		return preg_replace($patterns, $replacements, $message);
	}

	/**
	 * Send a message to the public forum. The variables passed are assumed to be already sanitized
	 *
	 * @param int $toID User/Thread ID to send to
	 * @param int $fromUserID UserID sent from
	 * @param string $message The message to be sent
	 * @param string[optional] $subject The subject
	 * @param string[optional] $type 'Bulletin'(GameMaster->Player) 'ThreadStart'(User->All) 'ThreadReply'(User->Thread)
	 *
	 * @return int The message ID
	 */
	static public function send($toID, $fromUserID, $message, $subject="", $type='Bulletin', $adminReply='No')
	{
		global $DB, $User;

		if( defined('AdminUserSwitch') && AdminUserSwitch != $User->id) $fromUserID = AdminUserSwitch;

		$message = self::linkify($message);

		$sentTime=time();

		if( 65000 < strlen($message) )
		{
			throw new Exception("Message too long");
		}

		libCache::wipeDir(libCache::dirName('mod_forum'));

		$DB->sql_put("INSERT INTO wD_ModForumMessages
						SET toID = ".$toID.", fromUserID = ".$fromUserID.", timeSent = ".$sentTime.",
						message = '".$message."', subject = '".$subject."', replies = 0,
						type = '".$type."', latestReplySent = 0, adminReply = '".$adminReply."'");

		$id = $DB->last_inserted();

		$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'ModForum') WHERE type LIKE '%Moderator%' AND id != ".$fromUserID);
		
		if ( $type == 'ThreadReply' )
			$DB->sql_put("UPDATE wD_ModForumMessages SET latestReplySent = ".$id.", replies = replies + 1 WHERE ( id=".$id." OR id=".$toID." )");
		else
			$DB->sql_put("UPDATE wD_ModForumMessages SET latestReplySent = id WHERE id = ".$id);
			
		if ($User->type['Moderator'] && $adminReply=='No')
		{
			$DB->sql_put("UPDATE wD_ModForumMessages SET status='Open' WHERE status='New' AND id = ".$toID);
		}
		
		if ( $type == 'ThreadReply' && $adminReply=='No')
		{
			list($starter) = $DB->sql_row('SELECT fromUserID FROM wD_ModForumMessages WHERE id = '.$toID);
			if ($starter != $fromUserID)
				$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'ModForum') WHERE id = ".$starter);
		}	


		$tabl=$DB->sql_tabl("SELECT t.id FROM wD_ModForumMessages t LEFT JOIN wD_ModForumMessages r ON ( r.toID=t.id AND r.fromUserID=".$fromUserID." AND r.type='ThreadReply' ) WHERE t.type='ThreadStart' AND ( t.fromUserID=".$fromUserID." OR r.id IS NOT NULL ) GROUP BY t.id");
		$participatedThreadIDs=array();
		while(list($participatedThreadID)=$DB->tabl_row($tabl)) {
			$participatedThreadIDs[$participatedThreadID] = $participatedThreadID;
		}

		$cacheUserParticipatedThreadIDsFilename = libCache::dirID('users',$fromUserID).'/readModThreads.js';

		file_put_contents($cacheUserParticipatedThreadIDsFilename, 'participatedModThreadIDs = $A(['.implode(',',$participatedThreadIDs).']);');

		return $id;
	}

	/**
	 * Remove any HTML added to a message
	 * @param $message The message to filter
	 * @return string The filtered message
	 */
	static function refilterHTML($message)
	{
		$patterns = array(
				'/<[^>]+>/i',
				'/<[^>]+$/i'
			);
		$replacements = array(
				' ',
				' '
			);

		return preg_replace($patterns, $replacements, $message);
	}
}


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

if ($User->type['Moderator'])
	list($ForumThreads) = $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE type='ThreadStart'");
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
	list($orderIndex) = $DB->sql_row("SELECT b.latestReplySent FROM wD_ModForumMessages b WHERE b.id = ".$viewthread);
	if(!isset($orderIndex) || !$orderIndex)
		libHTML::notice('Thread not found', "The thread you requested wasn't found.");

	list($position) = $DB->sql_row(
			"SELECT COUNT(*)-1 FROM wD_ModForumMessages a WHERE a.latestReplySent >= ".$orderIndex." AND a.type='ThreadStart'"
		);

	$forumPager->currentPage = $forumPager->pageCount - floor($position/PagerForum::$defaultPostsPerPage);
}

if (isset($_REQUEST['toggleStatus']) && $User->type['Moderator'])
{
	list($status)=$DB->sql_row("SELECT status FROM wD_ModForumMessages WHERE id = ".$viewthread);
	$newstatus = ($status=='Resolved' ? 'Open' : 'Resolved');
	$DB->sql_put("UPDATE wD_ModForumMessages SET status='".$newstatus."' WHERE id = ".$viewthread);
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

		if( isset($_SESSION['lastPostText']) && $_SESSION['lastPostText'] == $new['message'] && !$User->type['Admin'])
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

						
						$new['id'] = Message::send(0,
							$fromUserID,
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
					"SELECT f.id, f.latestReplySent
					FROM wD_ModForumMessages f 
					WHERE f.id=".$new['sendtothread']."
						AND f.type='ThreadStart'");

				unset($messageproblem);
				
				if( isset($threadDetails['id']) && !isset($messageproblem) )
				{
					// It's being sent to an existing, non-silenced / dated thread.
					try
					{
						$new['id'] = Message::send( $new['sendtothread'],
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
	
if ($ForumThreads == 0)
{
	list($threads)= $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE type='ThreadStart'");
	list($posts)= $DB->sql_row("SELECT COUNT(type) FROM wD_ModForumMessages WHERE 1");

	print '<div class="content-notice"><p class="notice">
			This is where you post issues you may have with certain users, games and bugs.<br>
			Every thread you post here is confidential and can only be viewed by yourself and the moderators.<br>
			All mods receive an alert when you make a post in this forum. </p><br>
			<p class="notice">To date there have been '.$threads.' threads and a total of '.$posts.' posts made here.</p>
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

		<form class="safeForm" action="modforum.php#postbox" method="post"><p>
		<div style="text-align:left; width:80%; margin-left:auto; margin-right:auto; float:middle">
		<strong>Subject:</strong><br />
		<input style="width:100%" maxLength=2000 size=60 name="newsubject" value="'.$_REQUEST['newsubject'].'"><br /><br />
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
		f.status as status
	FROM wD_ModForumMessages f
	INNER JOIN wD_Users u ON ( f.fromUserID = u.id )
	LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
	WHERE f.type = 'ThreadStart'
	".($User->type['Moderator'] ? '' : " AND fromUserID = '".$User->id."'")."
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

	print '<div class="leftRule message-head threadalternate'.$switch.'">

		<a href="profile.php?userID='.$message['fromUserID'].'">'.$message['fromusername'].
			' '.libHTML::loggedOn($message['fromUserID']).
				' ('.$message['points'].' '.libHTML::points().User::typeIcon($message['userType']).')</a>'.
			'<br />
			<strong><em>'.libTime::text($message['timeSent']).'</em></strong><br />
		</div>';
	
	
	if ($message['status']== "New")
		print '<div class="message-subject" style="color:#990000;">';
	elseif ($message['status']== "Resolved")
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
	else
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
					f.adminReply as adminReply
				FROM wD_ModForumMessages f
				INNER JOIN wD_Users u ON f.fromUserID = u.id
				LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
				WHERE f.toID=".$message['id']." AND f.type='ThreadReply'
				order BY f.timeSent ASC
				".(isset($threadPager)?$threadPager->SQLLimit():''));
		$replyswitch = 2;
		$replyNumber = 0;
		list($maxReplyID) = $DB->sql_row("SELECT MAX(id) FROM wD_ModForumMessages WHERE toID=".$message['id']." AND type='ThreadReply'");
		while($reply = $DB->tabl_hash($replytabl) )
		{
			$replyToID = $reply['toID'];
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


			print '
				<div class="message-body replyalternate'.$replyswitch.'" '
					.($reply['adminReply']=='Yes' ? 'style="background-color:#ffffff;"' : '').'>
					<div class="message-contents" fromUserID="'.$reply['fromUserID'].'">
						'.$reply['message'].'
					</div>
				</div>

				<div style="clear:both"></div>
				</div>';
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
				'<form class="safeForm" action="./modforum.php?newsendtothread='.$viewthread.'&amp;viewthread='.$viewthread.'#postbox" method="post">
				<input type="hidden" name="page" value="1" />
				<p>';

			print '<div class="hrthin"></div>';

			if ( isset($messageproblem) and $new['sendtothread'] )
			{
				print '<p class="notice">'.$messageproblem.'</p>';
			}

			if (isset($_REQUEST['toggleStatus']) && $User->type['Moderator'])
				print '<p class="notice">Status changed to '.$newstatus.'</p>';
	
			print '<TEXTAREA NAME="newmessage" style="margin-bottom:5px;" ROWS="4">'.$_REQUEST['newmessage'].'</TEXTAREA><br />
					<input type="hidden" value="'.libHTML::formTicket().'" name="formTicket">
					<input type="hidden" name="page" value="'.$forumPager->pageCount.'" />
					<input type="submit" ';
					
			if (strpos($message['userType'],'Moderator')===false && $User->type['Moderator'])
				print 'onclick="return confirm(\'Are you sure you want post this reply visible for the thread-starter too?\');"';
							
			print 'class="form-submit" value="Post reply" name="Reply">';

			if (strpos($message['userType'],'Moderator')===false && $User->type['Moderator'])
				print ' - <input type="submit" class="form-submit" value="Only for admins" name="ReplyAdmin">';

			if ($User->type['Admin'])
			{
				if( isset($_REQUEST['fromUserID']) && $User->type['Admin'] && $_REQUEST['fromUserID'] != $User->id && (int)$_REQUEST['fromUserID'] != 0)
					$fromUserIDprefill=(int)$_REQUEST['fromUserID'];
				else
					$fromUserIDprefill='';				
				print ' - UserID: <input type="text" size=4 value="'.$fromUserIDprefill.'" name="fromUserID">';
			}
			
			if ($message['status']!= 'New' && $User->type['Moderator'])
				print ' - <input type="submit" class="form-submit" value="Toggle Status" name="toggleStatus">';
			
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