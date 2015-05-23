ALTER table wD_UnitDestroyIndex MODIFY destroyIndex smallint;

UPDATE `wD_Misc` SET `value` = '140' WHERE `name` = 'Version';
