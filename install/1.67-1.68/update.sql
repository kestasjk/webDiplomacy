UPDATE `wD_Misc` SET `value` = '168' WHERE `name` = 'Version';

CREATE TABLE `wD_PaypalIPN` ( 
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
 `userId` int(10) unsigned NOT NULL, 
 `email` varchar(250) NOT NULL, 
 `value` float NOT NULL, 
 `currency` varchar(10) NOT NULL, 
 `status` varchar(100) NOT NULL, 
 `receivedTime` bigint(20) unsigned NOT NULL, 
 PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;