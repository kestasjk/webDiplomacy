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
    // If the current user is required to respond to something in a group this function will redirect them there.
    public static function redirectToGroup() 
    {
        global $User, $DB;

        if( $User->type['Moderator'] )
        {
            if( list($groupID) = $DB->sql_row("SELECT groupID FROM wD_GroupUsers WHERE (isWeightingWaiting = 1 AND modUserID = " . $User->id. ") OR (isMessageWaiting = 1 AND modUserID = " . $User->id. ")" ) )
            {
                header('refresh: 3; url=group.php?groupID='.$groupID);
                libHTML::notice("Redirecting to group panel", "A message or weighting you requested from a user has been provided; redirecting you to <a href='group.php?groupID=".$groupID."'>the group panel</a> now. Thank you!");
            }
            if( list($groupID) = $DB->sql_row("SELECT id FROM wD_Groups WHERE (isMessageWaiting = 1 AND modUserID = " . $User->id. ")" ) )
            {
                header('refresh: 3; url=group.php?groupID='.$groupID);
                libHTML::notice("Redirecting to group panel", "A message or weighting you requested from a user has been provided; redirecting you to <a href='group.php?groupID=".$groupID."'>the group panel</a> now. Thank you!");
            }
        }
        
        if( $User->type['User'] )
        {
            if( list($groupID) = $DB->sql_row("SELECT groupID FROM wD_GroupUsers WHERE (isWeightingNeeded = 1 AND userID = " . $User->id. ") OR (isMessageNeeded = 1 AND userID = " . $User->id. ")" ) )
            {
                header('refresh: 3; url=group.php?groupID='.$groupID);
                libHTML::notice("Redirecting to group panel", "A moderator has requested you provide a message or set a weighting for a group panel; redirecting you to <a href='group.php?groupID=".$groupID."'>the group panel</a> now. Thank you!");
            }
            if( list($groupID) = $DB->sql_row("SELECT id FROM wD_Groups WHERE (isMessageNeeded = 1 AND ownerUserID = " . $User->id. ")" ) )
            {
                header('refresh: 3; url=group.php?groupID='.$groupID);
                libHTML::notice("Redirecting to group panel", "A moderator has requested you provide a message or set a weighting for a group panel; redirecting you to <a href='group.php?groupID=".$groupID."'>the group panel</a> now. Thank you!");
            }
        }
    }

    // For all active games get all group data and use it to generate JSON that can display which users are in relationships.
    public static function generateGameRelationCache($lastUpdated)
    {
        global $DB;
        
        $DB->sql_script("
        -- Step one; find all groups that need their weightings recalculated. These are all users in any group that 
-- have been changed since the last refresh:

    -- Set records directly changed to dirty
        UPDATE wD_GroupUsers SET isDirty = 1 WHERE timeChanged > ".$lastUpdated.";
    
    -- Set records in the same group as also dirty
        UPDATE wD_GroupUsers dirtyRecords, wD_GroupUsers alsoInGroup
        SET alsoInGroup.isDirty = 1 
        WHERE dirtyRecords.isDirty = 1 
            AND alsoInGroup.isDirty = 0 
            AND dirtyRecords.groupID = alsoInGroup.groupID;
        
        -- Clear out the aggregation tables that are used to store calculated weightings before being aggregated up
        -- to the user - user level: (This prevents needing to recalc groups linked to groups linked to groups, by keeping
        -- recalcs within individual groups then reapplying the recalced groups to affected users, rather than recalcing every
        -- group for every affected user)
        DELETE FROM wD_GroupSourceJudgeUserWeightings WHERE groupID IN (SELECT DISTINCT groupID FROM wD_GroupUsers WHERE isDirty = 1);
        DELETE FROM wD_GroupSourceJudgeUserToUserWeightings WHERE groupID IN (SELECT DISTINCT groupID FROM wD_GroupUsers WHERE isDirty = 1);
        -- For the final table we need to delete and refresh the calculations for any user affected, to bring in the calcs from other groups
        DELETE FROM wD_GroupSourceUserToUserLinks 
        WHERE fromUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
            OR toUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1);
        DELETE FROM wD_GroupUserToUserLinks 
            WHERE fromUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
                OR toUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1);
            
    -- Extract the group structure from a record per user/owner/mod to a record for each weighting for each record, so 
    -- that one user/owner/mod record becomes three records, one for the user, one for the owner, one for the mod, each
    -- as the judge of the group relationship with a weighting, and the type/source of judge they are (user/owner/mod = self/peer/mod)
        INSERT INTO wD_GroupSourceJudgeUserWeightings (groupID, source, judgeUserID, userID, weighting)
        SELECT groupID, source, judgeUserID, userID, (agree + deny) weighting
        FROM (
            SELECT gu.groupID, 
                'Self' source,
                gu.userID,
                gu.userID judgeUserID, 
                IF(gu.userWeighting<0,gu.userWeighting/100.0,0) deny, 
                IF(gu.userWeighting>0,gu.userWeighting/100.0,0) agree
            FROM wD_GroupUsers gu 
            WHERE gu.isDirty = 1
            UNION ALL
            SELECT
                gu.groupID, 
                'Mod' source,
                gu.userID,
                gu.modUserID judgeUserID, 
                IF(gu.modWeighting<0,gu.modWeighting/100.0,0) deny, 
                IF(gu.modWeighting>0,gu.modWeighting/100.0,0) agree
            FROM wD_GroupUsers gu 
            WHERE gu.isDirty = 1 AND gu.modUserID IS NOT NULL
            UNION ALL
            SELECT
                gu.groupID,
                'Peer' source,
                gu.userID userID,
                g.ownerUserID judgeUserID,
                IF(gu.ownerWeighting<0,gu.ownerWeighting/100.0,0) deny, 
                IF(gu.ownerWeighting>0,gu.ownerWeighting/100.0,0) agree
            FROM wD_GroupUsers gu 
            INNER JOIN wD_Groups g ON g.id = gu.groupID
            WHERE gu.isDirty = 1 
        ) groupJudgeTypeUserWeightings;
      
      
      -- Now take the judge - user link and apply each weighting in the group to/from each user, so that weightings are on a user - user level within each group.
      -- (The grouping here is probably unnecessary as group logic should prevent one person being added multiple times to one group, but this just makes it clear
      -- that at this point there can't be multiple records for one group-judge-source-fromUser-toUser)
        INSERT INTO wD_GroupSourceJudgeUserToUserWeightings (groupID, source, judgeUserID, fromUserID, toUserID, toWeighting)
        SELECT a.groupID, a.source, a.judgeUserID, a.userID fromUserID, b.userID toUserID, MAX(b.weighting) toWeighting
        FROM wD_GroupSourceJudgeUserWeightings a
        INNER JOIN wD_GroupSourceJudgeUserWeightings b ON a.groupID = b.groupID AND a.source = b.source AND a.userID <> b.userID
        -- Ensure we are only reaggregating the dirty groups
        WHERE a.groupID IN (SELECT DISTINCT groupID FROM wD_GroupUsers WHERE isDirty = 1)
        GROUP BY a.groupID, a.judgeUserID, a.source, a.userID, b.userID;
        
        -- During this operation we aggregate the group/judge calculations so that it's simply a user to user link with a weighting, by the type/source 
        -- (self / peer / mod). This also as to take into account that a judge user may have created multiple groups with the same users associations,
        -- and those shouldn't be counted as if it was multiple people creating the associations.
        -- Also during this aggregation we need to aggregate not only the dirty recalculated records but all records for each user being included
        INSERT INTO wD_GroupSourceUserToUserLinks (source, fromUserID, toUserID, 
            avgPositiveWeighting, maxPositiveWeighting, countPositiveWeighting, 
            avgNegativeWeighting, maxNegativeWeighting, countNegativeWeighting)
        SELECT source, fromUserID, toUserID, 
            AVG(IF(toWeighting>0,toWeighting,0)) avgPositiveWeighting, 
            MAX(IF(toWeighting>0,toWeighting,0)) maxPositiveWeighting, 
            SUM(IF(toWeighting>0,1,0)) countPositiveWeighting, 
            AVG(IF(toWeighting<0,toWeighting,0)) avgNegativeWeighting, 
            MAX(IF(toWeighting<0,toWeighting,0)) maxNegativeWeighting, 
            SUM(IF(toWeighting<0,1,0)) countNegativeWeighting
        FROM (
            SELECT a.source, a.fromUserID, a.toUserID, toWeighting
            FROM wD_GroupSourceJudgeUserToUserWeightings a
            -- At this point we bring in all users 
            WHERE a.fromUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
                OR a.toUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
        ) x
        GROUP BY source, fromUserID, toUserID;

    INSERT INTO wD_GroupUserToUserLinks (fromUserID, toUserID, peerAvgScore, peerCount, modAvgScore, modCount, selfAvgScore, selfCount)
    SELECT p.fromUserID, p.toUserID, 
        COALESCE(p.avgScore peerAvgScore,0), COALESCE(p.count,0) peerCount, 
        COALESCE(m.avgScore modAvgScore,0), COALESCE(m.count,0) modCount, 
        COALESCE(s.avgScore selfAvgScore,0), COALESCE(s.count,0) selfCount
    FROM 
    (
        SELECT fromUserID, toUserID,
            (avgPositiveWeighting*countPositiveWeighting+avgNegativeWeighting*countNegativeWeighting)/(countPositiveWeighting+countNegativeWeighting) avgScore,
            (countPositiveWeighting+countNegativeWeighting) count
        FROM wd_groupsourceusertouserlinks
        WHERE SOURCE='Peer' AND (
            fromUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
            OR toUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
        )
    ) p
    LEFT JOIN (
        SELECT fromUserID, toUserID, 
            avgPositiveWeighting*countPositiveWeighting+avgNegativeWeighting*countNegativeWeighting avgScore,
            (countPositiveWeighting+countNegativeWeighting) count
        FROM wd_groupsourceusertouserlinks
        WHERE SOURCE='Mod' AND (
            fromUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
            OR toUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
        )
    ) m ON m.fromUserID = p.fromUserID AND m.toUserID = p.toUserID
    LEFT JOIN (
        SELECT fromUserID, toUserID,
            (avgPositiveWeighting*countPositiveWeighting+avgNegativeWeighting*countNegativeWeighting)/(countPositiveWeighting+countNegativeWeighting) avgScore,
            (countPositiveWeighting+countNegativeWeighting) count
        FROM wd_groupsourceusertouserlinks
        WHERE SOURCE='Self' AND (
            fromUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
            OR toUserID IN (SELECT DISTINCT userID FROM wD_GroupUsers WHERE isDirty = 1)
        )
    ) s ON s.fromUserID = p.fromUserID AND s.toUserID = p.toUserID;

-- Now that the update is done unset the dirty flag
UPDATE wD_GroupUsers SET isDirty = 0 WHERE isDirty = 1;

-- The user to user link info needs to be available for people to see, but one lump of data that contains every link across
-- every user would be too big of a dataset, and would grow with time, and querying it out of the DB for every place it should
-- be seen would be a large extra amount of data extraction where for every user you would need to query all their group links.

-- Instead group data should be made available in lumps based on the context it's needed in, with the lumps cached so a browser
-- can fetch it and display the data.
-- Group data needs to be available for all user-user group links for games, groups, and users, along with fingerprint info,
-- SMS link info, and Facebook/Google/Apple/Paypal links also. 
-- It needs to be available server-side just for specific tests, e.g. when a user joins a game that they meet the game criteria.
/*
SELECT * FROM wD_GroupSourceJudgeUserWeightings WHERE userID IN (76827, 77129);
SELECT * FROM wD_GroupSourceJudgeUserToUserWeightings WHERE fromUserID IN (76827, 77129) OR toUserID IN (76827, 77129) ;
SELECT * FROM wD_GroupSourceUserToUserLinks WHERE fromUserID IN (76827, 77129) OR toUserID IN (76827, 77129) ;
*/
");
        
        self::outputJSONGameCache();
    }

    private static function outputJSONGameCache()
    {
        global $DB;
	    
	    return;

        $DB->sql_put("COMMIT");
        //-- Per game-member JSON cache data
        $tabl = $DB->sql_tabl("
            SELECT m2.gameID, m2.countryID, m3.countryID, source, weighting, judgeCount
            FROM wD_Games g 
            INNER JOIN wD_Members m2 ON m2.gameID = g.id
            INNER JOIN wD_Members m3 ON m3.gameID = g.id AND m2.userID <> m3.userID
            INNER JOIN wD_Group_UserByUserBySourceWeights w ON m2.userID = w.fromUserID AND m3.userID = w.toUserID
            WHERE g.id IN (SELECT gameID FROM wd_Group_DirtyUsers u INNER JOIN wD_Members m ON m.userID = u.userID) AND g.gameOver = 'No';
        ");
        $perMemberData = array();
        while($row = $DB->tabl_hash($tabl))
        {
            if( !isset($perMemberData[$row['gameID']]) ) $perMemberData[$row['gameID']] = array();
            $perMemberData[$row['gameID']][$row['fromCountryID']] = array(
                'source'=>$row['source'],
                'weighting'=>$row['weighting'],
                'judgeCount'=>$row['judgeCount']
            );
        }

        $DB->sql_put("COMMIT");

        //-- Per user JSON cache data
        $tabl = $DB->sql_tabl("
            SELECT fromUserID, toUserID, source, weighting, judgeCount
            FROM wD_Group_UserByUserBySourceWeights w
            WHERE fromUserID IN (SELECT userID FROM wd_Group_DirtyUsers)
            ORDER BY fromUserID;
            ");
        $perUserData = array();
        while($row = $DB->tabl_hash($tabl))
        {
            
        }
    }
}
