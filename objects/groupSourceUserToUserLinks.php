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
require_once(l_r('objects/groupSourceUserToUserLink.php'));

/**
 * An object representing a relationship between users. Many to many between users and groups,
 * intended for associating people who know each other in real life, to allow people who know
 * each other to play together in a transparent way. Also can be used to allow users to associate
 * with one another, moderators to group people 
 * 
 *
 * @package Base
 */
class GroupSourceUserToUserLinks
{
	private $links = array();

	public function __construct($links)
	{
		$this->links = $links;
	}
	private static function load($whereClause)
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT l.source, l.fromUserID, l.toUserID, 
				l.avgPositiveWeighting, l.maxPositiveWeighting, l.countPositiveWeighting, 
				l.avgNegativeWeighting, l.maxNegativeWeighting, l.countNegativeWeighting
			FROM wD_GroupSourceUserToUserLinks l
			WHERE ". $whereClause);
		$res = array();
		while($row = $DB->tabl_hash($tabl))
		{
			$res[] = new GroupSourceUserToUserLink($row);
		}
		return $res;
	}
	public static function loadFromGroupID($groupID)
	{
		return new GroupSourceUserToUserLinks(self::load("l.toUserID IN (SELECT userID FROM wD_GroupUsers WHERE groupID = ".$groupID.")"));
	}
	public static function loadFromUserID($userID)
	{
		return new GroupSourceUserToUserLinks(self::load("l.toUserID = ".$userID));
	}
	public static function loadFromGameID($gameID)
	{
		return new GroupSourceUserToUserLinks(self::load("l.toUserID IN (SELECT userID FROM wD_Members WHERE gameID = ".$gameID.")"));
	}
}
