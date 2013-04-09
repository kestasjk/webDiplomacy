ALTER TABLE `wD_PointsTransactions` CHANGE `type` `type` ENUM( 'Supplement', 'Bet', 'Won', 'Returned', 'Trigger', 'Correction' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `wD_ForumMessages`  ADD `likeCount` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0';

UPDATE wD_ForumMessages fm 
INNER JOIN (
	SELECT f.id, COUNT(*) as likeCount
	FROM wD_ForumMessages f
	INNER JOIN wD_LikePost lp ON f.id = lp.likeMessageID
	GROUP BY f.id
) l ON l.id = fm.id
SET fm.likeCount = l.likeCount;

ALTER TABLE `wD_Members` CHANGE `newMessagesFrom` `newMessagesFrom` SET( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `wD_Members` CHANGE `votes` `votes` set('Draw','Pause','Cancel') NOT NULL DEFAULT '';
ALTER TABLE `wD_Members` CHANGE `countryID` `countryID` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT 0;  

UPDATE `wD_Misc` SET `value` = '132' WHERE `name` = 'Version';