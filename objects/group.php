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
class GroupType
{
	var $name;
	var $display;
	
}
class Group
{
	/**
	 * The member ID
	 * @var int
	 */
	var $id;

	/**
	 * @var string
	 */
	var $name;

	/**
	 * The game ID
	 * @var int
	 */
	var $gameID;

    static $validTypes = array('UserSuspicion'=>array('User','OwnUser'),'ModSuspicion','Family','School','OffSite','RealLife','Community','ModPrivate','ModPublic');
    static $userTagDisplay = array('UserSuspicion'=>array('Moderator',,'ModSuspicion','Family','School','OffSite','RealLife','Community','ModPrivate','ModPublic');

	/**
	 * The type of group
     * 
	 * @var string
	 */
	var $type;

	/**
	 * Text with info about the group from the creator
     * 
	 * @var string[]
	 */
	var $description;
    
	/**
	 * Any notes from moderators
     * 
	 * @var string[]
	 */
	var $moderatorNodes;

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
}

