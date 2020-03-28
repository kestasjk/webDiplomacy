UPDATE `wD_Misc` SET `value` = '162' WHERE `name` = 'Version';

ALTER TABLE `wD_Games`
ADD COLUMN `nextPhaseMinutes` int(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `phaseMinutes`,
ADD COLUMN `phaseSwitchPeriod` int(10) DEFAULT -1 AFTER `nextPhaseMinutes`,
ADD COLUMN `startTime` int(10) UNSIGNED DEFAULT NULL;

UPDATE wD_Games
SET nextPhaseMinutes = phaseMinutes
WHERE nextPhaseMinutes = 0;


ALTER TABLE `wD_Backup_Games`
ADD COLUMN `nextPhaseMinutes` int(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `phaseMinutes`,
ADD COLUMN `phaseSwitchPeriod` int(10) DEFAULT -1 AFTER `nextPhaseMinutes`,
ADD COLUMN `startTime` int(10) UNSIGNED DEFAULT NULL;

UPDATE wD_Backup_Games
SET nextPhaseMinutes = phaseMinutes
WHERE nextPhaseMinutes = 0;
