<?php
/*

Utility code for managing the Bots

SELECT g.gameID, fromCountryID, toCountryID, COUNT(*) c, COUNT(DISTINCT g.turn) tr, COUNT(*)/COUNT(DISTINCT g.turn) cpertr, 
FROM_UNIXTIME(MAX(a.lastHit)+8*60*60) lastHit, FROM_UNIXTIME(MAX(timeSent)+8*60*60) lastMsg 
FROM wD_GameMessages g 
INNER JOIN wD_Members m ON m.countryID = g.fromCountryID AND m.gameID = g.gameID 
INNER JOIN wD_ApiKeys a ON a.userID = m.userID 
WHERE (a.userID > 180000 OR a.userID = 10) AND g.gameID > 719000 
GROUP BY g.gameID, fromCountryID, toCountryID 
ORDER BY MAX(timeSent) DESC
LIMIT 50;

SELECT a.apiKey, u.id, u.username, FROM_UNIXTIME(a.lastHit+8*60*60) lastHit, a.isChecked, a.multiplexOffset 
FROM wD_ApiKeys a 
INNER JOIN wD_Users u ON u.id = a.userID 
WHERE a.isChecked = 1;



*/