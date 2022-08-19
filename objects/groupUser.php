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
	var $groupID;

	/**
	 * The user ID
	 * @var int
	 */
	var $userID;

	// The username of the accounts involved, or the country names
	// if this is an anonymous suspicion:
	var $userUsername;
	/**
	 * The country name if applicable
	 * @var string|null
	 */
	var $userCountryName;
	public function userLink($type = 'User', $points = 100)
	{
		if( $this->isUserHidden() ) return '<strong>'.$this->userCountryName.'</strong>';
		else return User::profile_link_static($this->userUsername, $this->userID, $type, $points). ($this->userCountryName ? ' ('.$this->userCountryName.')' : '');
	}

	/**
	 * The country ID if applicable
	 * @var int|null
	 */
	var $countryID;

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
	var $modUserID;
	var $modUsername;
	public function modUsernameLink()
	{
		if( $this->modUsername == null ) return '<strong>N/A</strong>';

		return User::profile_link_static($this->modUsername, $this->modUserID, 'Moderator', 100);
	}

	// A link to the group this group-user link is part of:
	var $Group;

	/**
	 * Create a GroupUser object
	 * @param array $row Hash row containing the record data
	 * @param Group $Group The parent group
	 */
	public function __construct($row, Group $Group)
	{
		$this->Group = $Group;

		foreach ( $row as $name => $value )
		{
			$this->{$name} = $value;
		}
		
		if( $this->countryID )
		{
			$Variant = libVariant::loadFromVariantID($this->Group->gameVariantID);
			$this->userCountryName = $Variant->countries[$this->countryID-1];
		}
		if( $this->isUserHidden() )
		{
			$this->userUsername = $this->userCountryName;
			$this->userCountryName = $this->userCountryName;
		}
	}
	public function isUserHidden() { return $this->Group->isUserIDHidden($this->userID); }
	/**
	 * If it is a suspicion then a small mod weighting means verified, if it is a disclosure the user is trusted, but a mod setting of 100 will force the relationship
	 */
	public function isVerified() 
	{
		return $this->userWeighting > 0 || $this->modWeighting >= ( $this->Group->type == 'Unknown' ? 33 : 100);
	}
	public function isDenied() 
	{
		return $this->userWeighting < 0 || $this->modWeighting <= ( $this->Group->type == 'Unknown' ? -33 : -100);
	}
}
