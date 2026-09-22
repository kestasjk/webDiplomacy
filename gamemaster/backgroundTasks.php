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
     * End the games a bot game cleanup has picked out the way a draw vote does, through processGame::setDrawn():
     * the moves and territories are archived, the members drawn and messaged, and the units, territories and
     * orders cleared, as they are for any game that finishes. The cleanups used to end a game with an UPDATE of
     * wD_Games instead, which left the board on the table and the final turn out of the archive.
     *
     * Each game is ended in its own transaction, and it stops at the deadline rather than holding the gamemaster
     * up; whatever it didn't get to is picked out again next time.
     *
     * @param string $gameIDsSql A query for the id and variantID of the games to end
     * @param float $deadline The microtime(true) to stop at
     * @return bool Whether every game selected was dealt with
     */
    private static function drawIdleGames($gameIDsSql, $deadline)
    {
        global $DB;

        $games = array();
        $tabl = $DB->sql_tabl($gameIDsSql);
        while( list($gameID, $variantID) = $DB->tabl_row($tabl) )
            $games[(int)$gameID] = (int)$variantID;
        $DB->sql_put("COMMIT");

        $drawn = 0;
        foreach($games as $gameID => $variantID)
        {
            if( microtime(true) > $deadline )
            {
                print "Ended ".$drawn." of ".count($games)." idle bot games; the rest are left for the next run\n";
                return false;
            }

            try
            {
                $DB->sql_put("BEGIN");
                $Variant = libVariant::loadFromVariantID($variantID);
                $Game = $Variant->processGame($gameID, UPDATE);

                // Checked again now it's locked, in case it finished between being selected and now
                if( $Game->gameOver == 'No' && in_array($Game->phase, array('Diplomacy','Retreats','Builds')) )
                {
                    $Game->setDrawn();
                    $drawn++;
                }
                $DB->sql_put("COMMIT");
            }
            catch(Exception $e)
            {
                $DB->sql_put("ROLLBACK");
                Game::wipeTurnPhaseCache($gameID);
                print "Couldn't end idle bot game ".$gameID.": ".$e->getMessage()."\n";
            }

            // Nothing else tells its clients the game has ended
            libGameFiles::refresh($gameID);
        }

        if( $drawn > 0 )
            print "Ended ".$drawn." idle bot games\n";

        return true;
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

            $Misc->LastStatsUpdate = time();
            // Without this write the timestamp only reached wD_Misc when a later task in the same run
            // happened to call write() (the backup, every 37 minutes), so these counts - among them a
            // COUNT over every finished game - were recounted on most gamemaster cycles instead of
            // every seven minutes.
            $Misc->write();
            $DB->sql_put("COMMIT");
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

        // Ghost Ratings ranks, read as columns by User::getCurrentGRByCategory() and halloffame.php
        if( self::getRedisTimestamp('lastGhostRatingRanksUpdate') < (time() - 60*30) )
        {
            $taskStart = libMetrics::start();
            print l_t('Updating Ghost Ratings ranks').'<br />';
            self::updateGhostRatingRanks();

            $Redis->set('lastGhostRatingRanksUpdate', time());
            self::taskDone('GRRANKS', $taskStart);
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

            // End bot games that haven't been used for an hour if they are anonymous. Sandbox games are
            // bot games too and are left to the 31 day cleanup below; they were told apart by their SB_ name
            // prefix, which is only a convention, where sandboxCreatedByUserID is what actually marks one.
            //
            // A play-now account is never given a wD_Sessions row and so never has its timeLastSessionEnded
            // moved on (see User::online()), which leaves m.timeLoggedIn - when the player was last seen in
            // this game - as the only thing keeping a game they are still playing out of this. Submitting
            // orders (api.php's game/orders) and opening the board (api/responses/player_context.php) both
            // keep it current; board.php used to be the only thing that did.
            $finished = self::drawIdleGames("SELECT DISTINCT g.id, g.variantID FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.phase IN ('Diplomacy','Retreats','Builds') AND g.playerTypes = 'MemberVsBots' AND (u.timeLastSessionEnded < UNIX_TIMESTAMP() - 2*60*60 AND u.timeJoined < UNIX_TIMESTAMP() - 2*60*60 AND m.timeLoggedIn < UNIX_TIMESTAMP() - 2*60*60 AND s.userID IS NULL) AND u.username LIKE 'diplonow_%' AND g.sandboxCreatedByUserID IS NULL ORDER BY g.id",
                microtime(true) + 10);

            // If it ran out of time it runs again next time rather than in an hour
            if( $finished )
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
            
            // Keep sandbox games from clogging things up using a hack for now, and ensure this doesn't cause paused games to error.
            // (By sandboxCreatedByUserID, not by the SB_ name prefix, so that a sandbox is parked however it is named.)
            $DB->sql_put("UPDATE wD_Games SET processTime = 2000000000, pauseTimeRemaining = NULL WHERE sandboxCreatedByUserID IS NOT NULL AND processStatus <> 'Paused' AND phase NOT IN ('Finished','Pre-game')");
            $DB->sql_put("COMMIT");

            // The cleanups below share this, and if they run out of it the whole task runs again next time
            $botGameCleanupDeadline = microtime(true) + 20;

            /*
             * End sandbox games that haven't been accessed for 31 days, otherwise these clog things up.
             *
             * A sandbox's countries are all held by its creator, and board.php only kept the timeLoggedIn
             * of the one country it mapped them to up to date, so as a join on any member being a week
             * stale this ended every sandbox a week after it was created however much it was being used.
             * (The board marks all of their countries as seen now; see player_context.php.)
             * It takes a game now only when none of its members has been seen inside 31 days - and still
             * leaves alone any game a member with an API key is in, as the join on wD_ApiKeys did.
             */
            $finished = self::drawIdleGames("SELECT g.id, g.variantID FROM wD_Games g
                WHERE g.phase IN ('Diplomacy','Retreats','Builds') AND g.gameOver = 'No' AND g.sandboxCreatedByUserID IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM wD_Members m
                        LEFT JOIN wD_ApiKeys a ON a.userID = m.userID
                        WHERE m.gameID = g.id
                            AND ( m.timeLoggedIn > UNIX_TIMESTAMP() - 31*24*60*60 OR a.userID IS NOT NULL )
                    )
                ORDER BY g.id", $botGameCleanupDeadline);

            // End bot games that haven't been used for 31 days if they are not anonymous (sandbox games
            // are excluded by sandboxCreatedByUserID rather than by their name, as above):
            $finished = self::drawIdleGames("SELECT DISTINCT g.id, g.variantID FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.phase IN ('Diplomacy','Retreats','Builds') AND g.playerTypes = 'MemberVsBots' AND (u.timeLastSessionEnded < UNIX_TIMESTAMP() - 31*24*60*60 AND m.timeLoggedIn < UNIX_TIMESTAMP() - 31*24*60*60 AND s.userID IS NULL) AND NOT u.username LIKE 'diplonow_%' AND g.sandboxCreatedByUserID IS NULL ORDER BY g.id",
                $botGameCleanupDeadline) && $finished;

            // Update member status for games that have finished, which makes vote queries etc faster and ensures stats are right:
            $DB->sql_put("UPDATE wD_Members m INNER JOIN wD_Games g ON g.id = m.gameID SET m.status = 'Survived' WHERE m.status = 'Playing' AND g.phase = 'Finished';");
            $DB->sql_put("COMMIT");

            if( $finished )
            {
                $Misc->LastBotGameCleanup = $botGameCleanupTime;
                $Misc->write();
            }
            $DB->sql_put("COMMIT");
            self::taskDone('BOTGAMECLEANUP', $taskStart);
        }

        /*
         * Clear away the boards left behind by the cleanups above as they used to be.
         *
         * They ended a game with an UPDATE of wD_Games instead of drawing it (they draw it properly now; see
         * drawIdleGames()), so the units, territory statuses and orders that Game::process() deletes when a
         * game reaches the Finished phase were left on the table. That was most of all three tables: in September 2026 6.7m of the 7.4m rows
         * in wD_Units, and a similar share of wD_TerrStatus and wD_Orders, belonged to bot games which
         * had ended, and every live game's queries were carrying them.
         *
         * A board is kept for a week after its game ended, because a board still on the table is what
         * makes a game which was ended in error recoverable (see restoreCancelledSandboxGames in
         * admin/adminActionsRestricted.php). Sandbox games are never touched: they are their creator's
         * own work rather than a game the server handed out, so they stay recoverable indefinitely.
         *
         * When a game ended is finishTime, which the cleanups now record as setWon()/setDrawn() do.
         * Where it is NULL the game was ended before they did, and processTime stands in for it: that
         * is the deadline of the phase the game was ended in, which is at or after the moment it ended.
         *
         * The work is spread over many runs: each one walks wD_Units from where the last one stopped,
         * in gameID order, which is an index jump per game rather than a scan of the table, until its
         * budget is used up. Once a pass reaches the end of the table it stops, and the next pass starts
         * a day later rather than straight away; walking the table every minute for nothing was most of
         * the database's time once the backlog was gone.
         */
        if( self::getRedisTimestamp('lastFinishedBoardTidy') < (time() - 60)
            && ( self::getRedisTimestamp('finishedBoardTidyCursor') > 0
                || self::getRedisTimestamp('lastFinishedBoardTidyPass') < (time() - 24*60*60) ) )
        {
            $taskStart = libMetrics::start();

            $tidyCutoff = time() - 7*24*60*60;
            $tidyDeadline = microtime(true) + 3;
            $tidyCursor = self::getRedisTimestamp('finishedBoardTidyCursor'); // Not a timestamp; the last gameID looked at
            $tidiedGames = 0;

            do
            {
                $gameIDs = array();
                $tabl = $DB->sql_tabl("SELECT DISTINCT gameID FROM wD_Units WHERE gameID > ".$tidyCursor." ORDER BY gameID LIMIT 200");
                while( list($gameID) = $DB->tabl_row($tabl) )
                    $gameIDs[] = (int)$gameID;

                if( count($gameIDs) == 0 )
                {
                    // The end of the table. The next pass starts a day from now: nothing ends a game without
                    // clearing its board any more, so once the backlog is gone a pass mostly finds nothing
                    $tidyCursor = 0;
                    $Redis->set('lastFinishedBoardTidyPass', time());
                    break;
                }

                $tidyCursor = $gameIDs[count($gameIDs)-1];

                $tidyIDs = array();
                $tabl = $DB->sql_tabl("SELECT id FROM wD_Games
                    WHERE id IN (".implode(',',$gameIDs).")
                        AND phase = 'Finished' AND sandboxCreatedByUserID IS NULL
                        AND ( finishTime < ".$tidyCutoff." OR ( finishTime IS NULL AND processTime < ".$tidyCutoff." ) )");
                while( list($gameID) = $DB->tabl_row($tabl) )
                    $tidyIDs[] = (int)$gameID;

                if( count($tidyIDs) )
                {
                    $tidiedGames += count($tidyIDs);
                    $tidyIDs = implode(',', $tidyIDs);

                    $DB->sql_put("DELETE FROM wD_Units WHERE gameID IN (".$tidyIDs.")");
                    $DB->sql_put("DELETE FROM wD_TerrStatus WHERE gameID IN (".$tidyIDs.")");
                    $DB->sql_put("DELETE FROM wD_Orders WHERE gameID IN (".$tidyIDs.")");
                    $DB->sql_put("COMMIT");
                }
            } while( microtime(true) < $tidyDeadline );

            $Redis->set('finishedBoardTidyCursor', $tidyCursor);
            $Redis->set('lastFinishedBoardTidy', time());

            if( $tidiedGames > 0 )
                print "Cleared the boards left behind by ".$tidiedGames." finished games\n";

            self::taskDone('FINISHEDBOARDTIDY', $taskStart);
        }

        // Apply the results of sending push notifications (see libPush::drain()) on every run; this is two Redis
        // requests when there's nothing to do
        $taskStart = libMetrics::start();
        if( libPush::applySendResults() > 0 )
            self::taskDone('PUSHRESULTS', $taskStart);

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

    /**
     * Recalculate the rank columns on wD_GhostRatings: ratingRank and peakRank within each category,
     * and peakRankActive, the peak rank counting only users active in the last six months.
     *
     * These used to be counted on every page view - once per category on a profile, twice more on the
     * hall of fame - over a table with no index on rating, behind a Redis cache whose key included the
     * viewer's own rating and so never hit. A rank only moves when a finished game changes somebody's
     * rating, so recalculating them on a timer is enough.
     *
     * RANK() gives ties the same rank, which is what the counts it replaces did (one more than the
     * number of players strictly above). The WHERE writes only the rows whose rank actually moved, so
     * after the first run this touches very little.
     */
    private static function updateGhostRatingRanks()
    {
        global $DB;

        // The same six month window the hall of fame's "active" lists use
        $sixMonths = 15552000;

        $DB->sql_put("UPDATE wD_GhostRatings g
            INNER JOIN (
                SELECT userID, categoryID,
                    RANK() OVER ( PARTITION BY categoryID ORDER BY rating DESC ) AS ratingRank,
                    RANK() OVER ( PARTITION BY categoryID ORDER BY peakRating DESC ) AS peakRank
                FROM wD_GhostRatings
            ) r ON ( r.userID = g.userID AND r.categoryID = g.categoryID )
            LEFT JOIN (
                SELECT a.userID, a.categoryID,
                    RANK() OVER ( PARTITION BY a.categoryID ORDER BY a.peakRating DESC ) AS peakRankActive
                FROM wD_GhostRatings a
                INNER JOIN wD_Users u ON ( u.id = a.userID )
                WHERE u.timeLastSessionEnded > UNIX_TIMESTAMP() - ".$sixMonths."
            ) ra ON ( ra.userID = g.userID AND ra.categoryID = g.categoryID )
            SET g.ratingRank = r.ratingRank,
                g.peakRank = r.peakRank,
                g.peakRankActive = ra.peakRankActive
            WHERE NOT ( g.ratingRank <=> r.ratingRank )
                OR NOT ( g.peakRank <=> r.peakRank )
                OR NOT ( g.peakRankActive <=> ra.peakRankActive )");

        $DB->sql_put("COMMIT");
    }
}