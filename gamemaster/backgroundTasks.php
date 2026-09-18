<?php
/*
    Copyright (C) 2004-2025 Kestas J. Kuliukas

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

/**
 * This class runs background tasks for the gamemaster, such as updating stats, 
 * taking backups, cleaning up data, etc.
 * These run when gamemaster.php runs, but they aren't directly related to game processing
 * 
 * @package Gamemaster
 */
class libBackgroundTasks
{
    /**
     * Utility function to get a timestamp from Redis or return 0 if not yet set
     * @param mixed $key
     * @return int
     */
    private static function getRedisTimestamp($key)    
    {
        global $Redis;
        $time = $Redis->get($key);
        if( $time === false ) return 0;
        return (int)$time;
    }

    /**
     * The number of background tasks run by run()
     * @var int
     */
    private static $tasksRun = 0;

    /**
     * Record a finished task's time and queries under its own metrics (METRICS_GAMEMASTER_TASK_<name>_*)
     * @param string $name
     * @param array $start The marker from libMetrics::start() when the task began
     */
    private static function taskDone($name, $start)
    {
        self::$tasksRun++;
        libMetrics::record('GAMEMASTER_TASK_'.$name, $start);
    }

    /**
     * Run the background tasks; most are on a timer. Background tasks that are important and have to know when they were
     * last run use $Misc to save that data, ad-hoc tasks that just need to be run occasionally use Redis to store the last
     * run time.
     *
     * @return int The number of tasks that ran
     */
    public static function run()
    {
        global $DB, $Misc, $Redis;

        /*
        * - Update session table
        * - Update misc values (if running as admin/mod)
        * - Check last process time, pause processing/save current process time
        * - Check queue and games table for games to process, votes to enact, and system functions to perform
        */
        if( self::getRedisTimestamp('lastSessionTableUpdate') < (time() - 60*7) )
        {
            $taskStart = libMetrics::start();
            print l_t('Updating session table').'<br />';
            libGameMaster::updateSessionTable();

            $Redis->set('lastSessionTableUpdate', time());
            self::taskDone('SESSIONS', $taskStart);
        }

        if( self::getRedisTimestamp('lastOnlineUsersUpdate') < (time() - 60*7) )
        {
            $taskStart = libMetrics::start();
            print l_t('Updating online users list').'<br />';
            
            $statsDir=libCache::dirName('stats');
            $onlineFile=$statsDir.'/onlineUsers.json';
            $tabl=$DB->sql_tabl("SELECT userID FROM wD_Sessions");
            $onlineUsers=array();
            
            while(list($userID)=$DB->tabl_row($tabl))
                $onlineUsers[]=$userID;
            file_put_contents($onlineFile, 'onlineUsers=$A(['.implode(',',$onlineUsers).']);');

            $Redis->set('lastOnlineUsersUpdate', time());
            self::taskDone('ONLINEUSERS', $taskStart);
        }

        //- Update misc values (if running as admin/mod)
        if( $Misc->LastStatsUpdate < (time() - 60*7) )
        {
            $taskStart = libMetrics::start();
            miscUpdate::errorLog();
            miscUpdate::forum();
            miscUpdate::game();
            miscUpdate::user();
            miscUpdate::bots();
            
            // Update like counts for the forum every day (TODO: WIP to prevent counting likes constantly which this phpBB extension does)
            if( false && floor($Misc->LastStatsUpdate / (24*60*60)) < floor(time() / (24*60*60)) )
            {
                $DB->sql_put("UPDATE phpbb_users u
                    SET webdip_like_count = 0
                    UPDATE phpbb_users u
                    INNER JOIN (
                        SELECT p.poster_id, COUNT(*) AS likes
                        FROM phpbb_posts p
                        INNER JOIN phpbb_posts_likes l ON l.post_id = p.post_id
                        GROUP BY p.poster_id
                    ) x ON x.poster_id = u.user_id
                    SET u.webdip_like_count = x.likes;");
            }

            $Misc->LastStatsUpdate = time();
            self::taskDone('MISCSTATS', $taskStart);
        }

        if( $Misc->LastReliabilityRatingsUpdate < (time() - 60*60*(3 + rand(0,100)/100.0)) )
        {
            $taskStart = libMetrics::start();
            // Do an incremental update of the reliability ratings every few hours:
            print l_t('Updating user phase/year counts and reliability ratings').'<br />';
            libGameMaster::updateReliabilityRatings();
            $Misc->LastReliabilityRatingsUpdate = time();
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('RELIABILITY', $taskStart);
        }

        if( $Misc->LastReliabilityRatingsRefresh < (time() - 60*60*24*(3 + rand(0,100)/100.0)) )
        {
            $taskStart = libMetrics::start();
            // Update the reliability ratings from scratch every few days:
            // TODO: Diagnose why this is needed, incremental updates should do
            print l_t('Updating user phase/year counts and reliability ratings').'<br />';
            libGameMaster::updateReliabilityRatings(true);
            $Misc->LastReliabilityRatingsRefresh = time();
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('RELIABILITYREFRESH', $taskStart);
        }

        if( false && $Misc->LastNMRWarningUpdate < (time() - 60*7) )
        {
            $taskStart = libMetrics::start();
            print "Generating NMR warnings\n";
            
            $nmrWarningUpdateTime = time();

            $tabl = $DB->sql_tabl("SELECT u.username, u.email, m.userID, g.id gameID, g.name gameName, 
                    g.phaseMinutes, g.processTime, m.countryID, g.phase, g.phaseMinutesRB 
                FROM wD_Members m 
                INNER JOIN wD_Games g ON g.id = m.gameID 
                INNER JOIN wD_Users u ON u.id = m.userID 
                WHERE m.status = 'Playing' AND orderStatus = 'None' 
                    AND g.gameOver='No' AND g.processStatus = 'Not-processing' 
                    AND g.phaseMinutes > 60 
                    AND (
                        (
                            (COALESCE(phaseMinutesRB,0) <= 0 OR phase='Diplomacy') 
                            AND 100*(processTime - ".$nmrWarningUpdateTime.")/(60*g.phaseMinutes) < 20
                            AND 100*(processTime - ".$Misc->LastNMRWarningUpdate.")/(60*g.phaseMinutes) >= 20
                        ) 
                        OR (
                            COALESCE(phaseMinutesRB,0) > 0 
                            AND phase <> 'Diplomacy' 
                            AND 100*((processTime - ".$nmrWarningUpdateTime.")/(60*g.phaseMinutesRB)) < 20
                            AND 100*(processTime - ".$Misc->LastNMRWarningUpdate.")/(60*g.phaseMinutes) >= 20
                        )
                    ) 
                    AND g.playerTypes <> 'MemberVsBots' 
                    AND g.phase <> 'Retreats' AND g.phase <> 'Builds' /* Until these phases are behaving correctly */
                    AND g.sandboxCreatedByUserID IS NULL 
                    AND g.processTime > ".$nmrWarningUpdateTime."");
            
            // Aggregate warnings by user email address, so we only send one email per user
            $nmrWarningMessagesByUserEmail = array();
            while($row = $DB->tabl_hash($tabl))
            {
                $nmrWarningMessagesByUserEmail[$row['email']][] = $row;
            }
            $DB->sql_put("COMMIT");

            require_once(l_r('objects/mailer.php'));
            $Mailer = new Mailer();
            foreach($nmrWarningMessagesByUserEmail as $email => $warnings)
            {
                $username = $warnings[0]['username'];
                $links = array();
                foreach($warnings as $warning)
                {
                    $links[] = '<a href="https://webdiplomacy.net/board.php?gameID='.$warning['gameID'].'">'.
                        htmlentities($warning['gameName']).
                        '</a> - '.
                        l_t($warning['phase']).' - '.
                        '<strong>'.libTime::remainingText($warning['processTime']).' remaining</strong>';
                }
                print 'E-mailing '.$email.' about '.count($links).' games'."\n";
                $Mailer->Send(
                    array($email=>$username), 
                    l_t('NMR Warning: No orders submitted!'),
                    l_t("You haven't submitted orders for the following game(s), which will be processed soon (less than 20% of phase left)!<br><br>").
                    l_t("Not submitting orders will affect your reliability rating, and makes the game less enjoyable for others.<br><br>Please use the link(s) below to submit orders for these games asap!<br><br>").
                    "<ul><li>".implode('</li><li>',$links)."</li></ul>"
                );
            }

            $Misc->LastNMRWarningUpdate = $nmrWarningUpdateTime;
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('NMRWARNINGS', $taskStart);
        }

        // Update relationship groups every couple of days
        if( $Misc->LastGroupUpdate < (time() - 60*60*6*(2 + rand(0,100)/100.0)) )
        {
            $taskStart = libMetrics::start();
            $groupUpdateTime = time();
            print "Running group relationship updates\n";
        
            // Update the user group calculations
            require_once('lib/group.php');
            libGroup::generateGameRelationCache($Misc->LastGroupUpdate);
            
            $Misc->LastGroupUpdate = $groupUpdateTime;
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('GROUPS', $taskStart);
        }

        if( $Misc->LastUserConnectionsUpdate < (time() - 60*60*(3 + rand(0,100)/100.0)) )
        {
            $taskStart = libMetrics::start();
            $connectionUpdateTime = time();

            print "Running user connection updates\n";

            // Update the user connections
            require_once('gamemaster/userconnections.php');
            
            print l_t('Updating user connection stats').'<br />';
            libUserConnections::updateUserConnections($Misc->LastUserConnectionsUpdate);
        
            $Misc->LastUserConnectionsUpdate = $connectionUpdateTime;
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('USERCONNECTIONS', $taskStart);
        }

        if( $Misc->LastTidyWatchedGamesUpdate < (time() - 60*60*24*(7 + rand(0,100)/100.0)) )
        {
            $taskStart = libMetrics::start();
            print l_t('Clearing old watched game records').'<br />';
            $DB->sql_put("DELETE wg FROM wD_WatchedGames wg LEFT JOIN wD_Games g ON g.id = wg.gameID WHERE g.id IS NULL OR g.phase = 'Finished' OR g.gameOver <> 'No'");

            $Misc->LastTidyWatchedGamesUpdate = time();
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('WATCHEDGAMES', $taskStart);
        }

        if( $Misc->LastPointsCheckUpdate < (time() - 60*60*24*(4 + rand(0,100)/100.0)) )
        {
            $taskStart = libMetrics::start();
            $pointsCheckUpdateTime = time();

            print l_t('Ensuring all users have the minimum 100 points available').'<br />';
            
            // TODO: Look into why points balances drift from this, as when users join / leave / etc it 
            // should balance users at that point
            $DB->sql_put("UPDATE wD_Users u INNER JOIN (
                SELECT u.id, u.points, SUM(m.bet) pointsBet 
                FROM wD_Users u 
                LEFT JOIN wD_Members m ON m.userID = u.id 
                WHERE m.status = 'Playing' 
                GROUP BY u.id, u.points
            ) up ON up.id = u.id 
            SET u.points = u.points + (100 - (up.points + IF(up.pointsBet IS NULL, 0, up.pointsBet))) 
            WHERE up.points + IF(up.pointsBet IS NULL, 0, up.pointsBet) < 100");
            $DB->sql_put("COMMIT");

            $Misc->LastPointsCheckUpdate = $pointsCheckUpdateTime;
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('POINTSCHECK', $taskStart);
        }

        // Anonymous bot games are abandoned quickly, so prune them hourly rather than with the 17 hour cleanup below
        if( self::getRedisTimestamp('lastAnonBotGameCleanup') < (time() - 60*60) )
        {
            $taskStart = libMetrics::start();
            print "Cleaning up anonymous bot games\n";

            // Cancel bot games that haven't been used for an hour if they are anonymous:
            $DB->sql_put("UPDATE wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id SET g.gameOver='Drawn', g.phase= 'Finished' WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND (u.timeLastSessionEnded < UNIX_TIMESTAMP() - 2*60*60 AND u.timeJoined < UNIX_TIMESTAMP() - 2*60*60 AND m.timeLoggedIn < UNIX_TIMESTAMP() - 2*60*60 AND s.userID IS NULL) AND u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%';");
            $DB->sql_put("COMMIT");

            $Redis->set('lastAnonBotGameCleanup', time());
            self::taskDone('ANONBOTGAMES', $taskStart);
        }

        // Clean up old bot games every 17 hours, ensuring there aren't lots of sandbox etc games clogging things up
        if( $Misc->LastBotGameCleanup < (time() - 60*60*17) )
        {
            $taskStart = libMetrics::start();
            print "Cleaning up old bot games\n";
            
            $botGameCleanupTime = time();

            // Tidy up any sandbox / bot games as part of this process
            
            // Keep sandbox games from clogging things up using a hack for now, and ensure this doesn't cause paused games to error:
            $DB->sql_put("UPDATE wD_Games SET processTime = 2000000000, pauseTimeRemaining = NULL WHERE name LIKE 'SB_%' AND processStatus <> 'Paused'");
            $DB->sql_put("COMMIT");

            // Cancel sandbox games that haven't been accessed for a week, otherwise these clog things up:
            $DB->sql_put("UPDATE wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id LEFT JOIN wD_ApiKeys a ON a.userID = m.userID SET g.gameOver='Drawn', g.phase='Finished' WHERE g.phase IN ('Diplomacy','Retreats','Builds') AND a.userID IS NULL AND g.sandboxCreatedByUserID IS NOT NULL AND m.timeLoggedIn < UNIX_TIMESTAMP() - 24*60*60*7 AND g.gameOver = 'No';");
            $DB->sql_put("COMMIT");

            // Cancel bot games that haven't been used for two days if they are not anonymous:
            $DB->sql_put("UPDATE wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id SET g.gameOver='Drawn', g.phase= 'Finished' WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND (u.timeLastSessionEnded < UNIX_TIMESTAMP() - 4*24*60*60 AND u.timeJoined < UNIX_TIMESTAMP() - 4*60*60 AND m.timeLoggedIn < UNIX_TIMESTAMP() - 4*60*60 AND s.userID IS NULL) AND NOT u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%';");
            $DB->sql_put("COMMIT");

            // Update member status for games that have finished, which makes vote queries etc faster and ensures stats are right:
            $DB->sql_put("UPDATE wD_Members m INNER JOIN wD_Games g ON g.id = m.gameID SET m.status = 'Survived' WHERE m.status = 'Playing' AND g.phase = 'Finished';");
            $DB->sql_put("COMMIT");

            $Misc->LastBotGameCleanup = $botGameCleanupTime;
            $Misc->write();
            $DB->sql_put("COMMIT");
            self::taskDone('BOTGAMECLEANUP', $taskStart);
        }

        // Backup from wD_Backup_* to json files every 37 minutes, as MySQL doesn't allow backups without going offline
        // In case of failure this will at least mean games are not ruined
        if( $Misc->LastBackupUpdate < (time() - 60*37) )
        {
            $taskStart = libMetrics::start();
            // Restore with RESTOREGAMES RESTOREGAMEIDS=1234,1235,1236. This will output SQL which can be restored to the backup directory
            print "Backing up games<br />\n";

            /*
            Backup flow:
            gamemaster.php - Process game, back up to wD_Backup_*, mark as needing backup in wD_Backup_Log
            backgroundTasks.php - Copy from wD_Backup_* to json.gz files, mark as exported in wD_Backup_Log, update wD_Misc.LastBackupUpdate
            [Your custom process, batch script etc] - Send the json.gz files to offsite storage, update wD_Misc.LastBackupArchived once successfully complete
            Now as long as wD_Misc.LastBackupArchived and wD_Misc.LastBackupUpdate are less than an hour old we know all backups are safely stored offsite

            (Note: Test restores from backup every so often to ensure they are valid and working)
            */
            if( isset(Config::$gameBackupDirectory) )
            {
                if( !is_dir(Config::$gameBackupDirectory) )
                {
                    print 'Creating game backup directory '.Config::$gameBackupDirectory."<br />\n";
                    mkdir(Config::$gameBackupDirectory, 0700, true);
                }
                
                $backupTime = time();

                $tabl = $DB->sql_tabl("SELECT gameID FROM wD_Backup_Log WHERE timestamp >= ".$Misc->LastBackupUpdate);
                $gameIDs = array();
                while(list($gameID) = $DB->tabl_row($tabl))
                {
                    print 'Backing up '.$gameID;
                    $data = processGame::getBackupData($gameID);
                    $jsonData = json_encode($data);
                    // Gzip the contents to save space:
                    $gzData = gzencode($jsonData, 9);
                    file_put_contents(Config::$gameBackupDirectory.'/'.$gameID.'.json.gz', $gzData);
                    $gameIDs[] = $gameID;
                }
                print 'Marking games as exported'."<br />\n";
                if( count($gameIDs) > 0 )
                {
                    $DB->sql_put("UPDATE wD_Backup_Log SET isExported = 1 WHERE gameID IN (".implode(',', $gameIDs).")");
                }
                
                // ALso backup critical user data, as without this a restore of games couldn't be associated to new users
                print 'Backing up new users';
                $tabl = $DB->sql_tabl("SELECT id, username, email FROM wD_Users WHERE timeJoined >= ".$Misc->LastBackupUpdate);
                $newUserRows = array();
                while($row = $DB->tabl_row($tabl))
                {
                    $newUserRows[] = $row;
                }
                $DB->sql_put("COMMIT");
                if( count($newUserRows) > 0 )
                {
                    print 'Backing up '.count($newUserRows).' new users';
                    $jsonData = json_encode($newUserRows);
                    $gzData = gzencode($jsonData, 9);
                    file_put_contents(Config::$gameBackupDirectory.'/newUsers_'.$backupTime.'.json.gz', $gzData);
                }

                print 'Backups complete';
                
                $Misc->LastBackupUpdate = $backupTime;
                $Misc->write();
                $DB->sql_put("COMMIT");
            }
            self::taskDone('BACKUP', $taskStart);
        }

        return self::$tasksRun;
    }
}