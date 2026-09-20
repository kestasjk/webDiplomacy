# Game data: who reads what today (step 1 audit)

Audited 2026-09-19 at webdiplomacy `d0513c40`, webdiplomacy_bots `08781ed5`,
webdiplomacy_bots_mila `9473c017`. Everything here was read from code; nothing was
measured on production. The four field-level reports this summarises are in `audit/`:

| Consumer | Report |
|---|---|
| React board, `beta-src/` | `audit/beta-board.md` |
| Classic board, `board.php` | `audit/board-php.md` |
| Cicero / Dora bots, `webdiplomacy_bots` | `audit/cicero-dora-bots.md` |
| Gunboat (DipNet) bots, `webdiplomacy_bots_mila` | `audit/gunboat-bots.md` |

The spec that answers this audit is `02-spec.md`.

## 1. Which consumer calls which read endpoint

| Endpoint | beta board | board.php | Cicero / Dora | gunboat bots |
|---|---|---|---|---|
| `game/overview` | startup, every `processed` / `set-vote` event | - | - | - |
| `game/data` | startup, every phase change | - | - | - |
| `game/status` | startup, every phase change | - | whenever the pulse row changes, plus 3 to 4 times per message sent | once per (game, country) needing orders |
| `game/getmessages` | startup (`sinceTime=0`), every `message` event | - | disabled by the live flags | - |
| `game/pulse` | - | fallback only, when the SSE server can't say what was missed | only before a draw vote | - |
| `game/members` | - (only through `game/overview`) | - | - | - |
| `players/pulse` | - | - | every sweep, 7 times per sweep for the multiplexed key | - |
| `players/active_games` | every phase change (for the "Games" tab) | - | only with a `game_name` filter, not configured | - |
| `players/missing_orders` | - | - | never | every 5 s per user key |
| `players/cd` | - | - | never | every 5 s per CD key; takeover has been rejected server-side since 2022 |
| `sse/authentication` | only if the page token is missing, expired or refused | same | - | - |
| inline PHP / page | `?gameID=` only | everything (game, members, chat, votes, order context) | - | - |
| `variants/<V>/cache/territories.js` | - | yes | - | - |
| `cache/games/../<turn>-json.map` | - | yes (units + territory status for the order form) | - | - |
| tables baked into the client | SVG map, centre positions, land/sea type, country colours; joined to server data by territory **name** | - | territory and country ID tables (two identical copies) | territory and country ID tables for variants 1, 15, 23 |

Writes, which all stay as they are: `ajax.php` (both boards' order saving, authenticated only by
the signed `context` + `contextKey`), `game/orders` (bots), `game/sendmessage`, `game/setvote`,
`game/togglevote` (Cicero), `game/messagesseen`, `game/markbackfromleft`, `game/join`,
`game/leave`, `sandbox/*`, `push/*`, `message.php` and the vote form post (board.php).

## 2. What the read endpoints send, and who reads it

"-" means no consumer reads the field.

### 2.1 Game-level

| Data | Sent by | beta | board.php | Cicero/Dora | gunboat |
|---|---|---|---|---|---|
| `gameID`, `turn`, `phase` | overview, data, status, pulse, players/* | yes | yes | yes | yes (from `phases[-1]`) |
| `variantID` | overview, status, pulse, players/* | - (Classic hard-coded) | yes | yes | yes (picks ID tables and model) |
| `name` | overview, active_games | yes | yes | only for the unused name filter | - |
| `processTime` | overview, status (as a string), pulse | yes | yes | yes | - |
| `processStatus`, `pauseTimeRemaining` | overview | yes | yes | - | - |
| `phaseMinutes`, `phaseMinutesRB` | overview (`phaseLengthInMinutes` in status/pulse) | yes | yes | `phaseLengthInMinutes` | - |
| `nextPhaseMinutes`, `phaseSwitchPeriod`, `startTime` | page only (`startTime` also in overview) | - | yes | - | - |
| `gameOver` | overview, status, pulse | - (uses `phase == Finished`) | yes | yes | key must exist |
| `pressType` | overview, status, pulse | yes | yes | yes | - |
| `potType` | overview, status, pulse | - | yes | yes | - |
| `pot`, `minimumBet` | overview | `pot` | yes | - | - |
| `anon` | overview | display text | yes (drives hiding) | - (not in status; models assume anon) | - |
| `drawType` | overview, status, pulse | - | yes | - (models assume public) | - |
| `missingPlayerPolicy`, `minimumReliabilityRating` | page only | - | yes | - | - |
| `playerTypes` | overview | analytics label | yes | - | - |
| `excusedMissedTurns` (game rule) | overview | yes | yes | - | - |
| `alternatives` (settings summary text) | overview | yes; also `includes("Sandbox")` | built in PHP | - | - |
| `season`, `year` | overview | yes | `Variant->turnAsDate()` | - | - |
| `variant` (whole PHP object) | overview | - | - | - | - |
| `isSandboxMode` | data | yes | yes | - | - |
| private flag (has invite code) | page only | - | yes | - | - |
| director user IDs | page only | - | permission checks only | - | - |
| civil disorder history | page only | - | finished games and moderators | - | - |

### 2.2 Per member (public part)

| Data | Sent by | beta | board.php | Cicero/Dora | gunboat |
|---|---|---|---|---|---|
| `countryID`, `country` | overview/members, pulse | yes | yes | yes | - |
| `userID`, `username` | overview/members | `username`; `user.member.userID` for ads | yes, plus points and user type icons | - | - |
| `status` | overview/members, pulse | yes | yes | - | - |
| `supplyCenterNo`, `unitNo` | overview/members, pulse | yes | yes | - | - |
| `bet`, `pointsWon` | overview/members | yes | yes | - | - |
| `orderStatus` of others | overview/members, status (`orderStatuses`), pulse | yes | yes | - | - |
| `votes` of others | overview/members, status (`publicVotes`), pulse | yes | yes | only as vote-log messages | - |
| `missedPhases`, member `excusedMissedTurns` | overview/members | `excusedMissedTurns` | yes | - | - |
| `timeLoggedIn` | overview/members | - | "last seen" | - | - |
| `online` | overview/members | - | - (hard-coded 0) | - | - |

### 2.3 Board state and history

| Data | Sent by | beta | board.php | Cicero/Dora | gunboat |
|---|---|---|---|---|---|
| current units `{id, terrID, countryID, type}` | data, `<turn>-json.map`, last status phase | yes | yes | from status | from status |
| territory status `{id, ownerCountryID, unitID, standoff, occupiedFromTerrID}` | data, `<turn>-json.map` | yes | yes | - | - |
| `standoffs`, `occupiedFrom` | status | - | - | - | in Retreats only |
| retreating units | status (`retreating`); inferred elsewhere (a unit no territory status points at) | yes | inferred | - | yes |
| `phases[].units`, `.orders`, `.centers` | status | yes (history view; spectators use it for the live board too) | via `map.php` and the archive pages | units, orders (re-adjudicated from 1901) | last 16 phases |
| `orders[].success` | status | yes | yes | - | - |
| `orders[].dislodged` | status | - | yes | - | - |
| static territories `{id, name, type, supply, countryID, coast, coastParentID, smallMapX/Y, Borders, CoastalBorders}` | data (every call), `territories.js` | all but `coast`, `smallMapX/Y` | all | - (own tables) | - (own tables) |

### 2.4 Private to the player

| Data | Sent by | beta | board.php | Cicero/Dora | gunboat |
|---|---|---|---|---|---|
| own `orderStatus`, `votes`, `status` | overview (`user.member`), status, pulse, players/pulse | yes | yes | `orderStatus`; `votes` before a draw vote | - |
| own current orders | data (`currentOrders`), page | yes | yes | - (keeps its own) | - |
| order save token `context` + `contextKey` | data, page | yes (opaque) | yes | - | - |
| private messages, notes to self, vote log | getmessages, status (`phases[].messages`) | yes | server-rendered, 50 newest per tab | full history on every rebuild | - |
| global messages | getmessages, status | yes (members only; spectators get 403) | yes, also for non-members through the archive | yes | - |
| `newMessagesFrom` | getmessages, overview, active_games | from getmessages | yes | - | - |
| muted countries | page only | - | yes | - | - |
| SSE token | overview (`user.sseAuth`), page, sse/authentication | yes | yes | - | - |
| `isTempBanned` | overview | yes | yes | - | - |
| list of the user's games | active_games, players/pulse | `gameID, name, turn, phase, processTime` | - | discovery + change detection | discovery |

## 3. Problems the new design has to avoid

Efficiency:

1. Every read endpoint builds the full `Game` object (game row with tournament join, all members
   joined to users, civil disorders) just to check membership. `game/status` then loads the game a
   second time.
2. `game/status` rebuilds the whole history from the archive tables per request. The Redis cache of
   archive rows helps, but seven bots in one game still make seven identical full fetches per phase,
   and Cicero downloads it 3 to 4 times per message it sends.
3. `game/data` sends the static territory table (about 40 KB) on every phase change.
4. The same facts arrive from several endpoints: `turn`/`phase` from five, the viewer's
   `orderStatus` from four, current units from two.
5. A `processed` event costs each connected beta client 4 API calls; the board refetches
   `players/active_games` on every phase change only to fill a games list.
6. The bots poll: Dora calls `players/pulse` about 0.85 times a second because an idle sleep is
   skipped; the gunboat bots make 8 list requests per 5 s.

Correctness and consistency:

7. `game/data` passes `variantID` to a function taking a `mapID`. It works only while they are equal.
8. Territory `name` goes through `l_t()`, and the beta board joins its map to server data by that
   name, so a translated name would break the board.
9. Value types differ per endpoint: `game/overview` uses `JSON_NUMERIC_CHECK` (so a numeric game
   name becomes a number), `game/data` sends raw DB strings with `"Yes"`/`"No"`, `game/status` sends
   ints but `processTime` as a string. Clients are written around each quirk; one of them caused a
   bug in the bots' ready flag (fixed in webdiplomacy_bots `ec2d047b`).
10. The two boards disagree on anonymity: board.php reveals identities when `phase == 'Finished'`;
    `game/members` reveals `username` when `gameOver != 'No'` but hides `userID` and `orderStatus`
    forever.
11. Response envelopes differ per route (`{msg, success, referenceCode, data}`, bare objects, a bare
    string for `game/setvote`).
12. Board-side vote toggles, order-status changes, pause/unpause and vote tallies publish no SSE
    event, so other clients don't see them until something else makes them refetch.
13. Spectators can never connect to the SSE server: their token is for `private-game<N>` but the
    server validates `private-game<N>-country<C>`. The beta retries every 17 s forever.
14. `beta-src` `loadGame` calls `loadGameData(...)` without dispatching it; the data load only
    happens as a side effect of a reducer flag read during render.

Security:

15. `game/getmessages` reads the Redis last-message time before checking membership, so any
    logged-in user can learn when any country in any game last sent or received a message.
16. The SSE server validates the token against the one country channel and then subscribes to every
    other channel the client listed, including other games' overview channels.
17. `map.php` serves sandbox games' maps and unit JSON to anyone who knows the game ID, although
    `board.php` restricts sandbox games to their creator, moderators and share-link holders.
18. `ajax.php` trusts `orderStatus` from the signed context and never enforces `tokenExpireTime`.
    Whatever mints that token must check membership first.
19. `wD_Games.password` is loaded into the `Game` object, and `game/overview` serialises the whole
    variant object. Nothing sensitive leaks today, but serialising PHP objects wholesale is how it
    would.
20. API keys are logged in clear text by both bot repos.
