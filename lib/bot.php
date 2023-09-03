<?php

/*
    Copyright (C) 2004-2023 Kestas J. Kuliukas

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

 /**
  * Utility code for handling the bots
  *
  * @package Base
  */
 class libAuth
 {
    /*
    for ctx, wakeuptime in x['dialogue_wakeup_times'].items(): print(f"{ctx.gameID} = {wakeuptime}")
    KEYS /home/kestasjk/Desktop/webdiplomacy_bots/logs/games/game_*.json
    HGETALL /home/kestasjk/Desktop/webdiplomacy_bots/logs/games/game_x.json
    game_id
    last_serviced
    wakeup_time
    HGETALL /home/kestasjk/Desktop/webdiplomacy_bots/logs/games/game_x.json:0
    msg
    target_power

    -- Get the 10 latest messages from the full press bots:
    SELECT g.gameID, fromCountryID, toCountryID, COUNT(*) c, FROM_UNIXTIME(MAX(a.lastHit)+8*60*60) lastHit, FROM_UNIXTIME(MAX(timeSent)+8*60*60) lastMsg FROM wD_GameMessages g INNER JOIN wD_Members m ON m.countryID = g.fromCountryID AND m.gameID = g.gameID INNER JOIN wD_ApiKeys a ON a.userID = m.userID WHERE a.userID > 180000 GROUP BY g.gameID, fromCountryID, toCountryID ORDER BY MAX(timeSent) DESC LIMIT 10;

    -- Bot activity and key links
    SELECT u.id, a.apiKey, u.username, FROM_UNIXTIME(a.lastHit+8*60*60) lastHit, a.isChecked, a.multiplexOffset FROM wD_ApiKeys a INNER JOIN wD_Users u ON u.id = a.userID;

    -- Active bot vs members games:
    SELECT FROM_UNIXTIME(processTime) FROM wD_Games WHERE playerTypes = 'MemberVsBots' AND sandboxCreatedByUserID IS NULL AND phase <> 'Finished' ORDER BY processTime DESC;

    redis-cli -n 1 "KEYS *json:0*"
127.0.0.1:6379[1]> KEYS *json:0*
1) "/home/kestasjk/fair/logs/games/game_107217711_ENGLAND.json:0"
2) "/home/kestasjk/fair/logs/games/game_107217714_TURKEY.json:0"
3) "/home/kestasjk/fair/logs/games/game_107233527_RUSSIA.json:0"
4) "/home/kestasjk/fair/logs/games/game_107217716_RUSSIA.json:0"
5) "/home/kestasjk/fair/logs/games/game_107191924_TURKEY.json:0"
6) "/home/kestasjk/fair/logs/games/game_107233522_ENGLAND.json:0"
7) "/home/kestasjk/fair/logs/games/game_107217717_GERMANY.json:0"
8) "/home/kestasjk/fair/logs/games/game_107233524_ITALY.json:0"
9) "/home/kestasjk/fair/logs/games/game_107233526_AUSTRIA.json:0"
127.0.0.1:6379[1]> HGETALL "/home/kestasjk/fair/logs/games/game_107217711_ENGLAND.json:0"
1

-- 9:23am - Start 7 games
-- 10:02am - Orders submitted for all players


-- Clone a game many times, to test performance / capacity
INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_A')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_A') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_B')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_B') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');

INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_C')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_C') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');

INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_D')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_D') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');


INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_E')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_E') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');


INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_F')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_F') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');


INSERT INTO wD_Games (variantID     , turn , phase     , processTime , pot  , name, gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID)
SELECT variantID     , turn , 'Pre-game' phase     , UNIX_TIMESTAMP() processTime , pot  , CONCAT(name,'_G')           , gameOver , processStatus  , password         , potType          , pauseTimeRemaining , minimumBet , phaseMinutes , phaseMinutesRB , nextPhaseMinutes , phaseSwitchPeriod , anon , pressType , attempts , missingPlayerPolicy , directorUserID , minimumReliabilityRating , minimumNMRScore , minimumIdentityScore , drawType          , excusedMissedTurns , finishTime , playerTypes , startTime  , grCalculated , gameMasterUserID , relationshipLimit , suspicionLimit , identityRequirement , sandboxCreatedByUserID FROM wD_Games WHERE name='FullPressTest2';

INSERT INTO wD_Members (userID , gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag)
SELECT userID , (SELECT id FROM wD_Games WHERE name = 'FullPressTest2_G') gameID , countryID , status  , timeLoggedIn , bet , missedPhases , newMessagesFrom , supplyCenterNo , unitNo , votes , pointsWon , gameMessagesSent , orderStatus     , hideNotifications , excusedMissedTurns , groupTag
FROM wD_Members WHERE gameID = (SELECT id FROM wD_Games WHERE name = 'FullPressTest2');


    */
 }
