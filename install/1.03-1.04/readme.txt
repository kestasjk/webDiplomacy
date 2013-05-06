Changelog
---------
- A new category of moderator; forum moderator
- The ability to silence a thread or user
- After a set number of days silences for users will expire
- Forum moderators can see and manage a user's silences from within their profile page
- Forum moderators can see and manage thread silences from within the forum
- A new section in the rulebook on forum moderation; self-moderation (mutes) and site-moderation (silences)

Updating
--------
- Take a backup (database and files) <-- Important!
- Set the server to maintenance mode (perhaps set an appropriate message warning users of the update in config.php first)
- Wait a minute for all active processes to finish
- Run update.sql
- Copy the new code over the old code
- View and test the updated site
- Turn off maintenance mode