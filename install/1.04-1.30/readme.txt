Changelog
---------
- The ability to localize webDiplomacy by changing only files in the locales/ folder, allowing
	webDiplomacy updates to translated installations without requiring re-translation.
- Open (as in "Open games") has been renamed to Joinable, since Open as a verb (Open this forum post)
	or a noun (This is an open game) couldn't be translated into two different things.
- A new Locale page on the admin control panel, allowing new translations to be uploaded, and 
	a list of any untranslated strings to be viewed.

See locales/readme.txt for information on the new localization layer. This information will be 
relevant only to: 
- Admins of webDiplomacy installations who want to translate webDiplomacy into their own language.
- Variant developers who want their variants to be translatable.
- webDiplomacy developers who want thier modifications to be translatable.

Note that this does not allow users within a certain installation to choose a localization; this
modification is intended to allow an installation to be localized. We are aiming to encourage 
the development of new webDiplomacy communities in new languages, which can still participate in 
updates to webDiplomacy and still contribute code back; not allowing multiple languages to play
on the same server.

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode