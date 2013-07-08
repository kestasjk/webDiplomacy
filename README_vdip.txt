vDiplomacy readme, for webmasters
----------------------------------------
=> Note to webmasters
=> Install

Note to webmasters
-------------------
vDiplomacy is based on the webdiplomacy code with some small changes and I'll try to stick as close as possible to the original codebase.
If you do not need a special vDiplomacy-feature I strongly reccomend running the webdip-code instead of vDiploamcy. All variants _should_
work with the webdiploamcy code as well.


Install
-------
Follow the original webdiplomacy README.txt for a step-by-step installation guide
vDip requires quite a lot of new sql-tables and entries.
You can go the easy-route and uncomment the "$easyDevInstall = 'install_dev.php';" line in the config.php to generate
all needed SQL-tables and a generic Adminuser (without a password!!) automatic, or you can apply all necessary files on your own.
 1. install.sql (This will insall the SQL-tables for webdip 1.00)
 2. udpate.sql (from 1.00->1.03)
 3. all sql-files in vdip-1.03
 4. udpate.sql (from 1.03->1.04)
 5. all sql-files in vdip-1.04
 6. udpate.sql (from 1.04->1.32)
 7. all sql-files in vdip-1.32
and so on.

Oliver Auth - 22/05/13