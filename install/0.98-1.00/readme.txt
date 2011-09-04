This is the 1.00 release, based on our webDiplomacy's version number roughly reflecting the 
year (0.8x=2008, 0.9x=2009, etc), but also reflecting the relative feature completeness. The 
project has many more features than the original aim for 1.00.

Unlike past 0.7x->0.80/0.8x->0.90 changes this 0.9x->1.00 change is a pretty standard bugfix 
release, mainly because, unlike 0.7x and 0.8x, 0.9x has had many significant feature additions.
The difference between 0.90 and 0.97 (Live/Anonymous games, client-side orders, Variants, etc) 
is much larger than 0.70 vs 0.78 and 0.80 vs 0.82.

Although it's date based it's still a milestone, so congrats to all involved for getting us 
here. Hopefully the next five years will be as fun as the first five.

Changelog
---------
- Variant renamed to WDVariant, as Variant is a reserved word on Windows IIS webservers.
- Some bug fixes to the World variant
- Ancient Med variant added
- Preliminary Facebook integration support added
- Added parameter to prevent client-side map caching, which sometimes prevented users seeing the latest orders 

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Unpack the new code, copy config.sample.php to config.php, and enter the correct config details from the old config.php
- Move the old code into a folder which can't be accessed from the web
- Copy the new code where the old code used to be
- Delete variants/*/cache/*
- View and test the updated site
- Turn off maintenance mode