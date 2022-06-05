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

require_once(l_r('objects/basic/set.php'));

/**
 * An object representing a relationship between users. Many to many between users and groups,
 * intended for associating people who know each other in real life, to allow people who know
 * each other to play together in a transparent way. Also can be used to allow users to associate
 * with one another, moderators to group people 
 * 
 *
 * @package Base
 */
class Group
{
	/**
	 * The group ID
	 * @var int
	 */
	var $id;

	/**
	 * @var string
	 */
	var $name;

    static $validTypes = array('Person','Family','School','Work','Other','Unknown');

	/**
	 * The game ID, if applicable
	 * @var int|null
	 */
	var $gameID;

	/**
	 * The type of group
     * 
	 * @var string
	 */
	var $type;

	/**
	 * Is active
     * 
	 * @var bool
	 */
	var $isActive;

	/**
	 * Text with info about the group from the creator
     * 
	 * @var string
	 */
	var $description;
    
	/**
	 * Any notes from moderators
     * 
	 * @var string
	 */
	var $moderatorNotes;

	/**
	 * When was this group creasted
     * @var int
	 */
	var $timeCreated;

	/**
	 * When was this group last changed. This is used to detect what tags on users and members need updating
     * @var int
	 */
	var $lastChanged;

	/**
	 * Who owns this group
     * @var int
	 */
	var $ownerUserId;

	/**
	 * An array of GroupUser objects for this group
	 * 
	 * @var GroupUser[]
	 */
	var $GroupUsers;

	/**
	 * This is called if a suspicion is submitted directly from a game. The intention is that when created this way 
	 * suspicions can be created for anonymous games, tying them to the user IDs while keeping them anonymous by
	 * linking to the gameID and getting the country.
	 * 
	 * @return int ID of the group created
	 */
	static function createSuspicionFromGame($gameId, $countriesSuspected, $suspicionStrength, $explanation )
	{
		global $DB, $User;

		$gameId = (int)$gameId;
		$filteredCountriesSuspected = array();
		foreach($countriesSuspected as $countrySuspected)
			$filteredCountriesSuspected[] = (int)$countrySuspected;
		$countriesSuspected = $filteredCountriesSuspected;
		unset($filteredCountriesSuspected);
		$suspicionStrength = (int) $suspicionStrength;
		// $explanation = Pass this in as-is, will be filtered within
		
		// Take the gameID and countries and get the user IDs
		$Variant = libVariant::loadFromGameID($gameId);
		$Game = $Variant->Game($gameId);

		$suspectedUsers = array();
		foreach($countriesSuspected as $countrySuspected)
		{
			$suspectedUsers[] = new User($Game->Members->ByCountryID[$countrySuspected]->userID);
		}
	
		$groupId = self::create('Unknown', $Game->name . ' - #' . $Game->turn, $explanation, $Game->id, $Game->id);
		$Group = new Group($groupId);
		foreach($suspectedUsers as $suspectedUser)
		{
			$Group->userAdd($User, $suspectedUser, $suspicionStrength);
		}

		return $groupId;
	}



	/**
	 * Validate the inputs and permissions and create a group in the DB, returning an ID, or else throw exception
	 * @return int ID of the group created
	 */
	static function create($groupType, $groupName, $groupDescription, $groupGameReference, $gameID = null)
	{
		global $User, $DB;
		if( Group::canUserCreate($User) )
		{
			if( in_array($groupType, Group::$validTypes, true) )
			{
				$groupGameReference = $DB->msg_escape($groupGameReference);
				$groupDescription = $DB->msg_escape($groupDescription);
				if( strlen($groupDescription) < 5 )
				{
					throw new Exception("Description / explanation does not contain enough detail, please enter a description / explanation.");
				}
				if( $groupType === 'Unknown' && ( strlen($groupGameReference) < 5 && !$User->type['Moderator'] ) )
				{
					throw new Exception("Please select a game you are actively/recently playing against this user, which is causing you to suspect the user.");
				}
				$groupDescription .= '<br />Game Reference: ' . $groupGameReference;

				$groupName = $DB->msg_escape($groupName);
				$DB->sql_put("INSERT INTO wD_Groups (`name`,isActive,`type`,`display`,ownerUserId,timeCreated,timeChanged,`description`,`gameId`) VALUES ('".$groupName."',1,'" .$groupType ."','Moderators',".$User->id.",".time().",".time().",'".$groupDescription."',".($gameID == null ? "NULL" : $gameID).")");
				list($groupId) = $DB->sql_row("SELECT LAST_INSERT_ID()");
				return $groupId;
			}
			throw new Exception("Group type provided is invalid.");
		}
		throw new Exception("User does not have permission to create groups.");
	}
	public function userAdd($userAdding, $userToBeAdded, $groupUserStrength = 0)
	{
		global $DB;
		if( !$this->canUserAdd($userAdding, $userToBeAdded) )
		{
			throw new Exception("User does not have permission to add given user.");
		}

		$groupUserStrength = intval($groupUserStrength);
		if( $groupUserStrength < 0 ) $groupUserStrength = 0;
		if( $groupUserStrength > 100 ) $groupUserStrength = 100;

		$userWeighting = 0;
		$ownerWeighting = 0;
		$modWeighting = 0;
		if( $userAdding->type['Moderator'] ) $modWeighting = $groupUserStrength;
		if( $userAdding->id == $userToBeAdded->id ) $userWeighting = $groupUserStrength;
		if( $userAdding->id == $this->ownerUserId ) $ownerWeighting = $groupUserStrength;
		
		$DB->sql_put("INSERT INTO wD_GroupUsers (userId, groupId, isActive, userWeighting, ownerWeighting, modWeighting, createdByUserId, timeChanged, timeCreated) VALUES (".
			$userToBeAdded->id.", ". 
			$this->id.", ".
			"1, ".$userWeighting.", ".$ownerWeighting.", ".$modWeighting.", ".
			$userAdding->id.", ".
			time().", ".
			time().") ON DUPLICATE KEY UPDATE timeChanged = VALUES(timeChanged)");
	}

	static private function canUserCreate($User)
	{
		return ( $User->type['User'] || $User->type['Moderator']);
	} 
	private function canUserAdd($userAdding, $userToBeAdded)
	{
		if ( !$this->canUserModify($userAdding) ) return false;

		if( $userToBeAdded->type['User'] ) return true;

		return false;
	}
	private function canUserModify($userModifying)
	{
		if( $userModifying->type['Moderator'] ) return true;

		if( !$userModifying->type['User'] ) return false;

		if( $this->ownerUserId == $userModifying->id ) return true;

		return false;
	}
	public function userSetDescription($userModifying, $groupDescription)
	{
		global $DB;

		if( !$this->canUserModify($userModifying) ) throw new Exception("Permission denied for description update.");
		
		$groupDescription = $DB->msg_escape($groupDescription);

		$DB->sql_put("UPDATE wD_Groups SET `description` = '" . $groupDescription . "' WHERE id = " . $this->id);
	}
	public function userSetModNotes($userModifying, $modNotes)
	{
		global $DB;

		if( !$userModifying->type['Moderator'] ) throw new Exception("Permission denied for mod notes update.");
		
		$modNotes = $DB->msg_escape($modNotes . "-" . $userModifying->username);

		$DB->sql_put("UPDATE wD_Groups SET `moderatorNotes` = '" . $modNotes . "' WHERE id = " . $this->id);
	}
	public function userSetActive($userModifying, $groupActive)
	{
		global $DB;

		if( !$this->canUserModify($userModifying) ) throw new Exception("Permission denied for active update.");
		
		$groupActive = intval($groupActive) ? 1 : 0;

		$DB->sql_put("UPDATE wD_Groups SET `isActive` = '" . $groupActive . "' WHERE id = " . $this->id);
	}
	private function canUserUpdateUserWeighting($userUpdating, $groupUserToUpdate)
	{
		return ( $userUpdating->id == $groupUserToUpdate->userId );
	}
	private function canUserUpdateOwnerWeighting($userUpdating)
	{
		return ( $userUpdating->id == $this->ownerUserId );
	}
	private function canUserUpdateModWeighting($userUpdating)
	{
		return $userUpdating->type['Moderator'];
	}
	public function userUpdateUserWeighting($userUpdating, $groupUserToUpdate, $newWeighting)
	{
		global $DB;

		$newWeighting = self::getClosestWeighting($newWeighting);
		if( $groupUserToUpdate->userWeighting == $newWeighting ) return;
		
		if( $this->canUserUpdateUserWeighting($userUpdating, $groupUserToUpdate) )
		{
			$DB->sql_put("UPDATE wD_GroupUsers SET userWeighting = " . $newWeighting . ", timeChanged = ".time()." WHERE userId = ". $groupUserToUpdate->userId." AND groupId = ".$groupUserToUpdate->groupId);
		}
	}
	public function userUpdateOwnerWeighting($userUpdating, $groupUserToUpdate, $newWeighting)
	{
		global $DB;

		$newWeighting = self::getClosestWeighting($newWeighting);
		if( $groupUserToUpdate->ownerWeighting == $newWeighting ) return;
		
		if( $this->canUserUpdateOwnerWeighting($userUpdating, $groupUserToUpdate) )
		{
			$DB->sql_put("UPDATE wD_GroupUsers SET ownerWeighting = " . $newWeighting . ", timeChanged = ".time()." WHERE userId = ". $groupUserToUpdate->userId." AND groupId = ".$groupUserToUpdate->groupId);
		}
	}
	public function userUpdateModWeighting($userUpdating, $groupUserToUpdate, $newWeighting)
	{
		global $DB;

		$newWeighting = self::getClosestWeighting($newWeighting);
		if( $groupUserToUpdate->modWeighting == $newWeighting ) return;
		
		if( $this->canUserUpdateModWeighting($userUpdating, $groupUserToUpdate) )
		{
			$DB->sql_put("UPDATE wD_GroupUsers SET modWeighting = " . $newWeighting . ", modUserId = ".$userUpdating->id.", timeChanged = ".time()." WHERE userId = ". $groupUserToUpdate->userId." AND groupId = ".$groupUserToUpdate->groupId);
		}
	}
	public function canUserComment($userCommenting)
	{
		if( $userCommenting->type['Moderator'] ) return true;

		if( $this->ownerUserId == $userCommenting->id ) return true;

		foreach( $this->GroupUsers as $groupUser )
		{
			if( $groupUser->userId == $userCommenting->id ) return true;
		}

		return false;
	}
	private static $SELECTSQL = "SELECT id, `name`, `type`, isActive, `description`, moderatorNotes, timeCreated, timeChanged, ownerUserId FROM wD_Groups ";
	/**
	 * Create a Group object
	 * @param int|array $id Group id, or an array containing the group data
	 */
	public function __construct($id)
	{
		global $DB;

		if( !is_array($id) )
		{
			$row = $DB->sql_hash(self::$SELECTSQL . " WHERE id = " . intval($id));
			if( !$row )
			{
				throw new Exception("Group ID not found.");
			}
		}
		else
		{
			$row = $id;
		}

		foreach ( $row as $name => $value )
		{
			$this->{$name} = $value;
		}

		$this->loadUsers();
	}
	private function loadUsers()
	{
		$this->GroupUsers = self::getUsers("groupId = " . $this->id);
	}
	public static function getUsers($whereClause )
	{
		global $DB;

		$groupUsers = array();
		$users = $DB->sql_tabl("SELECT gr.name groupName, gr.type groupType, g.userId, g.groupId, g.isActive, g.userWeighting, g.ownerWeighting, g.modWeighting, g.createdByUserId, g.timeCreated, g.timeChanged, g.modUserId, ".
			// Request the details needed to render links:
			"u.username userUsername, u.points userPoints, u.type userType, ".
			"o.username ownerUsername, o.points ownerPoints, o.type ownerType, ".
			"m.username modUsername, m.points modPoints, m.type modType ".
			"FROM wD_GroupUsers g ".
			"INNER JOIN wD_Groups gr ON gr.id = g.groupId ".
			"INNER JOIN wD_Users u ON u.id = g.userId ".
			"INNER JOIN wD_Users o ON o.id = g.createdByUserId ".
			"LEFT JOIN wD_Users m ON m.id = g.modUserId ".
			"WHERE ".$whereClause);
		while($userRec = $DB->tabl_hash($users) )
		{
			$groupUsers[] = new GroupUser($userRec);
		}
		return $groupUsers;
	}
	public static function ownedGroupNamesByID($User, $activeOnly = true)
	{
		global $DB;
		
		$groups = $DB->sql_tabl('SELECT id, `name`, `type` FROM wD_Groups WHERE ownerUserId = '.$User->id.($activeOnly?' AND isActive = 1 ':' ').' ORDER BY timeChanged DESC');
		$groupNames = array();
		while($row = $DB->tabl_hash($groups))
		{
			$groupNames[$row['id']] = '#'.$row['id'] . ' ' . $row['name'] . ' - ' . $row['type'];
		}

		return $groupNames;
	}

	/**
	 * Declared group names that this user is in
	 */
	public static function declaredGroupNamesByID($User, $activeOnly = true)
	{
		global $DB;
		
		$groups = $DB->sql_tabl('SELECT g.id, g.`name`, g.`type` FROM wD_Groups g INNER JOIN wD_GroupUsers u ON u.groupId = g.id WHERE u.userId = '.$User->id.($activeOnly?' AND g.isActive = 1 AND u.isActive = 1 ':' ').' AND `type`<>"Unknown" AND (modWeighting > 0.0 OR userWeighting > 0.0)  ORDER BY g.timeChanged DESC');
		$groupNames = array();
		while($row = $DB->tabl_hash($groups))
		{
			$groupNames[$row['id']] = '#'.$row['id'] . ' ' . $row['name'] . ' - ' . $row['type'];
		}

		return $groupNames;
	}
	/**
	 * Suspected groups that this user has created
	 */
	public static function suspectedGroupNamesByID($User, $activeOnly = true)
	{
		global $DB;
		
		$groups = $DB->sql_tabl('SELECT id, `name` FROM wD_Groups WHERE ownerUserId = '.$User->id.($activeOnly?' AND isActive = 1 ':' ').' AND `type`="Unknown" ORDER BY id DESC');
		$groupNames = array();
		while($row = $DB->tabl_hash($groups))
		{
			$groupNames[$row['id']] = '#'.$row['id'] . ' ' . $row['name'];
		}

		return $groupNames;
	}
	public static function validGroupNamesByID($User, $activeOnly = true)
	{
		global $DB;
		
		$groups = $DB->sql_tabl('SELECT g.id, g.`name`, g.`type` FROM wD_Groups g INNER JOIN wD_GroupUsers u ON u.groupId = g.id WHERE u.userId = '.$User->id.($activeOnly?' AND g.isActive = 1 AND u.isActive = 1 ':' ').' AND (modWeighting > 0.0 OR userWeighting > 0.0)  ORDER BY g.timeChanged DESC');
		$groupNames = array();
		while($row = $DB->tabl_hash($groups))
		{
			$groupNames[$row['id']] = '#'.$row['id'] . ' ' . $row['name'] . ' - ' . $row['type'];
		}

		return $groupNames;
	}
	public function outputUserTable($User = null)
	{
		$Game = null;
		if( $this->gameID != null )
		{
			// This is associated with a game; if it is an anonymous game
			// we need to ensure the user ID is not shown.
			$Variant = libVariant::loadFromGameID($this->gameID);
			$Game = $Variant->Game($this->gameID);
		}
		return self::outputUserTable_static($this->GroupUsers, $User, $Game);
	}
	public static function outputUserTable_static($groupUsers, $User = null, $Game = null)
	{
		$userId = -1;
		$isModerator = false;
		$creatorId = -1; 

		if( $User != null )
		{
			$userId = $creatorId = $User->id;
			$isModerator = $User->type['Moderator'];	
		}
		
		$buf = '';
		$buf .= '<table class="rrInfo" style="text-align:center">';
		$buf .= '<tr><th style="text-align:right">Link / Type</th><th style="text-align:center">User / Rating</th><th style="text-align:center">Creator / Rating</th><th style="text-align:center">Moderator / Rating</th><th style="text-align:left">Created / Updated</th></tr>';
		foreach($groupUsers as $groupUser)
		{
			
			
			$buf .= '<tr>';
			$buf .= '<td style="text-align:right">';
			$buf .= '<a href="group.php?groupId='.$groupUser->groupId.'">#'.$groupUser->groupId.' '.$groupUser->groupName.'</a>';
			$buf .= ' <br /> ';
			$buf .= $groupUser->groupType;
			$buf .= '</td>';
			$buf .= '<td>';
			$buf .= User::profile_link_static($groupUser->userUsername, $groupUser->userId, $groupUser->userType, $groupUser->userPoints);
			$buf .= ' <br /> ';
			if( $userId == $groupUser->userId )
			{
				$buf .= self::getSelectWeighting('user', $groupUser->userId, $groupUser->userWeighting);
			}
			else
			{
				$buf .= self::getClosestWeightingName($groupUser->userWeighting);
			}
			
			$buf .= '</td>';
			$buf .= '<td>';
			$buf .= User::profile_link_static($groupUser->ownerUsername, $groupUser->createdByUserId, $groupUser->ownerType, $groupUser->ownerPoints);
			$buf .= ' <br /> ';
			if( $userId == $groupUser->createdByUserId )
			{
				$buf .= self::getSelectWeighting('owner', $groupUser->userId, $groupUser->ownerWeighting);
			}
			else
			{
				$buf .= self::getClosestWeightingName($groupUser->ownerWeighting);
			}
			
			$buf .= '</td>';
			$buf .= '<td>';
			if( $groupUser->modUserId )
			{
				$buf .= User::profile_link_static($groupUser->modUsername, $groupUser->modUserId, $groupUser->modType, $groupUser->modPoints);
			}
			else
			{
				$buf .= 'N/A';
			}
			$buf .= ' <br /> ';
			if( $isModerator )
			{
				$buf .= self::getSelectWeighting('mod', $groupUser->userId, $groupUser->modWeighting);
			}
			else
			{
				$buf .= self::getClosestWeightingName($groupUser->modWeighting);
			}
			$buf .= '</td>';
			$buf .= '<td style="text-align:left">';
			$buf .= libTime::text($groupUser->timeCreated);
			if( $groupUser->timeCreated != $groupUser->timeChanged)
			{
				$buf .= ' <br /> ';
				$buf .= libTime::text($groupUser->timeChanged);
			}
			$buf .= '</td>';
			$buf .= '</tr>';
		}
		$buf .= '</table>';
		return $buf;
	}
	
	private static $allowedWeightings = array(-100=>'Deny',-50=>'Doubt',0=>'None',33=>'Weak',66=>'Mid',100=>'Strong');
	private static function getClosestWeighting($givenWeighting)
	{
		$givenWeighting = intval($givenWeighting);
		foreach(self::$allowedWeightings as $weighting=>$weightingName)
		{
			if( $givenWeighting <= $weighting ) return $weighting;
		}
		return 0;
	}
	private static function getClosestWeightingName($givenWeighting)
	{
		$closestWeighting = self::getClosestWeighting($givenWeighting);
		return self::$allowedWeightings[$closestWeighting];
	}
	public static function getSelectWeighting($weightingType, $userId, $weighting)
	{
		$closestWeighting = self::getClosestWeightingName($weighting);
		$buf = '<select name="'.$weightingType.'Weighting'.$userId.'">';
		foreach(self::$allowedWeightings as $weighting=>$weightingName)
		{
			$buf .= '<option value=';
			$buf .= $weighting;
			
			if( $closestWeighting == $weightingName) {
				$buf .= ' selected ';
			}
			$buf .= '>';
			$buf .= $weightingName;
			$buf .= '</option>';
		}
		$buf .= '</select>';
		return $buf;
	}
}
