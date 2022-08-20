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

require_once('objects/group.php');
require_once('objects/groupUser.php');

/**
 * A library for managing groups on a site-wide level; stats, caching, search, 
 * notifications, etc
 *
 * @package Base
 * @subpackage Group
 */
class libGroup
{
    // For all active games get all group data and use it to generate JSON that can display which users are in relationships.
    public static function generateGameRelationCache()
    {
        global $DB;
        
        $DB->sql_put("COMMIT");
        // Find all users that need their stats updated
        $DB->sql_put("DROP TABLE IF EXISTS wd_Group_DirtyUsers;");
        $DB->sql_put("CREATE TABLE wd_Group_DirtyUsers SELECT DISTINCT userID FROM wD_GroupUsers WHERE timeChanged > lastUpdateTimestamp AND isActive = 1;");
        
        // Mark all users that need to be recalculated
        $DB->sql_put("UPDATE wD_GroupUsers SET isDirty = 1 WHERE isActive = 1 AND userID IN (SELECT userID FROM wd_Group_DirtyUsers);");
        
        $DB->sql_put("COMMIT");

        // Calculate group-source-user-judge-weighting
        $DB->sql_put("DROP TABLE IF EXISTS wD_Group_Staging;");
        $DB->sql_put("CREATE TABLE wD_Group_Staging
        SELECT groupID, source, userID, judgeUserID, (agree + deny) weighting
        FROM (
            SELECT gu.groupID, 
                'Self' source,
                gu.userID,
                gu.userID judgeUserID, 
                IF(gu.userWeighting<0,-gu.userWeighting/100.0,0) deny, 
                IF(gu.userWeighting>0,gu.userWeighting/100.0,0) agree
            FROM wD_GroupUsers gu 
            WHERE gu.isDirty = 1
            UNION ALL
            SELECT
                gu.groupID, 
                'Mods' source,
                gu.userID,
                gu.modUserID judgeUserID, 
                IF(gu.modWeighting<0,-gu.modWeighting/100.0,0) deny, 
                IF(gu.modWeighting>0,gu.modWeighting/100.0,0) agree
            FROM wD_GroupUsers gu 
            WHERE gu.isDirty = 1 AND gu.modUserID IS NOT NULL
            UNION ALL
            SELECT
                gu.groupID,
                'Peers' source,
                gu.userID userID,
                0 judgeUserID,
                IF(gu.ownerWeighting<0,-gu.ownerWeighting/100.0,0) deny, 
                IF(gu.ownerWeighting>0,gu.ownerWeighting/100.0,0) agree
            FROM wD_GroupUsers gu 
            WHERE gu.isDirty = 1
        ) X;");
        
        $DB->sql_put("COMMIT");

        // Bring in the owner/accuser information, excluding any double-weightings group-source-user-judge-weighting
        // TODO: This would be a good place to add weightings based on the accuracy of the owner
        $DB->sql_put("DROP TABLE IF EXISTS wD_Group_Staging_ByUser;");
        $DB->sql_put("CREATE TABLE wD_Group_Staging_ByUser
        SELECT a.groupID, 
            a.source,
            a.userID, 
            IF(a.judgeUserID=0,g.ownerUserID,a.judgeUserID) judgeUserID,
            a.weighting weighting
        FROM wD_Group_Staging a
        INNER JOIN wD_Groups g ON g.id = a.groupID
        WHERE NOT (a.judgeUserID = 0 AND g.ownerUserID = a.userID);");
        // No extra score from voting twice for yourself
        
        $DB->sql_put("COMMIT");
        
        // Calculate group-source-judge-userA-userB-weighting , getting each judges most significant weighting for each user pair in each group
        $DB->sql_put("DROP TABLE IF EXISTS wD_Group_Staging_UserToUser_ByGroup;");
        $DB->sql_put("CREATE TABLE wD_Group_Staging_UserToUser_ByGroup
        SELECT groupID, SOURCE, judgeUserID, fromUserID, toUserID, IF(ABS(maxWeighting)>ABS(minWeighting),maxWeighting,minWeighting) weighting
        FROM (
            SELECT a.groupID, a.source, a.judgeUserID, a.userID fromUserID, b.userID toUserID, MAX(a.weighting) maxWeighting, MIN(a.weighting) minWeighting
            FROM wD_Group_Staging_ByUser a
            INNER JOIN wD_Group_Staging_ByUser b ON a.groupID = b.groupID AND a.source = b.source AND a.userID <> b.userID
            GROUP BY a.groupID, a.source, a.judgeUserID, a.userID, b.userID
        ) judgeMax;");
        
        // DROP TABLE IF EXISTS wD_Group_UserByUserBySourceWeights;
        // CREATE TABLE wD_Group_UserByUserBySourceWeights
        
        // Remove the records to be replaced:
        $DB->sql_put("DELETE FROM wD_Group_UserByUserBySourceWeights WHERE fromUserID IN (SELECT userID FROM wd_Group_DirtyUsers);");
        // Calculate userA-userB-source-weighting ; the weights that link each user to each other user
        $DB->sql_put("INSERT INTO wD_Group_UserByUserBySourceWeights (fromUserID, toUserID, SOURCE, weighting, judgeCount)
        SELECT fromUserID, toUserID, source, IF(ABS(maxWeighting)>ABS(minWeighting),maxWeighting,minWeighting) weighting, judgeCount
        FROM (
            SELECT fromUserID, toUserID, source, MAX(weighting) maxWeighting, MIN(weighting) minWeighting, COUNT(*) judgeCount
            FROM wD_Group_Staging_UserToUser_ByGroup
            GROUP BY fromUserID, toUserID, source
        ) USER2user;");

        // Reset the dirty flag
        $DB->sql_put("UPDATE wD_GroupUsers SET isDirty = 0 WHERE isDirty = 1;");

        $DB->sql_put("COMMIT");

        // Group weightings are now up to date
        
        $DB->sql_put("DROP TABLE IF EXISTS wD_Group_Staging;");
        $DB->sql_put("DROP TABLE IF EXISTS wD_Group_Staging_ByUser;");
        $DB->sql_put("DROP TABLE IF EXISTS wD_Group_Staging_UserToUser_ByGroup;");
        
        self::outputJSONGameCache();
    }

    private static function outputJSONGameCache()
    {
        global $DB;

        $DB->sql_put("COMMIT");
        //-- Per game-member JSON cache data
        $tabl = $DB->sql_tabl("
            SELECT m2.gameID, m1.countryID, m2.countryID, source, weighting, judgeCount
            FROM wD_Group_UserByUserBySourceWeights w
            INNER JOIN wD_Members m1 ON m1.userID = w.fromUserID
            INNER JOIN wD_Games g ON g.id = m1.gameID
            INNER JOIN wD_Members m2 ON m2.userID = w.toUserID AND m2.gameID = g.id
            WHERE fromUserID IN (SELECT userID FROM wd_Group_DirtyUsers) AND g.gameOver = 'No' AND m1.`status` = 'Playing'
            ORDER BY m2.gameID, m1.countryID;");
        $perMemberData = array();
        while($row = $DB->tabl_hash($tabl))
        {
            
        }

        $DB->sql_put("COMMIT");

        //-- Per user JSON cache data
        $tabl = $DB->sql_tabl("SELECT fromUserID, toUserID, source, weighting, judgeCount
            FROM wD_Group_UserByUserBySourceWeights w
            WHERE fromUserID IN (SELECT userID FROM wd_Group_DirtyUsers)
            ORDER BY fromUserID;");
        $perUserData = array();
        while($row = $DB->tabl_hash($tabl))
        {

        }
    }
}
