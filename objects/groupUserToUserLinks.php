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

require_once('objects/basic/set.php');
require_once('objects/groupUserToUserLink.php');

/**
 * An object representing a relationship between users. Many to many between users and groups,
 * intended for associating people who know each other in real life, to allow people who know
 * each other to play together in a transparent way. Also can be used to allow users to associate
 * with one another, moderators to group people 
 * 
 *
 * @package Base
 */
class GroupUserToUserLinks
{
	private $links = array();

	public function __construct($links)
	{
		$this->links = $links;
	}
	private static function load($whereClause)
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT l.fromUserID, l.toUserID, 
				l.peerAvgScore, l.peerCount, 
				l.modAvgScore, l.modCount, 
				l.selfAvgScore, l.selfCount
			FROM wD_GroupUserToUserLinks l
			WHERE ". $whereClause);
		$res = array();
		while($row = $DB->tabl_hash($tabl))
		{
			$res[] = new GroupUserToUserLink($row);
		}
		return $res;
	}
	public function outputTable($onlyIncludeUserIDs = false)
	{
		$links = $this->links;
		if( $onlyIncludeUserIDs !== false && is_array($onlyIncludeUserIDs) )
		{
			$links = array();
			foreach($this->links as $link)
				if( in_array($link->toUserID, $onlyIncludeUserIDs) || in_array($link->fromUserID, $onlyIncludeUserIDs) )
					$links[] = $link;
		}
		$buf = '<div class="hr"></div>';
		$buf .= '<table class="table"><tr><th>From</th><th>To</th><th>Self</th><th>Peer</th><th>Mod</th></tr>';
		if( count($links) == 0 )
		{
			$buf = '<tr><td colspan=5 style="text-align:center">No relationship links</td></tr>';
		}
		else
		{
			foreach($links as $link)
				$buf .= $link->outputRow();
		}
		$buf .= '</table>';
		$buf .= '<div class="hr"></div>';
		
		return $buf;
	}
	public static function loadFromGroupID($groupID)
	{
		return new GroupUserToUserLinks(self::load(
			"l.toUserID IN (SELECT userID FROM wD_GroupUsers WHERE groupID = ".$groupID.")
			AND l.fromUserID IN (SELECT userID FROM wD_GroupUsers WHERE groupID = ".$groupID.")
			"));
	}
	public static function loadFromUserID($userID)
	{
		return self::loadFromUserIDs(array($userID));
	}
	public static function loadFromUserIDs($toUserIDs, $fromUserIDs=null)
	{
		$whereClause = "l.toUserID in (".implode(',',$toUserIDs).")";
		if( $fromUserIDs != null )
			$whereClause .= " AND l.fromUserID IN (".implode(',',$fromUserIDs).")";
		return new GroupUserToUserLinks(self::load($whereClause));
	}
	public static function loadFromGameID($gameID)
	{
		return new GroupUserToUserLinks(self::load("l.toUserID IN (SELECT userID FROM wD_Members WHERE gameID = ".$gameID.")"));
	}
	public static function loadFromGame($Game, $membersOnly = true)
	{
		$userIDs = array_keys($Game->Members->ByUserID);
		$links = self::loadFromUserIDs($userIDs, $membersOnly ? $userIDs : null);
		$links->applyGame($Game);
		return $links;
	}
	public static function loadFromUser($User)
	{
		$links = self::loadFromUserIDs(array($User->id));
		$UsersByUserID = array();
		$UsersByUserID[$User->id] = $User;
		foreach($links->links as $link)
			$UsersByUserID[$link->fromUserID] = new User($link->fromUserID);
		$links->applyUsers($UsersByUserID);
		return $links;
	}
	public static function loadFromGroup($Group)
	{
		$userIDs = array();
		foreach($Group->GroupUsers as $groupUser)
			$userIDs[] = $groupUser->userID;
		$links = self::loadFromUserIDs($userIDs, $userIDs);
		$links->applyGroup($Group);
		return $links;
	}
	public function applyGroup($Group, $filter=true)
	{
		foreach($this->links as $link) $link->applyGroup($Group);
		if( $filter ) $this->includeScoresAbove(1,1000,1);
	}
	public function applyGame($Game, $filter=true)
	{
		foreach($this->links as $link) $link->applyGame($Game);
		if( $filter ) $this->includeScoresAbove(1,1000,1);
	}
	public function applyUsers($UsersByUserID, $filter=true)
	{
		foreach($this->links as $link) $link->applyUsers($UsersByUserID);
		if( $filter ) $this->includeScoresAbove(1,1000,1);
	}
	private static function returnScoresAbove($inputLinks, $minimumModScore, $minimumPeerScore, $minimumSelfScore)
	{
		$links = array();
		foreach($inputLinks as $link)
		{
			if( $link->modAvgScore >= $minimumModScore )
				$links[] = $link;
			else if ( $link->peerAvgScore >= $minimumPeerScore )
				$links[] = $link;
			else if ( $link->selfAvgScore >= $minimumSelfScore )
				$links[] = $link;
		}
		return $links;
	}
	private function includeScoresAbove($minimumModScore, $minimumPeerScore, $minimumSelfScore)
	{
		$this->links = self::returnScoresAbove($this->links, $minimumModScore, $minimumPeerScore, $minimumSelfScore);
	}
	public function getUserIDsOverThreshold($minimumModScore, $minimumPeerScore, $minimumSelfScore)
	{
		$userIDs = array();
		foreach(self::returnScoresAbove($this->links, $minimumModScore, $minimumPeerScore, $minimumSelfScore) as $link)
		{
			$userIDs[] = $link->toUserID;
		}
		return $userIDs;
	}		
}
