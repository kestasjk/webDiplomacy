UPDATE `wD_Misc` SET `value` = '156' WHERE `name` = 'Version';

ALTER TABLE `wD_MissedTurns` ADD `liveGame` BOOLEAN DEFAULT 0;  

update wD_MissedTurns m inner join wD_Games g on g.id = m.gameID set m.liveGame = 1 where g.phaseMinutes < 61 and g.id is not null;
