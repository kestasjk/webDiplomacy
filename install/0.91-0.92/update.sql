UPDATE wD_Notices SET `text`=REPLACE(REPLACE(`text`,'\\''',''''),'\\"','"');
UPDATE wD_Games SET processStatus='Paused' WHERE processTime IS NULL AND NOT phase = 'Finished';
UPDATE wD_Users SET `type` = 'Guest,System' WHERE username = 'Guest' LIMIT 1 ;

UPDATE wD_Misc SET value=92 WHERE name = 'Version';