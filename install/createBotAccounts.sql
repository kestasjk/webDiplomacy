INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot1', 'bot1@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot2', 'bot2@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot3', 'bot3@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot4', 'bot4@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot5', 'bot5@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot6', 'bot6@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('bot7', 'bot7@bot.com', 'Bot', 1154508107, 1154508107, '12345678');
INSERT INTO wD_ApiKeys(userID, apiKey) SELECT id, username FROM wD_Users WHERE username LIKE '%bot%';
INSERT INTO wD_ApiPermissions(userID, getStateOfAllGames, submitOrdersForUserInCD, listGamesWithPlayersInCD) 
SELECT id, 'Yes', 'Yes', 'Yes' FROM wD_Users WHERE username LIKE '%bot%';