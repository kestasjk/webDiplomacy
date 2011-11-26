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
			)
		);

		adminActions::$actions = array_merge(adminActions::$actions, $forumActions);
	}
	
	public function disableSilence(array $params) {
		
		$silence = new Silence($params['silenceID']);
		$silence->disable();
		
		/*
		 * Disabling a silence becomes a bit more complicated because a user / thread can
		 * only have one silence. A disabled silence may not cover other silences against a 
		 * user / thread which are still in effect.
		 * 
		 * TODO: Look for other applicable silences and reapply those, instead of the disabled one.
		 */
		
		return $silence->toString().' disabled.';
	}
	public function disableSilenceConfirm(array $params) {

		$silence = new Silence($params['silenceID']);
		
		return 'Are you sure you want to disable this silence: <b>'.$silence->toString().'</b>?';
	}
	
	public function changeSilenceLength(array $params) {
		
		$silence = new Silence($params['silenceID']);
		
		$previousLength = $silence->length;
		
		// This function will validate the given length and check that it's not a post silence
		$silence->changeLength($params['length']);
		
		/*
		 * For the same reasons as in the disable silence section, reducing the length may effectively 
		 * disable it, which may bring another silence into play, and this should be found and applied.
		*/
		
		return $silence->toString().' changed from <i>'.
			Silence::printLength($previousLength).'</i> to <i>'.
			Silence::printLength($silence->length).'</i>.';
	}
	public function changeSilenceLengthConfirm(array $params) {

		$silence = new Silence($params['silenceID']);
		
		if( $params['length'] < 0 ) 
			throw new Exception("Silence length must be non-negative.");
		
		return 'Are you sure you want to change the silence length from <i>'.
			Silence::printLength($silence->length).'</i> to <i>'.
			Silence::printLength($params['length']).'</i>, 
			for <b>'.$silence->toString().'</b>?';
	}
	
	private static function checkSilenceParams(array $params) {
		global $DB;
		
		if( strlen($params['reason']) < 10 )
			throw new Exception("Please give a reason.");
		
		if( isset($params['userID']) ) {
			$SilencedUser = new User((int)$params['userID']);
			
			if( $params['length'] < 0 )
				throw new Exception("Length in days must be greater than 0.");
		}
		
		if( isset($params['postID']) ) {
			list($threadsFound) = $DB->sql_row("SELECT COUNT(*) FROM wD_ForumMessages WHERE id = ".$params['postID']);
			if( $threadsFound == 0 )
				throw new Exception("Thread ID # ".$params['postID']." does not exist.");
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
		return 'User silenced: <br/>' .$silence->toString();
	}
	public function createUserSilenceConfirm(array $params)
	{
		self::checkSilenceParams($params);
		
		$UserSilence = new User($params['userID']);
		
		return 'Are you sure you want to silence this user '.
			Silence::printLength($params['length']).' because <i>'.
			$params['reason'].'</i> ?';
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
		return 'Thread silenced: <br/>' .$silence->toString();
	}
	public function createThreadSilenceConfirm(array $params)
	{
		self::checkSilenceParams($params);
		
		return 'Are you sure you want to silence the thread containing post ID # '.
		$params['postID'].' indefinitely because <i>'.
		$params['reason'].'</i> ?';
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
		return 'User and thread silenced: <br/>' .$silence->toString();
	}
	public function createUserThreadSilenceConfirm(array $params)
	{
		self::checkSilenceParams($params);
		
		return 'Are you sure you want to silence this user '.
			Silence::printLength($params['length']).', and silence the thread they were posting in, 
			because <i>'.$params['reason'].'</i> ?';
	}
}

?>
