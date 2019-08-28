UPDATE `wD_Misc` SET `value` = '160' WHERE `name` = 'Version';

ALTER TABLE `wD_Members` MODIFY `votes` set('Draw','Pause','Cancel','Concede');
