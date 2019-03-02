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
 * Forum moderator specific actions; related to silencing.
 * 
 * These will mainly be called directly from forms in the forum, or on user profiles.
 *
 * @package Admin
 */
class adminActionsForum extends adminActions
{
	public function __construct()
	{
		parent::__construct();

		$forumActions = array(
			'disableSilence' => array(
				'name' => 'Unsilence something',
				'description' => 'Disables a silence on a user/thread.',
				'params' => array('silenceID'=>'Silence ID#'),
			),
			'changeSilenceLength' => array(
				'name' => 'Change silence length',
				'description' => 'Alter the length of an existing silence (note that this only applies to user silences; thread silences are indefinite).<br />0 length silences are indefinite.',
				'params' => array('silenceID'=>'Silence ID#', 'length'=>'Length (days)'),
			),
			'createUserSilence' => array(
				'name' => 'Silence user',
				'description' => 'Silences a user for the given length of time (0 is indefinite).',
				'params' => array('userID'=>'User ID','reason'=>'Reason','length'=>'Length (days)')
			),
			'createThreadSilence' => array(
				'name' => 'Silence thread',
				'description' => 'Silence a thread/post.',
				'params' => array('postID'=>'Post ID','reason'=>'Reason')
			),
			'createUserThreadSilence' => array(
				'name' => 'Silence thread and user',
				'description' => 'Silence a thread/post and user, with the user silence acting for the given length of time (0 is indefinite). Thread silences are always indefinite.',
				'params' => array('userID'=>'User ID','postID'=>'Post ID','reason'=>'Reason','length'=>'Length (days)')
			),'syncForumLikes' => array(
				'name' => 'Sync forum likes',
				'description' => 'Synchronizes the cached forum post like counts with the user-tracked like records, in case they somehow get out of sync.',
				'params' => array(),
			)
		);

		adminActions::$actions = array_merge(adminActions::$actions, $forumActions);
	}
	
	private static function setNextActivePostSilence($silenceID) {
		global $DB;
		
		/*
		 * For this silence ID find the thread that is being silenced
		 * Then find silences on posts which are part of that thread
		 * Find an active silence, and link it to the thread
		 */
		$silence = new Silence($silenceID);
		
		// Find replacement silences for the post
		$tabl = $DB->sql_tabl("
			SELECT silence.id 
			FROM wD_Silences silence 
			WHERE silence.postID = ".$silence->postID." AND NOT silence.id = ".$silence->id);
		while(list($potentialSilenceID) = $DB->tabl_row($tabl)) {
			$potentialSilence = new Silence($potentialSilenceID);
			
			if( $potentialSilence->isEnabled() ) {
				$DB->sql_put("
					UPDATE wD_ForumMessages 
					SET silenceID = ".$silenceID."
					WHERE id=".$potentialSilence->postID);
				break; // Only one active silence is needed
			}
		}
		
		
		// Find replacement silences for the thread
		$tabl = $DB->sql_tabl("
			SELECT thread.id, silence.id 
			FROM wD_ForumMessages thread
			INNER JOIN wD_ForumMessages response ON response.toID = thread.id
			INNER JOIN wD_Silences silence ON silence.id = response.silenceID
			WHERE thread.silenceID = ".$silenceID." AND silence.id = ".$silenceID);
		while(list($threadID, $potentialSilenceID) = $DB->tabl_row($tabl)) {
			$potentialSilence = new Silence($potentialSilenceID);
			
			if( $potentialSilence->isEnabled() ) {
				$DB->sql_put("
					UPDATE wD_ForumMessages 
					SET silenceID = ".$silenceID."
					WHERE id=".$threadID);
				break; // Only one active silence is needed
			}
		}
	}
	private static function setNextActiveUserSilence($silenceID) {
		global $DB;
		$silence = new Silence($silenceID);
		if( !$silence->userID ) return;
		
		$SilencedUser = new User($silence->userID);
		foreach($SilencedUser->getSilences() as $potentialSilence) {
			
			if( $potentialSilence->id == $silenceID ) continue;
			
			if( $potentialSilence->isEnabled() ) {
				$SilencedUser->silenceID = $potentialSilence->id;
				$DB->sql_put("UPDATE wD_Users SET silenceID = ".$potentialSilence->id." WHERE id = ".$SilencedUser->id);
				break; // Only one active silence is needed
			}
		}
	}
	public function disableSilence(array $params) {
		
		$silence = new Silence($params['silenceID']);
		$silence->disable();
		
		/*
		 * Disabling a silence is tricky, because disabling one silence may bring another silence
		 * into play as the active silence, so other applicable active silences need to be looked for,
		 * and linked in the place of this one if found.
		 */
		self::setNextActiveUserSilence($silence->id);
		self::setNextActivePostSilence($silence->id);
		
		return l_t('%s disabled.',$silence->toString());
	}
	public function disableSilenceConfirm(array $params) {

		$silence = new Silence($params['silenceID']);
		
		return l_t('Are you sure you want to disable this silence:').' <b>'.$silence->toString().'</b>?';
	}
	
	public function changeSilenceLength(array $params) {
		
		$silence = new Silence($params['silenceID']);
		
		$previousLength = $silence->length;
		
		// This function will validate the given length and check that it's not a post silence
		$silence->changeLength($params['length']);
		
		if( !$silence->isEnabled() ) {
			// Don't look for changes to posts, because they will not be affected by length changes
			self::setNextActiveUserSilence($silence->id);
		}
		
		return l_t('%s changed from <i>%s</i> to <i>%s</i>.',$silence->toString(),Silence::printLength($previousLength),Silence::printLength($silence->length));
	}
	public function changeSilenceLengthConfirm(array $params) {

		$silence = new Silence($params['silenceID']);
		
		if( $params['length'] < 0 ) 
			throw new Exception(l_t("Silence length must be non-negative."));
		
		return l_t('Are you sure you want to change the silence length from <i>%s</i> to <i>%s</i>, for <b>%s</b>?',
			Silence::printLength($silence->length),
			Silence::printLength($params['length']),$silence->toString());
	}
	
	private static function checkSilenceParams(array $params) {
		global $DB;
		
		if( strlen($params['reason']) < 10 )
			throw new Exception(l_t("Please give a reason longer than 10 characters."));
		
		if( isset($params['userID']) ) {
			$SilencedUser = new User((int)$params['userID']);
			
			if( $params['length'] < 0 )
				throw new Exception(l_t("Length in days must be greater than 0."));
		}
		
		if( isset($params['postID']) ) {
			list($threadsFound) = $DB->sql_row("SELECT COUNT(*) FROM wD_ForumMessages WHERE id = ".$params['postID']);
			if( $threadsFound == 0 )
				throw new Exception(l_t("Thread ID # %s does not exist.",$params['postID']));
		}
	}
	
	public function createUserSilence(array $params)
	{
		global $User;
		
		self::checkSilenceParams($params);
		
		$silenceID = Silence::create(
			$User->id,
			$params['reason'],
			null,
			$params['userID'],
			$params['length']
		);
		
		$silence = new Silence($silenceID);
		return l_t('User silenced:').' <br/>' .$silence->toString();
	}
	public function createUserSilenceConfirm(array $params)
	{
		self::checkSilenceParams($params);
		
		$UserSilence = new User($params['userID']);
		
		return l_t('Are you sure you want to silence this user %s because <i>%s</i> ?',
			Silence::printLength($params['length']),
			$params['reason']);
	}
	
	public function createThreadSilence(array $params)
	{
		global $User;
		
		self::checkSilenceParams($params);
		
		$silenceID = Silence::create(
			$User->id,
			$params['reason'],
			$params['postID']
		);
		
		$silence = new Silence($silenceID);
		return l_t('Thread silenced:').' <br/>' .$silence->toString();
	}
	public function createThreadSilenceConfirm(array $params)
	{
		self::checkSilenceParams($params);
		
		return l_t('Are you sure you want to silence the thread containing post ID # %s indefinitely because <i>%s</i> ?',$params['postID'],$params['reason']);
	}
	
	public function createUserThreadSilence(array $params)
	{
		global $User;
		
		self::checkSilenceParams($params);
		
		$silenceID = Silence::create(
			$User->id,
			$params['reason'],
			$params['postID'],
			$params['userID'],
			$params['length']
		);
		
		$silence = new Silence($silenceID);
		return l_t('User and thread silenced:').' <br/>' .$silence->toString();
	}
	public function createUserThreadSilenceConfirm(array $params)
	{
		self::checkSilenceParams($params);
		
		return l_t('Are you sure you want to silence this user %s, and silence the thread they were posting in, because <i>%s</i> ?',
			Silence::printLength($params['length']),$params['reason']);
	}
	public function syncForumLikes(array $params)
	{
		global $DB;
		
		$DB->sql_put("UPDATE wD_ForumMessages fm
			INNER JOIN (
			SELECT f.id, COUNT(*) as likeCount
			FROM wD_ForumMessages f
			INNER JOIN wD_LikePost lp ON f.id = lp.likeMessageID
			GROUP BY f.id
			) l ON l.id = fm.id
			SET fm.likeCount = l.likeCount");
		
		return l_t("All forum like counts have been synced, %s posts affected.", $DB->last_affected());
	}
}

?>
