ALTER TABLE `wD_Games`
ADD COLUMN `nextPhaseMinutes` int(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `phaseMinutes`
ADD COLUMN `phaseSwitchPeriod` int(10) DEFAULT -1 AFTER `nextPhaseMinutes`,
ADD COLUMN `createTime` int(10) UNSIGNED DEFAULT NULL,
ADD COLUMN `startTime` int(10) UNSIGNED DEFAULT NULL;

UPDATE wD_Games
SET nextPhaseMinutes = phaseMinutes
WHERE nextPhaseMinutes = 0;

