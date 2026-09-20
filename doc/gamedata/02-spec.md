# Game data: static JSON files + `game/playercontext` (step 2 spec)

Draft 3, 2026-09-19. Answers the audit in `01-audit.md`. Everything here is implemented and tested on the dev
stack, not yet deployed; section 10 lists what was built and tested, and what was found on the way. Section 8 maps every audited field to
its new home; section 9 lists the decisions that are still open.

## 1. Principles

1. **Public data is a file; private data is one API call.** Anything every viewer may see goes in a
   static JSON file that the web server serves without PHP or the database. Anything that depends
   on who is asking comes from `game/playercontext`.
2. **A file never contains anything board.php would hide from a logged-out visitor.** The files are
   world-readable by URL, so that is the test. What a moderator, a director or the player
   themself may additionally see comes from `game/playercontext`.
3. **Files are written by one class, whole, atomically, under a per-game lock, after COMMIT.** No
   other code writes them and nothing appends to them.
4. **Correctness never depends on a write hook having fired.** Hooks make updates prompt. A cheap
   fingerprint check in `game/playercontext` (section 5.4) catches anything a hook missed: admin
   SQL edits, background tasks, a crash between COMMIT and the file write, a wiped cache folder.
5. **One schema, one set of types.** IDs and counts are JSON numbers, flags are booleans, times are
   Unix seconds as numbers, "none" is `null`, sets are arrays of strings. Enumerations keep the
   database spelling (`"Diplomacy"`, `"Support hold"`, `"Army"`). Clients adapt to this schema, not
   the other way round.
6. **Reads move; writes stay.** `ajax.php`, `game/orders`, `game/sendmessage`, `game/setvote`,
   `game/messagesseen`, `game/markbackfromleft`, `game/join`, `game/leave`, `sandbox/*` and
   `push/*` are unchanged. Every read endpoint in the audit is replaced.

## 2. The files

| File | Path | Contents | Rewritten when |
|---|---|---|---|
| Variant | `variants/<Name>/cache/variant.json` | map and variant constants | variant install / cache wipe |
| Game | `cache/games/<id/100>/<id>/game.json` | settings, members, current board | processing, join/leave, pause, votes applied, admin edits |
| Status | `.../status.json` | each country's order status and votes | a player saves/readies orders or votes; processing |
| History | `.../history.json` | every phase: units, centres, orders and results | processing, draw/concede, turn moved back |
| Messages | `.../messages.json` | global messages, and the vote log in public-draw-vote games | a global message or a logged vote |

`<id/100>` is `floor(gameID / 100)`, the layout `libCache::dirID('games', $id)` already uses.

`status.json` is separate from `game.json` because it changes on player clicks, many times per
phase, and needs one query to rebuild; `game.json` needs four. In anonymous games with hidden draw
votes it never changes between phases, so nothing is rewritten or published for a Ready click.

Every per-game file starts with the same header:

```json
{ "schema": 1, "gameID": 122573921, "file": "game", "version": "9f2c41d07a3e", "generated": 1789776000 }
```

`version` is new every time the file is rewritten. Clients treat it as opaque and compare it only
for equality. They request files as `<path>?v=<version>`, which makes any HTTP caching safe.

**Sandbox games** get no files at the predictable path, because board.php restricts who may view
them. Their files are named `game-<token>.json` etc., where `<token>` is derived from the game ID
and `Config::$secret` the way `libCache::privateFilename()` does it. Only `game/playercontext`
gives out those URLs, and only to the creator, moderators and holders of the share token.

### 2.1 `variant.json`

Generated from `wD_Territories`, `wD_Borders`, `wD_CoastalBorders` and the variant object, next to
`territories.js`, and lazily if missing.

```json
{
  "schema": 1, "variantID": 1, "mapID": 1, "name": "Classic", "fullName": "Classic",
  "version": "c0ffee12",
  "countries": [ { "countryID": 1, "name": "England" } ],
  "supplyCenterCount": 34, "supplyCenterTarget": 18,
  "territories": [
    { "id": 1, "name": "Clyde", "type": "Coast", "supply": false, "homeCountryID": null,
      "coast": "No", "coastParentID": 1, "smallMapX": 144, "smallMapY": 165,
      "borders":        [ { "id": 2, "army": true, "fleet": true } ],
      "coastalBorders": [ { "id": 2, "army": true, "fleet": true } ] }
  ]
}
```

- `name` is the database name, not passed through `l_t()`, so it is a stable join key.
- `coast` keeps the database enum (`"No"`, `"Parent"`, `"Child"`). `coastParentID` equals `id` for
  territories that aren't child coasts, as in the database.
- `homeCountryID` is `wD_Territories.countryID` (the starting owner), `null` for neutral.
- Loaded by `mapID` from the variant object, which fixes the `variantID`-as-`mapID` bug.
- Variant-specific order rules (BuildAnywhere, Modern2, ClassicChaos, Zeus5 splice extra JS into
  the classic board) are out of scope for now: the React board and all bots only support variants
  1, 15 and 23. The file has a reserved `"rules": {}` object for them.

### 2.2 `game.json`

```json
{
  "schema": 1, "gameID": 122573921, "file": "game", "version": "…", "generated": 1789776000,
  "name": "Example game",
  "variant": { "id": 1, "name": "Classic", "url": "variants/Classic/cache/variant.json", "version": "c0ffee12" },
  "turn": 7, "phase": "Diplomacy", "turnText": "Autumn, 1904",
  "gameOver": "No",
  "processTime": 1789800000, "processStatus": "Not-processing", "pauseTimeRemaining": null,
  "phaseMinutes": 1440, "phaseMinutesRB": -1, "nextPhaseMinutes": 1440, "phaseSwitchPeriod": -1,
  "startTime": 1789000000, "finishTime": null,
  "pot": 35, "potType": "Sum-of-squares", "minimumBet": 5,
  "pressType": "Regular", "anon": true, "drawType": "draw-votes-hidden",
  "missingPlayerPolicy": "Normal", "playerTypes": "Members",
  "excusedMissedTurns": 1, "minimumReliabilityRating": 0,
  "isPrivate": false, "isSandbox": false, "identitiesHidden": true,
  "members": [
    { "countryID": 1, "country": "England", "status": "Playing",
      "supplyCenterNo": 5, "unitNo": 5, "bet": 5, "pointsWon": null,
      "user": null, "missedPhases": null, "excusedMissedTurns": null, "lastSeen": "day" }
  ],
  "units": [ { "id": 9912, "countryID": 1, "type": "Fleet", "terrID": 6, "retreating": false } ],
  "territories": [ { "terrID": 6, "ownerCountryID": 1, "unitID": 9912, "retreatingUnitID": null,
                     "standoff": false, "occupiedFromTerrID": null } ],
  "civilDisorders": null
}
```

Visibility rules, which follow board.php (`audit/board-php.md` section 3):

- `identitiesHidden = (anon == 'Yes' && phase != 'Finished')`.
- While identities are hidden: `user`, `missedPhases` and member `excusedMissedTurns` are `null`,
  `lastSeen` is a bucket (`"day"`, `"week"`, `"older"`) and `civilDisorders` is `null`. Otherwise
  `user` is `{ "userID", "username", "points", "type" }` and `lastSeen` is a Unix time.
- `members` is always ordered by `countryID` (pre-game, by when they joined). board.php orders
  open games by the users' points, which an anonymous game must not do; clients sort as they like.
- `lastSeen` is refreshed only when the file is rewritten for another reason. `timeLoggedIn`
  changes on every page view and must never trigger a rewrite.
- `civilDisorders` (`[{ userID, username, countryID, turn, supplyCenterNo }]`) only once Finished.
- Pre-game: members have `countryID: 0`, and `units` and `territories` are empty.
- Finished: `units` and `territories` are empty (the tables are cleared); the final position is the
  last entry of `history.json`, a phase named `"Finished"`.
- `isPrivate` is a boolean. The invite code hash is never selected by the generator.
- `turnText` is `$Variant->turnAsDate($turn)`, so clients need no per-variant date logic.
- `retreating` and `retreatingUnitID` are explicit, instead of being inferred by the client.

### 2.3 `status.json`

```json
{
  "schema": 1, "gameID": 122573921, "file": "status", "version": "…", "generated": 1789776000,
  "turn": 7, "phase": "Diplomacy",
  "members": [
    { "countryID": 1, "orderStatus": ["Saved", "Completed", "Ready"], "votes": ["Pause"] },
    { "countryID": 2, "orderStatus": null, "votes": [] }
  ]
}
```

- `orderStatus`: when identities are hidden it is `["None"]` for a country with nothing to order
  this phase and `null` (hidden) otherwise, which is exactly what board.php's lock icon shows.
  When identities aren't hidden it is the real set, `[]` meaning nothing saved yet.
- `votes`: `Pause`, `Cancel` and `Concede` votes are always listed. `Draw` is listed only when
  `drawType == 'draw-votes-public'`. Votes are listed only for `Playing` members of unfinished games.
- A player's own full status and votes come from `game/playercontext`.

### 2.4 `history.json`

```json
{
  "schema": 1, "gameID": 122573921, "file": "history", "version": "…", "generated": 1789776000,
  "phases": [
    { "turn": 0, "phase": "Diplomacy", "turnText": "Spring, 1901",
      "units":   [ { "countryID": 1, "type": "Fleet", "terrID": 6, "retreating": false } ],
      "centers": [ { "terrID": 6, "countryID": 1 } ],
      "orders":  [ { "countryID": 1, "terrID": 6, "unitType": "Fleet", "type": "Move",
                     "toTerrID": 17, "fromTerrID": null, "viaConvoy": false,
                     "success": true, "dislodged": false } ] }
  ]
}
```

- Every phase, oldest first, as `game/status` gives them. While the game is running the last entry is
  the phase being played, with its units, its supply centres (from the archive, as `game/status` gives
  them) and no orders. Its units and centres don't change until the game is processed, so including
  it doesn't make the file change more often, and clients that replay a game (the bots) get every
  phase from one source. A finished game ends with an extra `"Finished"` entry holding the final
  units, as `game/status` builds today.
- A client checks that the last entry is for the turn and phase `game.json` and `game/playercontext`
  say the game is in. The files are rewritten one after another just after a process, so for a
  moment they can disagree; asking `game/playercontext` again rewrites any that are stale.
- Built by the code that builds `game/status` `phases[]` today (archive rows, unit
  reconstruction for Retreats and Builds, removal of the duplicated final phase of drawn games),
  moved into a shared class so the two can't drift while `game/status` still exists. The bots
  re-adjudicate the game from this, so the semantics don't change; only the types are cleaned.
- Rewritten whole at each process. That is the cost of one `game/status` call today, paid once per
  phase instead of once per reader.

### 2.5 `messages.json`

```json
{
  "schema": 1, "gameID": 122573921, "file": "messages", "version": "…", "generated": 1789776000,
  "messages": [
    { "id": 88120431, "turn": 7, "phase": "Diplomacy", "timeSent": 1789770000,
      "fromCountryID": 3, "toCountryID": 0, "message": "Hello all" }
  ],
  "voteLog": [
    { "id": 88120440, "turn": 7, "phase": "Diplomacy", "timeSent": 1789770100,
      "countryID": 3, "vote": "Draw", "on": true }
  ]
}
```

- `messages`: every `toCountryID = 0` row, which any visitor can already read through the
  messages archive. `fromCountryID: 0` is the GameMaster or a moderator. `message` is the stored,
  HTML-escaped text; clients must keep sanitising it.
- `voteLog`: only when `drawType == 'draw-votes-public'`, parsed from the "Voted for X" notes as
  `game/status` `publicVotesHistory` does today. Otherwise `[]`.
- Known limit: the file is rewritten whole for each global message, and every connected client
  refetches it. That is fine for the tens to hundreds of global messages a game normally has. If
  public-press games with thousands of messages make it a problem, the remedy is to keep only the
  current turn in `messages.json` and freeze earlier turns into `messages-<turn>.json`.

## 3. `game/playercontext`

`GET api.php?route=game/playercontext`

| Parameter | Meaning |
|---|---|
| `gameID` | Optional. With it: one context. Without it: a list of contexts for every active game of the caller, which replaces `players/pulse`, `players/active_games` and `players/missing_orders`. |
| `countryID` | Optional; only meaningful in sandbox games, where one user holds every country. Ignored unless the caller controls that country. |
| `orders=1` | Include the caller's current orders and the order save token. Single-game form only. |
| `messages=1` | Include private messages. Single-game form only. |
| `messagesSince=<unix>` | Only messages with `timeSent >= messagesSince`. |
| `messageTurns=<n>` | Only messages from the last `n` turns (`turn >= currentTurn - n`). Combines with `messagesSince`. |
| `multiplexOffset` | As for `players/pulse` today, for multiplexed API keys in the list form. |

Authentication is the existing session or API key. No permission flag is needed: non-members get
the spectator form, which contains nothing that isn't public. Sandbox games return 403 unless the
caller is the creator or a moderator, or supplies `sbToken`.

### 3.1 Single-game response

```json
{
  "schema": 1,
  "game": { "gameID": 1325739211, "realGameID": 122573921, "variantID": 1, "name": "Example game",
            "turn": 7, "phase": "Diplomacy", "gameOver": "No",
            "processTime": 1789800000, "processStatus": "Not-processing",
            "pressType": "Regular", "potType": "Sum-of-squares", "phaseMinutes": 1440 },
  "files": {
    "variant":  { "url": "variants/Classic/cache/variant.json", "version": "c0ffee12" },
    "game":     { "url": "cache/games/1225739/122573921/game.json", "version": "…" },
    "status":   { "url": "…/status.json", "version": "…" },
    "history":  { "url": "…/history.json", "version": "…" },
    "messages": { "url": "…/messages.json", "version": "…" }
  },
  "viewer": { "userID": 1234, "isMember": true, "isModerator": false, "isDirector": false,
              "isSandboxOwner": false },
  "member": {
    "countryID": 7, "memberID": 5550123, "status": "Playing",
    "orderStatus": ["Saved"], "votes": ["Draw"],
    "missedPhases": 0, "excusedMissedTurns": 1, "bet": 5,
    "newMessagesFrom": [3, 0], "mutedCountryIDs": [], "isTempBanned": false,
    "ordersPending": true,
    "lastMessageTime": 1789770000
  },
  "sseAuth": "5f4dcc3b…_1789776000",
  "orders": {
    "context": "{\"gameID\":122573921,…}", "contextKey": "…",
    "orders": [ { "id": 771, "countryID": 7, "unitID": 9950, "unitType": "Army", "terrID": 44,
                  "type": "Hold", "toTerrID": null, "fromTerrID": null, "viaConvoy": false } ]
  },
  "messages": {
    "messages": [ { "id": 88120400, "turn": 7, "phase": "Diplomacy", "timeSent": 1789769000,
                    "fromCountryID": 3, "toCountryID": 7, "message": "…" } ],
    "cursor": 1789775990
  },
  "hiddenMembers": null
}
```

- `game` is read from `wD_Games` in the same request and is authoritative. A client that finds a
  file disagreeing with it refetches the file with the `version` given here.
- `game.gameID` is the ID the caller should keep using (multiplexed for multiplexed keys);
  `realGameID` and `files.*.url` mean a bot never has to decode it.
- `member` is `null` for non-members, and for temp-banned members who have left. `ordersPending`
  is board.php's "at least one country still needs to enter orders", which is shown to members
  even in anonymous games but is not public.
- `sseAuth`: for members, the existing per-country token. For other logged-in viewers, a token for
  the game's public channel only (section 4), which fixes spectators never connecting.
- `orders`: only with `orders=1`, only for members, only in Diplomacy, Retreats and Builds.
  `context` is the exact signed string, to be posted back to `ajax.php` byte for byte; clients
  must not parse and re-serialise it. The token is minted only after the membership check. In
  sandbox games `orders` holds every country's orders.
- `messages`: only with `messages=1`. Rows where the caller's country is sender or recipient
  and `toCountryID != 0`: private press, notes to self, the caller's own vote log, and GameMaster
  messages addressed to the country. Global messages are in `messages.json`. Membership is checked
  **before** the Redis last-message-time short-circuit. Clients pass `cursor` as the next
  `messagesSince` and dedupe by `id`; `cursor` trails the clock by a few seconds so a message
  committed late is not skipped.
- API-key callers read `wD_GameMessages_Redacted` unless
  `Config::$allowBotsAccessToUnredactedMessages`, as `game/status` does today (open question 9.3).
- `hiddenMembers`: for a moderator who is not in the game, or a director, in a game whose
  identities are hidden: `[{ countryID, userID, username, points, type, orderStatus, votes }]`.
  `null` for everyone else. This keeps privileged views out of the public files.

### 3.2 List response (no `gameID`)

```json
{ "schema": 1, "games": [
  { "gameID": 1325739211, "realGameID": 122573921, "countryID": 7, "variantID": 1,
    "name": "Example game", "turn": 7, "phase": "Diplomacy", "gameOver": "No",
    "processTime": 1789800000, "processStatus": "Not-processing", "pressType": "Regular",
    "status": "Playing", "orderStatus": ["Saved"], "votes": [], "newMessagesFrom": [3],
    "lastMessageTime": 1789770000,
    "files": { "variant": { "url": "…", "version": "…" }, "game": { "url": "…", "version": "…" }, "…": {} } } ] }
```

One joined query over `wD_Members` and `wD_Games`, plus two Redis reads per game for the file
versions and last message time: the cost of `players/pulse` today. `files` has the same form as in the
single-game response; a version is null for a file never written, which a single-game request writes.
`multiplexOffset` selects one account of a multiplexed key, as it did for `players/pulse`. A bot compares the `files`
versions with the ones it last loaded and fetches only the files that changed. "Needs orders" is
`orderStatus` containing none of `Saved`, `Ready`, `None`.

### 3.3 Cost of the single-game form

| Step | Queries |
|---|---|
| Authentication | existing (session: none extra; API key: 2 reads, 1 write) |
| Game row | 1, by primary key, public columns plus creator and director IDs |
| Member rows | 1, by `gameID`; serves the membership check, `member`, `ordersPending` and the fingerprints |
| Tournament directors | 1, by `gameID` |
| File versions | 2 Redis reads |
| `orders=1` | 1 (`wD_Orders` joined to `wD_Units`) |
| `messages=1` | 1 Redis read, and 1 query only if something is new |
| mutes | 1, members only; temp ban 1, only for members who have left |

No `Game`, `Members` or `Variant` object is built. That is the main saving over today's endpoints,
which build all three for every request. Measured by the API's own counters on the dev stack, with
an API key: 7.3 database reads per call with orders and messages, against 8.9 (`game/overview`) +
12.4 (`game/data`) + 17.4 (`game/status`) + 7.3 (`game/getmessages`) for the calls it replaces. The
dev database is too small for the timings to mean anything.

## 4. Change notification

Redis publish, forwarded by the SSE server, on the game's files channel `private-game<id>-files`
(a channel of its own, as the older boards take any event on the overview channel that mentions
"message" for a new message):

```json
{ "event": "files", "data": { "game": "…", "status": "…", "history": "…", "messages": "…" } }
```

`data` lists only the files whose version changed. Clients refetch those with `?v=`. The existing
`processed`, `set-vote` and `messageSent` events keep being published until the old clients are
gone. Private messages keep using the per-country channel's `messageSent`, which tells the client
to call `game/playercontext?messages=1&messagesSince=…`.

The versions are also kept in the Redis key `gamefiles_<gameID>` (JSON), which gives:

- **Catch-up on connect.** The client connects with `have=game:<v>,status:<v>,…`; the SSE server
  compares that with the hash and immediately sends a `files` event for the differences, then the
  existing `catchup` marker. This generalises the turn/phase catch-up added in `f2703b85`.
- **Bots**, which don't use SSE, get the same versions in the list response.

SSE server changes: accept a spectator token for `private-game<id>-country0`, and subscribe a
connection only to channels of the game its token was issued for (its country channel, the overview
channel and the files channel; closes the over-subscription finding).

## 5. Generating the files

### 5.1 One writer

`lib/gamefiles.php`, class `libGameFiles`:

- `refresh($gameID, array $files)` takes the named lock `gamefiles_<gameID>` (`GET_LOCK`, as
  `map.php` does for map generation), reads with plain SQL (no `Game` object), builds the JSON,
  writes a temp file and `rename()`s it over the target, updates the Redis key, publishes one
  `files` event for the versions that changed, and releases the lock. It never throws: a file
  that can't be written stays stale until the next refresh, and the request that changed the game
  carries on.
- `refreshAfterCommit($gameID, $files)` is for pages that commit when they end (join, leave, admin
  actions, board.php's "back from left"): `close()` in `header.php` runs the queued refreshes
  after its COMMIT, and not at all if the page failed.
- Reading under the lock, after the caller's COMMIT, is what makes concurrent order saves safe:
  whichever writer goes last has seen every earlier commit.
- If a file's fingerprint (5.4) equals the stored one the file is not rewritten and nothing is
  published.
- `Game::wipeCache()` leaves `*.json` alone, so there are always files for clients to load.
- It must not run inside an open transaction that could still roll back.

### 5.2 Hooks (promptness)

| Event | Where | Files |
|---|---|---|
| Game processed | `gamemaster.php`, after COMMIT and **after** `Game::wipeCache()` (which deletes `*.*` in the folder), before the `processed` publish | game, status, history |
| Votes applied: pause/unpause, draw, concede | `libGameMaster::findAndApplyGameVotes()` after each commit | game, status, history |
| Cancelled / abandoned / erased | `eraseGame()` | delete all |
| Turn moved back | `moveTurnBack()` after COMMIT | game, status, history, messages |
| Order status changed | `OrderInterface::writeOrderStatus()` callers, after their COMMIT (`ajax.php`, `game/orders`) | status |
| Vote toggled | `userMember::toggleVote()`, `game/setvote`, `game/togglevote` | status, messages if the vote log is public |
| Global message sent | `libGameMessage::send()` when `toCountryID == 0` | messages |
| Join, leave, takeover, back from left | `processMembers::join()`, `processMember::leave()`, `markBackFromLeft()` | game, status |
| Admin / director / TD actions with a game ID | one call at the end of the admin action dispatcher | game, status |

### 5.3 Fallback for missing files

`gamefile.php?gameID=<id>&file=<name>` is a small public script in the style of `map.php`: it
checks the game exists and is not a sandbox game (or that the token matches), calls
`libGameFiles::refresh()` if the file is missing or stale, and redirects to the static URL. Clients
call it when a static URL returns 404. Old finished games get their files this way on first view.

### 5.4 Fingerprints

Next to each file's version, Redis keeps a fingerprint of the database values the file was built
from. It is never given to clients.

| File | Fingerprint is a hash of |
|---|---|
| game | the public `wD_Games` columns, plus each member's `countryID, userID, status, supplyCenterNo, unitNo, bet, pointsWon, missedPhases, excusedMissedTurns` |
| status | `turn`, `phase`, and each member's `orderStatus` and `votes` after the visibility rules |
| history | `turn`, `phase`, `gameOver` |
| messages | the id of the newest global message or vote log row (Redis `gamefilesmsg_<gameID>`, set by `libGameMessage::send()`, reseeded from the database if missing), `drawType`, `pressType`. An id rather than a time, because two messages can share a second. |

All of these come from the two queries `game/playercontext` already runs, plus one Redis read. So
on every call it recomputes the fingerprints, compares them with the stored ones, and calls
`refresh()` for any file that differs, has nothing stored, or is missing on disk. (Tested: after a
direct SQL `UPDATE wD_Games SET phaseMinutes`, a spectator's call rewrote `game.json`.) A missed
hook therefore costs a delay until the next player or bot looks at the game, never a wrong answer.

## 6. Security checklist

| Risk | Rule |
|---|---|
| Identity leak in anonymous games | Generator applies `identitiesHidden`; member order by `countryID`; `lastSeen` bucketed; no `userID`, points or user type; no civil disorder list. Tested by generating an anonymous game's files and grepping for every member's username and user ID. |
| Hidden draw votes | `Draw` never in `status.json` or `voteLog` unless `draw-votes-public`. |
| Readiness in anonymous games | Only `["None"]` or `null`. |
| Private messages | Never in a file. `game/playercontext` checks membership before any Redis or DB message read, and selects by the member row's own `countryID`, never by a request parameter. |
| Invite code | Column not selected. |
| Sandbox games | No predictable file names; URLs only from `game/playercontext` after the board.php access rule. |
| Order save token | Minted only for the member, after the membership check. |
| Current-phase orders | Only in `game/playercontext` for their owner. `history.json` holds adjudicated phases only. |
| Moderator / director views | `hiddenMembers` in `game/playercontext` only. |
| SSE | Token scoped to one game; spectator token gives the public channel only. |
| SQL | Every parameter `intval()`'d on read; no raw request value reaches SQL or a Redis key. |
| Partial reads | Temp file + `rename()`. |
| Wholesale serialisation | The generator builds arrays field by field from named columns. It never `json_encode`s a PHP object. |
| Message HTML | Stored escaped; clients keep sanitising (the React board already restricts to `br`/`strong`). |

## 7. Startup cost, before and after

| | Today (beta member) | New board |
|---|---|---|
| PHP + DB requests at startup | 5 (`overview`, `data`, `status`, `active_games`, `getmessages`), each building a `Game` | 1 (`game/playercontext?orders=1&messages=1`) |
| Static fetches | 0 | up to 5, cacheable by version |
| On `processed` | 4 API calls per client | 3 static files + 1 `playercontext` (new orders and token) |
| On another player's vote or Ready | 1 API call per client (or nothing, and stale) | 1 static file |
| Gunboat bot, 7 countries, one phase | 7 full `game/status` | 3 static files, fetched once if the bots share a cache |
| Cicero, per message sent | 3 to 4 full `game/status` | 0 to 1 static file + 1 `playercontext` |

## 8. Where every audited field goes

V = `variant.json`, G = `game.json`, S = `status.json`, H = `history.json`, M = `messages.json`,
P = `game/playercontext`, X = dropped (no consumer reads it).

| Audited data | New home |
|---|---|
| `gameID`, `turn`, `phase`, `gameOver`, `processTime`, `processStatus` | G, and P `game` (authoritative) |
| `name`, `pot`, `potType`, `minimumBet`, `pressType`, `anon`, `drawType`, `playerTypes`, `missingPlayerPolicy`, `minimumReliabilityRating`, game `excusedMissedTurns`, `startTime`, `pauseTimeRemaining`, `phaseMinutes`, `phaseMinutesRB`, `nextPhaseMinutes`, `phaseSwitchPeriod` | G |
| `phaseLengthInMinutes` | G `phaseMinutes`; P `game.phaseMinutes` |
| `season`, `year`, `Variant->turnAsDate()` | G and H `turnText` |
| `alternatives` | X; clients build the text from the G settings. Sandbox detection uses G `isSandbox`. |
| `variant` object in overview | X. `variantID`, names, countries, SC counts in V. |
| `variantID` | G `variant.id`, P |
| private flag | G `isPrivate` |
| director IDs | P `viewer.isDirector` |
| civil disorder history | G `civilDisorders`, finished games only |
| member `countryID`, `country`, `status`, `supplyCenterNo`, `unitNo`, `bet`, `pointsWon` | G `members` |
| member `userID`, `username`, points, user type | G `members[].user` when not hidden; P `hiddenMembers` for privileged viewers |
| member `missedPhases`, `excusedMissedTurns`, `timeLoggedIn` | G (`null` / bucketed when hidden); own values in P `member` |
| member `online` | X |
| others' `orderStatus`, `orderStatuses` | S |
| others' `votes`, `publicVotes` | S |
| `publicVotesHistory` | M `voteLog` |
| own `orderStatus`, `votes`, `status` | P `member` |
| aggregate "orders still needed" | P `member.ordersPending` |
| current units, territory status, `standoffs`, `occupiedFrom`, retreating | G `units`, `territories` |
| `phases[]` units, centres, orders, `success`, `dislodged` | H |
| static territories, borders, coasts, map coordinates | V |
| `isSandboxMode` | G `isSandbox`; P `viewer.isSandboxOwner` |
| own current orders, `context`, `contextKey` | P `orders` |
| private messages, notes, own vote log | P `messages` |
| global messages | M |
| `newMessagesFrom`, muted countries, `isTempBanned` | P `member` |
| SSE token | P `sseAuth` |
| user's game list (`players/active_games`, `players/pulse`, `players/missing_orders`) | P list form |
| `lastMessageTimeSent`, `lastVoteTime` | P `member.lastMessageTime`, and the `status` / `messages` file versions |
| `players/cd` | not replaced (open question 9.2) |
| envelope `msg`, `referenceCode`, `success` | X; errors are HTTP status codes with a plain-text reason, as the API's error handler already does |

## 9. Open questions

1. **Production web server.** The files need `Content-Type: application/json` and gzip for
   `.json` under `cache/` and `variants/*/cache/`. Is production Apache or nginx, and is there
   anything in front of it (CDN, proxy cache) that would cache `cache/**` without the `?v=`?
2. **`players/cd`.** The route was removed on 2026-09-20 and the new gunboat client ignores the CD
   keys; the takeover it was for had been rejected by `game/status` since 2022. With the public files
   it could work again (the state no longer needs membership, and `game/orders` still accepts orders
   for a country in CD from a key with `submitOrdersForUserInCD`), if something lists the countries
   in CD: wanted?
3. **Redacted messages.** `config.sample.php` says a separate process fills
   `wD_GameMessages_Redacted` in production, so API-key callers keep reading that table unless
   `allowBotsAccessToUnredactedMessages` is set. Logged-in users read the real one. Confirm this
   is still how production runs.
4. **Moderators in the new board.** `hiddenMembers` is specified but could be left out of the first
   version, leaving moderators on board.php.
5. **Guests.** Files are world-readable, so logged-out visitors could get a read-only board with
   no API call and no live updates. Wanted?

## 10. Status, 2026-09-19

Built and tested on the dev stack; nothing committed or deployed yet.

| Step | What | Tested by |
|---|---|---|
| 3 | `lib/gamefiles.php` (the files), hooks listed in 5.2, `gamefile.php` (5.3), SSE `files` event and catch-up (4) | `scratchpad/test_playercontext.py` (37 contract and privacy checks: anonymity, hidden draw votes, readiness, private messages, spoofed `countryID`, SQL injection attempts, sessions, order token accepted by `ajax.php` and tampering rejected), sandbox checks, a standalone SSE server test (catch-up, channels outside the token's game refused, spectator tokens, live events), a missed hook put right by a spectator's request |
| 4 | `game/playercontext` (`api/responses/player_context.php`) | the same, plus the API's own counters: 7.3 database reads a call against 46 for the four calls it replaces |
| 5 | `game-src/`, built to `/game/`: the beta board reading only the files and `game/playercontext` (`src/utils/api/gameSource.ts` adapts them to the shapes the board's state already used) | headless Chrome: load as a player, as a spectator of an anonymous game (no names in the page) and of a finished game; Ready and Unready through `ajax.php`; a message sent from the press panel reaching another player's board live; another player's vote and a processed turn reaching a board over SSE, fetching only the changed files; no old read route in the built bundle |
| 6 | Gunboat bots (`webdiplomacy_bots_mila`, `webdiplomacy_net/api.py`) | the bots played two dev games through the new client (one to a win in 1913, one past 1908 with 202 messages and 15 draw votes sent around them) without errors; `docker/equivalence_test.py` built the engine game from `game/status` and from the files for every country at 279 sampled points (movement, retreat and build phases), all identical |
| 7 | `~/Desktop2/webdiplomacy_bots_meta`: a clone of the Cicero/Dora bots using only the new routes for reading (`dev/README.md`) | `dev/equivalence_test.py`: the pydipcc game from `game/status` and from the new sources identical apart from pydipcc's random game id, messages and draw votes included, in 93 comparisons: 7 countries at 13 points of a full-press game (up to 94 messages each), a multiplexed account, and FairBot in France vs Austria. Not yet run with the models, which need the GPU the live bots are using |

Found on the way:

- `game/setvote` logged "Un-Voted for X" when a vote was turned on, and the reverse (fixed).
- `game/setvote` and `game/togglevote` didn't check the country belonged to the caller: the vote wasn't
  changed, but the "Voted for X" log was written as that country, and in public-draw-vote games that log
  is what Cicero reads as other countries' draw votes and what `messages.json` publishes. Fixed: 403.
- Files events are published on `private-game<id>-files`, not the overview channel: the older boards
  treat any overview event mentioning "message" as a new message.
- `game/playercontext` seeds a missing `lastmsgtime_<game>_<country>` key as `game/pulse` did, so the SSE
  server's catch-up doesn't fall back to a resync for games with no messages yet.
- `libGameFiles::refresh()` turns PHP warnings into exceptions while it runs (the site's error handler
  would otherwise end the order save, vote or gamemaster run it follows), and `Config::$gameFilesDisabled`
  turns it off without a redeploy.
- For stg.webdiplomacy.net, the staging site on the `master` branch, set up 2026-09-19. It has its own
  copy of the production database, cache folder, redis and SSE server, so it shares nothing with the live
  site and `Config::$gamemasterDisabled` (added for a site that *would* share a database) stays false
  there. What the staging work left behind is still useful anywhere the code is newer than the gamemaster
  writing the files: the messages fingerprint includes `lastmsgtime_<game>_0` and, in public-draw-vote
  games, the newest `votesChanged`, which every version of the code keeps current, so changes made
  through older code still get `messages.json` rewritten; and the `/game/` board acts on the older
  `processed` and `set-vote` events as well as files events. Because the staging database is a copy of
  production's, it holds real addresses and push subscriptions: turn off mail and push there before
  letting its gamemaster run, or `gamemaster/backgroundTasks.php` will email real players about staging
  games.
- The gamemaster can miss that a game's orders are all ready, so the game waits for its deadline:
  `gamemaster.php` reads the `processHint` key and then deletes it, losing any hint added in between,
  and `findGameOrdersReady()` only looks at `orderStatusChanged` from the start of its previous run, so
  a Ready committed just after a run began is never seen. Seen on the dev stack; not fixed.
- `game/sendmessage` can deadlock on the `newMessagesFrom` update while other requests write the same
  member rows (seen with a message every 1.5 s during processing). Not fixed.
- In the live Cicero bot the ready flag compared the deadline from `game/status`, a string, as a number
  (fixed in `webdiplomacy_bots` `ec2d047b`).

### 2026-09-20

Committed locally (not pushed): the docs, `lib/gamefiles.php` and the endpoint, the `/game/` board, the
`gitpull.php` build step, and the dev docker work below.

Bringing the dev stack up from a bare `git clone` found one thing wrong with the work above: `.gitignore`
ignored `game/`, which matches any folder of that name, so `game-src/src/state/game/` was never committed
and the new board didn't build from a clean checkout. Both `beta/` and `game/` are now anchored to the
root, and the two files are in.

The rest of that session went into the dev stack itself, which couldn't come up from a clean checkout at
all: `install/gamemaster-entrypoint.sh` now runs `composer install`, generates an SSE secret into
config.php and `sse-server/.env` (so live updates work in dev without hand-editing two files), installs
the database again whenever it finds it empty, and clears the variant caches after the install as well as
before; the SSE server installs its own dependencies; `--profile build` builds both boards. The published
ports are bound to localhost, phpmyadmin can only reach the dev database, and nginx no longer serves dot
files. Verified from a clone: READY, all of `test_playercontext.py`, both boards built, the `/game/` board
loading a game from its files, and the gunboat bots playing a dev game through the new route with
`equivalence_test.py` clean.

Later the same day, at the user's direction:

- **The legacy read API is gone.** `game/status`, `game/pulse`, `game/overview`, `game/data`,
  `game/members`, `game/getmessages`, `players/pulse`, `players/active_games`, `players/cd` and
  `players/missing_orders` were removed from `api.php`, with the response classes only they used.
  `api/responses/game_state.php` stays, because `libGameFiles::buildHistory()` builds `history.json`
  from its `GameState`. What remains is `game/playercontext`, the writes (`game/orders`,
  `game/sendmessage`, `game/setvote`, `game/togglevote`, `game/messagesseen`, `game/markbackfromleft`,
  `game/join`, `game/leave`), `sse/authentication`, `push/*` and `sandbox/*`. `api/README.md`, which
  documented `players/cd`, `players/missing_orders`, `game/status` and `game/orders` for outside bot
  authors, is rewritten around the files, `game/playercontext` and the writes, and lists what replaced
  each removed route.
- **`beta-src/` is deleted and `/beta/` redirects to `/game/`** (302, in `.htaccess` and the dev nginx
  config, which catches the old build too if it is still on the server). Every link the site generated
  to `beta/` now points at `game/`: `board.php`'s point-and-click redirect, the join redirect, the game
  panel's map link, the play-now game list and the apple-touch-icon and manifest icons.
- **`gitpull.php` runs the SSE server.** It starts `sse-server/server.js` with node if it isn't
  running, and restarts it when `sse-server/` changed, keeping the pid in `../sse-server.pid` and the
  output in `../sse-server.log`. It reports the port it came up on, or the tail of the log if it
  didn't. This closes the "needs a manual SSE restart on deploy" item.

**The live Cicero and Dora bots read `game/status`, `game/pulse`, `players/pulse`,
`players/active_games` and `game/getmessages`, so they stop working when this is deployed** unless they
are switched to the `webdiplomacy_bots_meta` clone at the same time. The two equivalence tests also need
`game/status` and can only be run against a server on older code from now on.

Later still, after the first staging deploy of all of the above:

- **`Config::$apiConfig` is gone**, replaced by `Config::$botVariantIDs` and read through
  `libVariant::botVariantIDs()`, which falls back to the old `$apiConfig['variantIDs']` so an install
  isn't broken by the deploy. It was written when the API was only for bots, and each of its keys had
  become a hazard now that the site's own board is an API client: `enabled` 404'd every route,
  including `game/playercontext` and the writes, so flipping it took the board down rather than the
  bots; `restrictToGameIDs` gated three write routes and both halves of `game/playercontext` against a
  list that would now have to name every game on the site; `noPressOnly` was read by nothing.
- **`game/playercontext` no longer hides a player's non-Classic games.** Its list form filtered on the
  bot variant list, inherited from `players/active_games`, so a player in a Modern or World game was
  never told about it while `game/playercontext?gameID=` for the same game answered normally. The
  filter now applies only to API-key callers, which is what it was for.
- **Bots are kept to their variants at the point they join**, not by what they're shown: `game/join`
  refuses a variant outside `libVariant::botVariantIDs()` for API-key callers. The old arrangement
  relied on a bot never being *listed* a game it couldn't play.
- **The classic board's missed-update fallback was calling the deleted `game/pulse`** (`api.js:118`,
  which `doc/gamedata/audit/board-php.md` row 5 had documented). It reads `game/playercontext` now:
  `game.turn`, `game.phase` and `member.lastMessageTime` in place of `data.turn`, `data.phase` and
  `data.lastMessageTimeSent`. The commented-out `monitorForUpdate` poller, which called the deleted
  `game/getLastUpdateTime`, is deleted with it. JSVERSION 1.92 -> 1.93.
- **`game/playercontext` re-seeds `gameTurnPhase_<id>` as `game/pulse` did.** Both of the keys the SSE
  server compares on reconnect expire after 30 days, and `game/pulse` set this one again when it was
  missing (`Game::cacheTurnPhase(..., true)`); nothing did after the route was deleted, so a game whose
  key had gone would have had every client asked to resync, for ever. The single-game form of
  `game/playercontext` now sets it, next to where it already re-seeds `lastmsgtime_<game>_<country>`.
- **A deploy that changes `gitpull.php` runs the old copy of it**, because PHP has the file loaded
  before `git pull` replaces it: the first staging deploy tried to build `beta-src/`, which no longer
  has a `package.json`, and never ran the SSE step at all. A second run (`php gitpull.php FORCEALL`)
  does the new steps. `deploySSE()` now also stops if `npm ci` fails instead of killing a working
  server and starting one that can't load its dependencies, which is what happened on staging when
  `sse-server/node_modules` turned out to be owned by another user.
