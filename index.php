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

require_once(l_r('lib/message.php'));

require_once(l_r('objects/game.php'));

require_once(l_r('gamepanel/gamehome.php'));

/*
 * A field
 *
 * add(field, index)
 * compare(field1, field2) -> 1 if aligned, 0 if not
 *
 */
libHTML::starthtml(l_t('Home'));

if( !isset($_SESSION['lastSeenHome']) || $_SESSION['lastSeenHome'] < $User->timeLastSessionEnded )
{
	$_SESSION['lastSeenHome']=$User->timeLastSessionEnded;
}

global $DB;
$gameToggleID = 0;

if(isset($_POST['submit']))
{
	if(isset($_POST['gameToggleName']))
	{
		$gameToggleID = (int)$_POST['gameToggleName'];
	}

	if ($User->type['User'] and $gameToggleID > 0)
	{
		$noticesStatus = 5;
		list($noticesStatus) = $DB->sql_row("SELECT hideNotifications FROM wD_Members WHERE userID =".$User->id." and gameID =".$gameToggleID);

		if ($noticesStatus == 0)
		{
			$DB->sql_put("UPDATE wD_Members SET hideNotifications = 1 WHERE userID =".$User->id." and gameID =".$gameToggleID);
		}
		else if ($noticesStatus == 1)
		{
			$DB->sql_put("UPDATE wD_Members SET hideNotifications = 0 WHERE userID =".$User->id." and gameID =".$gameToggleID);
		}
	}
}

class libHome
{
	static public function getType($type=false, $limit=35)
	{
		global $DB, $User;

		$notices=array();

		$tabl=$DB->sql_tabl("SELECT n.*
			FROM wD_Notices n
			LEFT JOIN wD_Games g on g.name = n.linkName and n.type = 'Game'
			LEFT JOIN wD_Members m on m.gameID = g.id and n.type = 'Game' and m.userID = ".$User->id."
			WHERE (m.hideNotifications is null or m.hideNotifications = 0) and n.toUserID=".$User->id.($type ? " AND n.type='".$type."'" : '')."
			ORDER BY n.timeSent DESC ".($limit?'LIMIT '.$limit:''));
		while($hash=$DB->tabl_hash($tabl))
		{
			$notices[] = new notice($hash);
		}

		return $notices;
	}
	public static function PMs()
	{
		$pms = self::getType('PM', 10);
		$buf = '';
		foreach($pms as $pm)
		{
			$buf .= $pm->html();
		}
		return $buf;
	}
	public static function Game()
	{
		global $User;

		$pms = self::getType('Game');

		if(!count($pms))
		{
			print '<div class="hr"></div>';
			print '<p class="notice">'.l_t('No game notices found.').'</p>';
			return;
		}

		print '<div class="hr"></div>';

		foreach($pms as $pm)
		{
			print $pm->viewedSplitter();

			print $pm->html();
		}
	}
	public static function NoticePMs()
	{
		global $User;

		try
		{
			$message=notice::sendPMs();
		}
		catch(Exception $e)
		{
			$message=$e->getMessage();
		}

		if ( $message )
			print '<p class="notice">'.$message.'</p>';

		$pms = self::getType('PM',50);

		if(!count($pms))
		{
			print '<div class="hr"></div>';
			print '<p class="notice">'.l_t('No private messages found; you can send them to other people on their profile page.').'</p>';
			return;
		}

		print '<div class="hr"></div>';

		foreach($pms as $pm)
		{
			print $pm->viewedSplitter();

			print $pm->html();
		}
	}
	public static function NoticeGame()
	{
		global $User;

		$pms = self::getType('Game');

		if(!count($pms))
		{
			print '<div class="hr"></div>';
			print '<p class="notice">'.l_t('No game notices found; try browsing the <a href="gamelistings.php">game listings</a>, '.
				'or <a href="gamecreate.php">create your own</a> game.').'</p>';
			return;
		}

		print '<div class="hr"></div>';

		foreach($pms as $pm)
		{
			print $pm->viewedSplitter();

			print $pm->html();
		}
	}
	public static function Notice()
	{
		global $User;

		$pms = self::getType();

		if(!count($pms))
		{
			print '<div class="hr"></div>';
			print '<p class="notice">'.l_t('No notices found.').'</p>';
			return;
		}

		print '<div class="hr"></div>';

		foreach($pms as $pm)
		{
			print $pm->viewedSplitter();

			print $pm->html();
		}
	}
	static function topUsers()
	{
		global $DB;
		$rows=array();
		$tabl = $DB->sql_tabl("SELECT id, username, points FROM wD_Users
						order BY points DESC LIMIT 10");
		$i=1;
		while(list($userID,$username,$points)=$DB->tabl_row($tabl))
		{
			$rows[] = '#'.$i.': <a href="profile.php?userID='.$userID.'">'.$username.'</a> ('.$points.libHTML::points().')';
			$i++;
		}
		return $rows;
	}
	static function statsGlobalGame()
	{
		global $Misc;
		$stats=array(
			'Starting'=>$Misc->GamesNew,
			'Joinable'=>$Misc->GamesOpen,
			'Active'=>$Misc->GamesActive,
			'Finished'=>$Misc->GamesFinished
		);

		return $stats;
	}
	static function statsGlobalUser()
	{
		global $Misc;
		$stats=array(
			'Logged on'=>$Misc->OnlinePlayers,
			'Playing'=>$Misc->ActivePlayers,
			'Registered'=>$Misc->TotalPlayers
		);

		if( $stats['Logged on'] <= 1 ) unset($stats['Logged on']);
		if( $stats['Playing'] < 25 ) unset($stats['Playing']);

		return $stats;
	}
	static function globalInfo()
	{
		$userStats = self::statsGlobalUser();
		$gameStats = self::statsGlobalGame();
		//$topUsers = self::topUsers();

		//$buf='<div class="content" style="text-align:center;"><strong>Users:</strong> ';
		$buf='<strong>'.l_t('Users:').'</strong> ';
		$first=true;
		foreach($userStats as $name => $val)
		{
			if( $first ) $first=false; else $buf .= ' - ';
			$buf .= l_t($name).':<strong>'.$val.'</strong>';
		}

		$buf .= '<br /><strong>'.l_t('Games:').'</strong> ';
		$first=true;
		foreach($gameStats as $name => $val)
		{
			if( $first ) $first=false; else $buf .= ' - ';
			$buf .= l_t($name).':<strong>'.$val.'</strong>';
		}

		//$buf .= '</div>';
		//$buf .= '<br /><h3>Hall of fame</h3>'.implode('<br />',$topUsers);


		return $buf;
	}


	static public function gameWatchBlock ()
	{
		global $User, $DB;

		$tabl=$DB->sql_tabl("SELECT g.* FROM wD_Games g
			INNER JOIN wD_WatchedGames w ON ( w.userID = ".$User->id." AND w.gameID = g.id )
			WHERE NOT g.phase = 'Finished'
			ORDER BY g.processStatus ASC, g.processTime ASC");
		$buf = '';

		$count=0;
		while($game=$DB->tabl_hash($tabl))
		{
			$count++;
			$Variant=libVariant::loadFromVariantID($game['variantID']);
			$Game=$Variant->panelGameHome($game);

			$buf .= '<div class="hr"></div>';
			$buf .= $Game->summary();
		}

		if($count==0)
		{
			$buf .= '<div class="hr"></div>';
			$buf .= '<div><p class="notice">'.l_t('You\'re not spectating any games.').'<br />
				'.l_t('Click the \'spectate\' button on an existing game to add games to your list of spectated games.').
			      	'</p></div>';
		}
		return $buf;
	}
	static public function upcomingLiveGames ()
	{
		global $User, $DB;

                if ($User->options->value['displayUpcomingLive'] == 'No') return '';

		$tabl=$DB->sql_tabl("SELECT g.* FROM wD_Games g
			WHERE (g.phase = 'Pre-game' OR (g.phase in ('Diplomacy','Retreats','Builds') and g.minimumBet is not null and g.gameOver = 'No')) AND g.phaseMinutes < 60 AND g.password IS NULL
			ORDER BY g.processStatus ASC, g.processTime ASC LIMIT 3");
		$buf = '';
		$count=0;
		while($game=$DB->tabl_hash($tabl))
		{
			$count++;
			$Variant=libVariant::loadFromVariantID($game['variantID']);
			$Game=$Variant->panelGameHome($game);
			$buf .= '<div class="hr"></div>';
			$buf .= $Game->summary();
		}
		return $buf;
	}

	static public function gameNotifyBlock ()
	{
		global $User, $DB;

		$tabl=$DB->sql_tabl("SELECT g.* FROM wD_Games g
			INNER JOIN wD_Members m ON ( m.userID = ".$User->id." AND m.gameID = g.id )
			WHERE NOT g.phase = 'Finished' and m.status <> 'Defeated'
			ORDER BY g.processStatus ASC, g.processTime ASC");
		$buf = '';

		$count=0;
		while($game=$DB->tabl_hash($tabl))
		{
			$count++;
			$Variant=libVariant::loadFromVariantID($game['variantID']);
			$Game=$Variant->panelGameHome($game);

			$buf .= $Game->summary();
		}

		if($count==0)
		{
			$buf .= '<div class="hr"></div>';
			$buf .= '<div class="bottomborder"><p class="notice">'.l_t('You\'re not joined to any games!').'<br />
				'.l_t('Access the <a href="gamelistings.php?tab=">Games</a> '.
				'link above to find games you can join, or start a '.
				'<a href="gamecreate.php">New game</a> yourself.</a>').'</p></div>';
		}
		elseif ( $count == 1 && $User->points > 5 )
		{
			$buf .= '<div class="hr"></div>';
			$buf .= '<div class="bottomborder"><p class="notice">'.l_t('You can join as many games as you '.
			'have the points to join.').' </a></p></div>';
		}
		return $buf;
	}

	static public function gameDefeatedNotifyBlock ()
	{
		global $User, $DB;

		$tabl=$DB->sql_tabl("SELECT g.* FROM wD_Games g
			INNER JOIN wD_Members m ON ( m.userID = ".$User->id." AND m.gameID = g.id )
			WHERE NOT g.phase = 'Finished' and m.status = 'Defeated'
			ORDER BY g.processStatus ASC, g.processTime ASC");
		$buf = '';

		$count=0;
		while($game=$DB->tabl_hash($tabl))
		{
			$count++;
			$Variant=libVariant::loadFromVariantID($game['variantID']);
			$Game=$Variant->panelGameHome($game);

			$buf .= '<div class="hr"></div>';
			$buf .= $Game->summary();
		}

		if($count==0)
		{
			$buf .= '<div class="hr"></div>';
			$buf .= '<div class="bottomborder"><p class="notice"> You are not defeated in any active games, good job!<br />
				</p></div>';
		}
		elseif ( $count == 1 && $User->points > 5 )
		{
			$buf .= '<div class="hr"></div>';
			$buf .= '<div class="bottomborder"><p class="notice">'.l_t('You can join as many games as you '.
			'have the points to join.').' </a></p></div>';
		}
		return $buf;
	}

	static function forumNew() {
		// Select by id, prints replies and new threads
		global $DB, $Misc;

		$tabl = $DB->sql_tabl("
			SELECT m.id as postID, t.id as threadID, m.type, m.timeSent, IF(t.replies IS NULL,m.replies,t.replies) as replies,
				IF(t.subject IS NULL,m.subject,t.subject) as subject,
				u.id as userID, u.username, u.points, IF(s.userID IS NULL,0,0) as online, u.type as userType,
				SUBSTRING(m.message,1,100) as message, m.latestReplySent, t.fromUserID as threadStarterUserID
			FROM wD_ForumMessages m
			INNER JOIN wD_Users u ON ( m.fromUserID = u.id )
			LEFT JOIN wD_Sessions s ON ( m.fromUserID = s.userID )
			LEFT JOIN wD_ForumMessages t ON ( m.toID = t.id AND t.type = 'ThreadStart' AND m.type = 'ThreadReply' )
			ORDER BY m.timeSent DESC
			LIMIT 50");
		$oldThreads=0;
		$threadCount=0;

		$threadIDs = array();
		$threads = array();

		while(list(
				$postID, $threadID, $type, $timeSent, $replies, $subject,
				$userID, $username, $points, $online, $userType, $message, $latestReplySent,$threadStarterUserID
			) = $DB->tabl_row($tabl))
		{
			$threadCount++;

			if( $threadID )
				$iconMessage=libHTML::forumMessage($threadID, $postID);
			else
				$iconMessage=libHTML::forumMessage($postID, $postID);

			if ( $type == 'ThreadStart' ) $threadID = $postID;

			if( !isset($threads[$threadID]) )
			{
				if(strlen($subject)>30) $subject = substr($subject,0,40).'...';
				$threadIDs[] = $threadID;
				$threads[$threadID] = array('subject'=>$subject, 'replies'=>$replies,
					'posts'=>array(),'threadStarterUserID'=>$threadStarterUserID);
			}

			$message=Message::refilterHTML($message);

			if( strlen($message) >= 50 ) $message = substr($message,0,50).'...';

			$message = '<div class="message-contents threadID'.$threadID.'" fromUserID="'.$userID.'">'.$message.'</div>';

			$threads[$threadID]['posts'][] = array(
				'iconMessage'=>$iconMessage,'userID'=>$userID, 'username'=>$username,
				'message'=>$message,'points'=>$points, 'online'=>$online, 'userType'=>$userType, 'timeSent'=>$timeSent
			);
		}

		$buf = '';
		$threadCount=0;
		foreach($threadIDs as $threadID)
		{
			$data = $threads[$threadID];

			$buf .= '<div class="hr userID'.$threads[$threadID]['threadStarterUserID'].' threadID'.$threadID.'"></div>';

			$buf .= '<div class="homeForumGroupNew homeForumAlt'.($threadCount%2 + 1).
				' userID'.$threads[$threadID]['threadStarterUserID'].' threadID'.$threadID.'">
				<div class="homeForumSubject homeForumTopBorder">'.libHTML::forumParticipated($threadID).' '.$data['subject'].'</div> ';

			if( count($data['posts']) < $data['replies'])
			{
				$buf .= '<div class="homeForumPost homeForumMessage homeForumPostAlt'.libHTML::alternate().' ">

				...</div>';
			}


			$data['posts'] = array_reverse($data['posts']);
			foreach($data['posts'] as $post)
			{
				$buf .= '<div class="homeForumPost homeForumPostAlt'.libHTML::alternate().' userID'.$post['userID'].'">


					<div class="homeForumPostTime">'.libTime::text($post['timeSent']).' '.$post['iconMessage'].'</div>
					<a href="profile.php?userID='.$post['userID'].'" class="light">'.$post['username'].'</a>
						'.' ('.$post['points'].libHTML::points().
						User::typeIcon($post['userType']).')

					<div style="clear:both"></div>
					<div class="homeForumMessage">'.$post['message'].'</div>
					</div>';

			}

			$buf .= '<div class="homeForumLink">
					<div class="homeForumReplies">'.l_t('%s replies','<strong>'.$data['replies'].'</strong>').'</div>
					<a href="forum.php?threadID='.$threadID.'#'.$threadID.'">'.l_t('Open').'</a>
					</div>
					</div>';
		}

		if( $buf )
		{
			return $buf;
		}
		else
		{
			return '<div class="homeNoActivity">'.l_t('No forum posts found, why not '.
				'<a href="forum.php?postboxopen=1#postbox" class="light">start one</a>?');
		}
	}

	static function forumBlock()
	{
		$buf = '<div class="homeHeader">'.l_t('Forum').'</div>';

		$forumNew=libHome::forumNew();
		$buf .=  '<table><tr><td>'.implode('</td></tr><tr><td>',$forumNew).'</td></tr></table>';
		return $buf;
	}

	static function forumBlockExtern()
	{
		$buf = '<div class="homeHeader">'.l_t('Forum').'</div>';

		$forumNew=libHome::forumNewExtern();
		$buf .=  '<table><tr><td>'.implode('</td></tr><tr><td>',$forumNew).'</td></tr></table>';
		return $buf;
	}

	static function forumNewExtern()
	{
		// Select by id, prints replies and new threads
		global $DB, $Misc;

		$tabl = $DB->sql_tabl("SELECT t.forum_id, f.forum_name,

				t.topic_id, t.topic_title, t.topic_time,
				t.topic_views, t.topic_posts_approved,

				u1.webdip_user_id as topic_poster_webdip, t.topic_poster, t.topic_first_poster_name, t.topic_first_poster_colour,

				t.topic_last_post_id, t.topic_last_post_time,
				u2.webdip_user_id as topic_last_poster_webdip, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_poster_colour,
				p.post_id, p.post_text

				FROM phpbb_topics t
				INNER JOIN phpbb_posts p ON p.post_id = t.topic_last_post_id
				INNER JOIN phpbb_forums f ON f.forum_id = t.forum_id
				INNER JOIN phpbb_users u1 ON u1.user_id = t.topic_poster
				INNER JOIN phpbb_users u2 ON u2.user_id = t.topic_last_poster_id
				WHERE t.topic_visibility = 1 and f.forum_name <> 'Politics' and t.topic_title not like '%HIDDEN%'
				ORDER BY t.topic_last_post_time DESC
				LIMIT 20");

		$buf = '';
		while($t = $DB->tabl_hash($tabl))
		{
			$buf .= '<div class="hr"></div>';

			$urlForum = '/contrib/phpBB3/viewforum.php?f='.$t['forum_id'];
			$urlThread = '/contrib/phpBB3/viewtopic.php?f='.$t['forum_id'].'&t='.$t['topic_id'];
			$urlPost = '/contrib/phpBB3/viewtopic.php?f='.$t['forum_id'].'&p='.$t['post_id'].'#p'.$t['post_id'];

			//topic_poster_webdip // ID
			//topic_last_poster_webdip // ID

			$alt = libHTML::alternate();
			$buf .= '<div class="homeForumGroupNew homeForumAlt'.$alt.'">';

			$buf .= '
				<div class="homeForumSubject" >';
			//$buf .= '<div style="float:right"><img src="http://127.0.0.1/images/historyicons/external.png" width="10" height="10"></div>';

			$buf .= '<span style=\'font-size:90%\'>'.($t['topic_posts_approved']>1?'Re: ':'New: ').'</span>'
					.'<a href="'.$urlThread.'" style=\'font-size:110%\'>'.$t['topic_title'].'</a>';
					$buf .= '<div style="clear:both"></div>';
					$buf .= '<div class="homeForumPostTime" style="float:right"><em>'.libTime::text($t['topic_time']).'</em></div>';
					$buf .= '<span style=\'font-size:90%\'>';
					$buf .= 'Thread:</span> <a href="profile.php?userID='.$t['topic_poster_webdip'].'" class="light">'.$t['topic_first_poster_name'].'</a> ';
					$buf .= '<div style="clear:both"></div></div>';
					$buf .= '<div class="homeForumPost homeForumPostAlt'.$alt.'">';


					if( $t['topic_posts_approved']>1 ) {

						$buf .= '<div class="" style="margin-bottom:5px;margin-left:3px; margin-right:3px;">';
						$buf .= '<div class="homeForumPostTime" style="float:right;font-weight:bold"><em>'.libTime::text($t['topic_last_post_time']).'</em></div>';
						$buf .= '<span style=\'color:#009902;font-size:90%\'>';
						$buf .= 'Latest:</span> <a href="profile.php?userID='.$t['topic_last_poster_webdip'].'" class="light">'.$t['topic_last_poster_name'].'</a> '
						.'</div>';
					}

					$post = $DB->msg_escape(preg_replace("/\[[^\]]+\]/","",preg_replace("/<[^>]+>/","",$t['post_text'])));
					$post = str_replace("\\'","'", $post);
					$post = substr($post, 0, 75);
					if(strlen($post) > 45) $post .= '...';

					$buf .= '<div><span style="font-style:italic">&quot;'.$post.'&quot;</span>';

					$buf .= '<div style="float:right"><a href="'.$urlPost.'">Open</a></div>';
					$buf .= '<div style="clear:both"></div>';

					$buf .= '</div>';
					$buf .= '</div>';

					$buf .= '<div class="" style="margin-bottom:5px;margin-left:3px; margin-right:3px;">';


					$buf .= '<div style="margin-left:3px; margin-right:3px; font-size:90%">';
					$buf .= '<div style="float:right">';
					$buf .= l_t('<span style="color:black">%s</span> replies','<strong>'.($t['topic_posts_approved']-1).'</strong>');
					$buf .= ', '.l_t('<span style="color:black">%s</span> views','<strong style=\'content: "\f14c"\'>'.($t['topic_views']-1).'</strong>');
					$buf .= '</div>';
					$buf .= '&raquo;
					<a href="'.$urlForum.'">'.$t['forum_name'].'</a>

					</div>';
					$buf .= '</div>';
					$buf .= '</div>';

		}

		if( $buf )
		{
			return $buf;
		}
		else
		{
			return '<div class="homeNoActivity">'.l_t('No forum posts found, why not start one?');
		}
	}
}

if( !$User->type['User'] )
{

	print '<div class = "introToDiplomacy"><div class="content-notice" style="text-align:center">'.libHome::globalInfo().'</div></div>';
	print libHTML::pageTitle(l_t('Welcome to webDiplomacy!'),l_t('A multiplayer web implementation of the popular turn-based strategy game Diplomacy.'));
	//print '<div class="content">';
	?>
	<p style="text-align: center;"><img
	src="<?php print l_s('images/startmap.png'); ?>" alt="<?php print l_t('The map'); ?>"
	title="<?php print l_t('A webDiplomacy map'); ?>" /></p>
<div class = "introToDiplomacy_show"><p class="welcome"><?php print l_t('<em> "Luck plays no part in Diplomacy. Cunning and
cleverness, honesty and perfectly-timed betrayal are the tools needed to
outwit your fellow players. The most skillful negotiator will climb to
victory over the backs of both enemies and friends.<br />
<br />

Who do you trust?"<br />
(<a href="https://avalonhill.wizards.com/games/diplomacy">Avalon Hill</a>)</em>'); ?></p>
	<?php
	print '</div></div>';

	require_once(l_r('locales/English/intro.php'));
	print '</div>';
}
elseif( isset($_REQUEST['notices']) )
{
	$User->clearNotification('PrivateMessage');

	print '<div class="content"><a href="index.php" class="light">&lt; '.l_t('Back').'</a></div>';

	print '<div class="content-bare content-home-header">';
	print '<table class="homeTable"><tr>';

	notice::$noticesPage=true;
	if( !isset(Config::$customForumURL) ) {
		print '<td class="homeNoticesPMs">';
		print '<div class="homeHeader">'.l_t('Private messages').'</a></div>';
		print libHome::NoticePMs();
		print '</td>';
	} else {
		// system will be disabled on webDip on June 1
		print '<td class="homeNoticesPMs">';
		print '<div class="homeHeader">'.l_t('Private messages').'</a></div>';
		print '<div class="homeDisableNotice"><h4 style="text-align:center; font-size:10px;">'.l_t('The old PM system will be disabled on June 1. Please use the forum to send messages to other players. Click ').'<a href="/contrib/phpBB3/viewtopic.php?f=5&p=72670" style="text-decoration:none;">'.l_t('here').'</a>'.l_t(' for more information.').'</h4></div>';
		print libHome::NoticePMs();
		print '</td>';
	}
	print '<td class="homeSplit"></td>';

	print '<td class="homeNoticesGame">';
	print '<div class="homeHeader">'.l_t('Game messages').'</a></div>';
	print libHome::NoticeGame();
	print '</td>';

	print '</tr></table>';
	print '</div>';
	print '</div>';
}
else
{
	/*
	print '<div class="content-bare content-home-header">';
	print '<div class="boardHeader">blabla</div>';
	print '</div>';
	*/
	print '<div class="content-bare content-home-header">';// content-follow-on">';

	print '<table class="homeTable"><tr>';

	print '<td class="homeMessages">';

	$liveGames = libHome::upcomingLiveGames();
	if ($liveGames != '') {
		print '<div class="homeHeader">'.l_t('Joinable live games').' <a href="gamelistings.php?gamelistType=Search&phaseLengthMax=30m&messageNorm=Yes&messagePub=Yes&messageNon=Yes&messageRule=Yes&Submit=Search#results">'.libHTML::link().'</a></div>';
		print $liveGames;
	}

	if( isset(Config::$customForumURL) ) { // isset($_REQUEST['HomeForumTest']) ) {

		print '<div class="homeHeader">'.l_t('Forum').' <a href="/contrib/phpBB3/">'.libHTML::link().'</a></div>';
		if( file_exists(libCache::dirName('forum').'/home-forum.html') )
		{
			print file_get_contents(libCache::dirName('forum').'/home-forum.html');
			$diff = (time() - filemtime(libCache::dirName('forum').'/home-forum.html'));
			if( $diff > 60*5 ) {
				unlink(libCache::dirName('forum').'/home-forum.html');
			}
		}
		else
		{
			$buf_home_forum=libHome::forumNewExtern();
			file_put_contents(libCache::dirName('forum').'/home-forum.html', $buf_home_forum);
			print $buf_home_forum;
		}
	}
	else { //if( !isset(Config::$customForumURL)) {
		print '<div class="homeHeader">'.l_t('Forum').' <a href="forum.php">'.libHTML::link().'</a></div>';
		if( file_exists(libCache::dirName('forum').'/home-forum.html') )
			print file_get_contents(libCache::dirName('forum').'/home-forum.html');
		else
		{
			$buf_home_forum=libHome::forumNew();
			file_put_contents(libCache::dirName('forum').'/home-forum.html', $buf_home_forum);
			print $buf_home_forum;
		}
	}
	print '</td>';

	print '<td class="homeSplit"></td>';

	print '<td class="homeGameNotices">';

	/*$buf = libHome::PMs();
	if(strlen($buf))
		print '<div class="homeHeader">Private messages</div>'.$buf;
	*/

	print '<div class="homeHeader">'.l_t('Notices').' <a href="index.php?notices=on">'.libHTML::link().'</a></div>';
	print libHome::Notice();
	print '</td>';

	print '<td class="homeSplit"></td>';

	print '<td class="homeGamesStats">';
	print '<div class="homeHeader">'.l_t('My games').' <a href="gamelistings.php?page=1&gamelistType=My games">'.libHTML::link().'</a></div>';
	print libHome::gameNotifyBlock();
	print '<div class="homeHeader">'.l_t('Defeated games').'</div>';
	print libHome::gameDefeatedNotifyBlock();
	print '<div class="homeHeader">'.l_t('Spectated games').'</div>';
	print libHome::gameWatchBlock();

	print '</td>
	</tr></table>';

	print '</div>';
	print '</div>';
}

libHTML::$footerIncludes[] = l_j('home.js');
libHTML::$footerScript[] = l_jf('homeGameHighlighter').'();';

$_SESSION['lastSeenHome']=time();

libHTML::footer();

?>
