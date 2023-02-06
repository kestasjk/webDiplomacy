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


INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('fairbot1', 'fairbot1@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('FairBot2', 'fairbot2@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('fairbot3', 'fairbot3@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('fairbot4', 'fairbot4@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('fairbot5', 'fairbot5@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('fairbot6', 'fairbot6@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_Users(username, email, type, timeJoined, timeLastSessionEnded, password) VALUES ('fairbot7', 'fairbot7@bot.com', 'Bot,User', 1154508107, 1154508107, '12345678');
INSERT INTO wD_ApiKeys(userID, apiKey) SELECT id, username FROM wD_Users WHERE username LIKE '%fairbot%';
INSERT INTO wD_ApiPermissions(userID, getStateOfAllGames, submitOrdersForUserInCD, listGamesWithPlayersInCD) 
SELECT id, 'Yes', 'No', 'No' FROM wD_Users WHERE username LIKE 'FairBot2';