Changelog
---------

- Bounty events: live games at fixed times with bonus points (GitHub issues #638 and #871). It has got hard to get
  seven players online at once, so these give players a time to show up, and a pool split into games means turnout
  isn't capped at one game. The code is in lib/bounty.php.

  - A template (wD_BountyTemplates) is a named game configuration with a bonus and optional consolation points.
    Bounty games are free to enter: members bet nothing, and the bonus is the whole pot. Unranked scoring isn't
    offered, as it would pay nobody.
  - An event (wD_BountyEvents) schedules a template at a UTC time, once or weekly, and can be disabled.
  - Each occurrence has a pool (wD_BountyPools, wD_BountyPoolMembers). The gamemaster opens it 5 minutes before the
    start, copying the template's settings into it. Players enter it from the home page, with the same checks as
    joining a game (logged on, not banned or temp banned, the reliability rating). A player can be in one open pool
    at a time, since two pools would start two live games at once.
  - At the start the gamemaster shuffles the pool, makes as many full games as it fills, with countries assigned at
    random, and starts them in the same run. Everyone else is left over: they're paid the consolation points (as a
    'Bounty' points transaction, outside any game) and told so, including when the pool couldn't fill one game.
  - Opening and shuffling happen once per occurrence however many gamemaster runs overlap. Opening is an INSERT
    IGNORE on the pool's unique (eventID, startTime) key; the shuffle runs in one transaction holding the pool row
    FOR UPDATE and only if it is still Open, and entering or leaving locks the same row, so a player entering during
    a shuffle waits for it and then finds the pool closed. A run claims a pool (timeClaimed) before shuffling it, so
    overlapping runs don't each try. A shuffle that fails is rolled back and retried, and given up on (status Failed)
    after 3 attempts; a database error ends the gamemaster run, so attempts are counted when the pool is claimed.
  - The home page shows the upcoming occurrences, with their settings and bonus, under "Upcoming live events". The
    joinable games list (libHome::joinableGames()) starts with them too, but it is still disabled on the home page.
  - Admins manage templates and events, and see past occurrences and what they produced, in the admin CP's new
    Bounty events tab. Moderators can see it but not change it, as a bounty creates points.

  The update adds a default template, a weekly 5-minute full-press Classic game with a 500 point bonus, and a weekly
  event for it at a placeholder time (Saturdays 18:00 UTC), disabled. Set its time and enable it in the admin CP.
