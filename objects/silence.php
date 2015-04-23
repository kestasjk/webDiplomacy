<?php

class Silence {
	
	/**
	* Silence ID
	* @var int
	*/
	public $id;
	
	/**
	* Silenced user ID 
	* (this may be null, if a post was silenced without a user)
	* @var int/null
	*/
	public $userID;
	
	/**
	* Silenced post ID 
	* (note that this silences the thread this post belongs to, not the post itself)
	* @var int/null
	*/
	public $postID;
	
	/**
	* UserID of the moderator which did the mute
	* @var int/null
	*/
	public $moderatorUserID;
	
	/**
	* Is the silence enabled (false if another mod has disabled it)
	* @var bool
	*/
	public $enabled;
	
	/**
	* A GMT+0 UNIX timestamp of when the silence was created
	* @var bool
	*/
	public $startTime;
	
	/**
	* The length the silence will last for, in days.
	* If 0 the silence is indefinite.
	* 
	* (This does not apply to muted threads, which are always permeanently silenced)
	* 
	* @var bool
	*/
	public $length;
	
	/**
	 * The reason given for the silence
	 * @var string
	 */
	public $reason;
	
	
	public function isEnabled() {
		return $this->enabled && !$this->isExpired();
	}
	protected function isExpired() {
		return !($this->length==0 || ( time() < ($this->startTime + $this->length*60*60*24)));
	}
	
	public function disable() {
		global $DB;
		
		$this->enabled=false;
		$DB->sql_put("UPDATE wD_Silences SET enabled=0 WHERE id=".$this->id);
	}
	
	/**
	 * 
	 * Creates a silence record, and returns the ID. Validation is performed here, plus a
	 * moderator log record will be created and the post, thread and user records will all be 
	 * linked to the newly created record.
	 * 
	 * @param int $moderatorUserID
	 * @param string $reason
	 * @param int/null $postID The post ID being silenced, or null
	 * @param int/null $userID The user ID being silenced, or null
	 * @param int/null $length The length in days (for user silences). If not given this is indefinite
	 * @return int The silence ID
	 */
	public static function create($moderatorUserID,$reason,$postID=null,$userID=null,$length=null) {
		global $DB;
		
		$moderatorUserID=(int)$moderatorUserID;
		$reason=$DB->escape($reason);
		$postID=($postID==null?"NULL":((int)$postID));
		$userID=($userID==null?"NULL":((int)$userID));
		$length=($length==null?0:((int)$length));
		
		$DB->sql_put(
			"INSERT INTO wD_Silences 
			(userID,postID,moderatorUserID,startTime,length,reason) 
			VALUES 
			(".$userID
			.",".$postID
			.",".$moderatorUserID
			.",".time()
			.",".$length
			.",'".$reason."')"
		);
		
		$silenceID = $DB->last_inserted();
		
		// Link the new silence record to the applicable user / forum post records
		if( is_numeric($userID) )
			$DB->sql_put("UPDATE wD_Users SET silenceID = ".$silenceID." WHERE id=".$userID);
		
		if( is_numeric($postID) ) {
			// The post
			$DB->sql_put("UPDATE wD_ForumMessages SET silenceID = ".$silenceID." WHERE id=".$postID);
			
			// The thread which the post is a part of
			$DB->sql_put("UPDATE wD_ForumMessages thread 
				INNER JOIN wD_ForumMessages post ON post.toID = thread.id 
				SET thread.silenceID = ".$silenceID." 
				WHERE post.id=".$postID);
			}
		
		return $silenceID;
	}
	
	public function changeLength($length) {
		global $DB;
		
		$length = (int) $length;
		
		if( $length < 0 ) 
			throw new Exception(l_t("The silence length must be non-negative"));
		
		if( $this->userID == null || !$this->userID )
			throw new Exception(l_t("Cannot apply a silence length to a post silence; post silences are indefinite."));
		
		$DB->sql_put("UPDATE wD_Silences SET length=".$length." WHERE id=".$this->id);
		
		$this->length = $length;
	}
	
	public function load($id) {
		global $DB;
		
		$record = array();
		
		if( is_array($id) ) {
			// If it's an array this may be coming in from a forum joined hash
			$record = $id;
		}
		else
		{
			$id= (int)$id;
			
			// Alias the column names, so that the data can be loaded from a linked join or from here the same way
			$record = $DB->sql_hash(
				"SELECT 
					id as silenceID,
					userID as silenceUserID,
					postID as silencePostID,
					moderatorUserID as silenceModeratorUserID,
					enabled as silenceEnabled,
					startTime as silenceStartTime,
					length as silenceLength,
					reason as silenceReason
					FROM wD_Silences 
					WHERE id=".$id
			);
		}
		
		$this->id = (int)$record['silenceID'];
		$this->userID = (int)$record['silenceUserID'];
		$this->postID = (int)$record['silencePostID'];
		$this->moderatorUserID = (int)$record['silenceModeratorUserID'];
		$this->enabled = ($record['silenceEnabled']!=0);
		$this->startTime = (int)$record['silenceStartTime'];
		$this->length = (int)$record['silenceLength'];
		$this->reason = $record['silenceReason'];
		
	}
	
	public function Silence($id) {
		$this->load($id);
	}

	public static function isSilenced(array $forumRecord) {
		return ( isset($forumRecord['silenceID']) && is_numeric($forumRecord['silenceID']));
	}
	/**
	 * A function which detects all inputs from silence related forms, 
	 * from $_REQUEST parameters, and acts on them (e.g. creating silences, 
	 * disabling, etc)
	 * 
	 * Validation, permission checking, etc, are done here, so that it can be 
	 * called from anywhere.
	 * 
	 * @return string A text message containing the results. Will be "" if nothing happened.
	 */
	public static function formActions() {
		global $User;
		
		if( !$User->type['ForumModerator'] ) return;
		
		if( 
			isset($_REQUEST['silencePostID']) && 
			isset($_REQUEST['silenceReason'])
		) {
			// Validation is done within create(), so these values can be passed straight through
			
			
			return l_t("Silence created successfully");
		}
		
		if( isset($_REQUEST['disableSilenceID']) ) {
			$silence = new Silence();
			$silence->load($_REQUEST['disableSilenceID']);
			$silence->disable();
			
			return l_t("Silence disabled successfully");
		}
		
		return "";
	}
	
	public function toString() {
		
		$Moderator = new User($this->moderatorUserID);
		
		$startTime = libTime::text($this->startTime);
		$endTime = ( $this->length == 0 ? l_t("Indefinite") : libTime::text($this->startTime + $this->length * 60 * 60 * 24));
		
		$silenceData = array(
			'Status' => '<b>'.($this->enabled ? ( $this->isExpired() ? l_t('Ended') : l_t('Active') ) : l_t('Disabled') ).'</b>',
			'Mod' => $Moderator->profile_link(),
			'Started' => $startTime,
			'Ends' => $endTime,
			'Reason' => $this->reason
		);
		
		if( $this->userID ) {
			$SilencedUser = new User($this->userID);
			$silenceData['User'] = $SilencedUser->profile_link();
		}
		
		if( $this->postID )
			$silenceData['Thread'] =  libHTML::threadLink($this->postID);
		
		$strArr = array('<ul class="formlist"><li>');
		foreach($silenceData as $k=>$v)
			$strArr[] = l_t($k).": <i>".$v."</i>";
		$strArr[]='</li></ul>';
		return implode("</li><li>",$strArr);
	}
	
	public static function printLength($length) {
		return ($length==0 ? l_t("indefinitely") : l_t("for %s days",$length) );
		
	}
}

class DummySilence extends Silence {
	public function DummySilence ($reason) {
         	$this->reason = $reason;
	}
	public function toString() {
         	return '<ul class="formlist"><li>'.$this->reason . '</li></ul>';
	}
}
