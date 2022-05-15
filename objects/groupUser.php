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
class GroupUser
{
	/**
	 * The group ID
	 * @var int
	 */
	var $groupId;

	/**
	 * The group ID
	 * @var int
	 */
	var $userId;

	/**
	 * @var bool
	 */
	var $isActive;

	/**
	 * @var float -1.0 to 1.0
	 */
	var $userWeighting;

	/**
	 * @var float -1.0 to 1.0
	 */
	var $ownerWeighting;

	/**
	 * @var float -1.0 to 1.0
	 */
	var $modWeighting;

	/**
	 * @var int User ID that created this
	 */
	var $createdByUserId;

	/**
	 * @var int Time this was last changed
	 */
	var $timeCreated;

	/**
	 * @var int Time this was last changed
	 */
	var $timeChanged;

	/**
	 * @var int? The last moderator user ID that set a weighting, or null
	 */
	var $modUserId;

	// Used to render individual user links
	var $groupName;
	var $groupType;

	// Below vars used to render profile links:
	var $userUsername;
	var $userPoints;
	var $userType;
	var $ownerUsername;
	var $ownerPoints;
	var $ownerType;
	var $modUsername;
	var $modPoints;
	var $modType;

	/**
	 * Create a GroupUser object
	 * @param array $row Hash row containing the record data
	 */
	public function __construct($row)
	{
		/*
			"u.username userUsername, u.points userPoints, u.type userType, ".
			"o.username ownerUsername, o.points ownerPoints, o.type ownerType, ".
			"m.username modUsername, m.points modPoints, m.type modType ".
		*/
		foreach ( $row as $name => $value )
		{
			$this->{$name} = $value;
		}
	}

	/**
	 * If it is a suspicion then a small mod weighting means verified, if it is a disclosure the user is trusted, but a mod setting of 100 will force the relationship
	 */
	public function isVerified() 
	{
		return $this->userWeighting > 0 || $this->modWeighting >= ( $this->groupType == 'Unknown' ? 33 : 100);
	}
	public function isDenied() 
	{
		return $this->userWeighting < 0 || $this->modWeighting <= ( $this->groupType == 'Unknown' ? -33 : -100);
	}
}
