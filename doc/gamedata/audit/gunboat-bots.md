# MILA gunboat bots: field-level audit of webDiplomacy API usage

Audited 2026-09-19, read-only. Repo: `~/Desktop2/webdiplomacy_bots_mila` (HEAD `9473c017`, clean).
Server-side shapes cross-checked against `~/Desktop2/webdiplomacy` (`api.php`, `api/responses/*.php`).

All HTTP traffic to webDiplomacy comes from two places; nothing else in `data/diplomacy`, `docker/`, or the
engine package calls the API (`docker/selftest.py` explicitly does not).

| Short name | Path |
|---|---|
| BOT | `data/diplomacy/diplomacy_research/scripts/launch_bot_webdip.py` (355 lines; loop, scheduling, model choice) |
| API | `data/env3.7/lib/python3.7/site-packages/diplomacy/integration/webdiplomacy_net/api.py` (HTTP client, 4 routes) |
| GAME | `.../webdiplomacy_net/game.py` (response -> `diplomacy.Game`) |
| ORD | `.../webdiplomacy_net/orders.py` (order string <-> webDip order dict) |
| UTIL | `.../webdiplomacy_net/utils.py` (baked-in ID tables) |
| BASE | `.../diplomacy/integration/base_api.py` (key, Tornado client, timeouts) |

The `webdiplomacy_net/` files are force-tracked in git even though the rest of `env3.7/` is ignored, so they
can be edited and committed like normal source.

---

## 1. Routes called

Exactly four routes. URL is always `API_WEBDIPLOMACY + '?' + urlencode({...})` (API:36, default
`https://webdiplomacy.net/api.php`). Every request carries `Authorization: Bearer <key>` and
`User-Agent: KestasBot / Philip Paquette v1.0` (API:35, 259-281).

| Route | Method | Function (file:line) | Query / body | When |
|---|---|---|---|---|
| `players/cd` | GET | `API.list_games_with_players_in_cd` API:42-74, route at API:48 | `route` only | Every cycle, once per `API_KEY_CD_nn` key, in parallel with the below (BOT:96) |
| `players/missing_orders` | GET | `API.list_games_with_missing_orders` API:77-108, route at API:82 | `route` only | Every cycle, once per `API_KEY_USER_nn` key (BOT:97) |
| `game/status` | GET | `API.get_game_and_power` API:111-152, route at API:126-127 | `route`, `gameID`, `countryID` (always both) | Once per listed (gameID, countryID) per cycle, sequentially (BOT:112-113, 138). `max_phases=16` is applied client-side only, not sent |
| `game/orders` | POST (raw JSON body) | `API.set_orders` API:155-255, route at API:197-198 | query: `route`; body: see section 4 | Once per listed pair per cycle, straight after inference (BOT:159) |

Not called anywhere: `players/active_games`, `players/pulse`, `game/pulse`, `game/overview`, `game/data`,
`game/members`, `game/join`, `game/leave`, `game/togglevote`, `game/setvote`, `game/sendmessage`,
`game/getmessages`, `game/messagesseen`, `game/markbackfromleft`, `sandbox/*`, `sse/*`, `push/*`.

HTTP client facts (relevant to a static-file design):

- Tornado 6.0.3 `simple_httpclient` (BASE:40). `Connection: close` on every request, so a new TCP/TLS
  connection per call. No conditional GET (no `If-None-Match` / `If-Modified-Since`), no client cache.
- Sends `Accept-Encoding: gzip` and decompresses (Tornado default), follows up to 5 redirects.
- `connect_timeout=30`, `request_timeout=60` (BASE:29).
- `AsyncHTTPClient()` is a per-IOLoop singleton with `max_clients=10`, so at most 10 list calls are in flight
  even with 40 keys.
- The POST has no explicit `Content-Type`; Tornado adds `application/x-www-form-urlencoded` although the body
  is JSON. The server reads `php://input`, so it works; a stricter new endpoint would reject it.
- JSON is parsed with `ujson`.

---

## 2. Response fields read, per route

### 2.1 `players/cd` and `players/missing_orders` (identical handling)

Expected: a JSON array of objects. Non-200 or empty body -> warning, empty list (API:69-71, 103-105). Bad JSON ->
warning, empty list (API:63-65, 97-99).

| Field | Read at | Use | Type requirement |
|---|---|---|---|
| `[].gameID` | API:67, API:101 | Passed back as `gameID` to `game/status`; error-throttle key (BOT:307, 316); "models per power" log-once set (BOT:146-148) | any scalar |
| `[].countryID` | API:67, API:101 | Passed as `countryID` to `game/status`; then used **uncast** as a dict key at GAME:348 (`CACHE[map_id]['ix_to_power'][country_id]`) | **must be a JSON number**; a string gives an uncaught `KeyError` and a bot restart |

Nothing else is read. List order is preserved; only the first `max_batch_size` = 32 rows per key are used per
cycle (BOT:105). Pairs are de-duplicated across keys, CD keys first (BOT:92, 104-109), so if a CD key and a
user key list the same pair the CD key is the one used for status and orders.

The bot relies entirely on server-side filtering here (`unordered_countries.php:65-76`: `orderStatus` empty,
`status='Playing'`, `variantID IN (1,15,23)`, `processStatus='Not-processing'`, phase in
Diplomacy/Retreats/Builds, `turn < 100`). It has no variant, phase or "already submitted" check of its own
before fetching the full state.

### 2.2 `game/status`

Top level (`GameState` in `api/responses/game_state.php`). "Required" means listed in `req_fields` at GAME:248:
if the key is absent the whole response is rejected (`None, None`, logged, counted as an error).

| Field | Required key | Value read? | Where / use |
|---|---|---|---|
| `gameID` | yes | **yes** | GAME:254 `str()`. Becomes `game.game_id`; sent back as `gameID` in the orders POST (API:183 `int()`), used in logs, and hashed to pick the model per power (BOT:171-172) |
| `variantID` | yes | **yes** | GAME:255 `int()`. Selects the ID tables and the engine map (GAME:263, 271). Anything other than 1, 15, 23 raises an uncaught `KeyError` |
| `turn` | yes | no | presence only |
| `phase` | yes | no | presence only. The current phase is taken from the **last element of `phases[]`**, never from here |
| `gameOver` | yes | no | presence only. Finished games are detected via a `phases[].phase == 'Finished'` entry instead (GAME:179, 266-268) |
| `phases` | yes | **yes** | GAME:260-263; only the last 16 elements are used (`max_phases=16`, BOT:138, GAME:261-262) |
| `standoffs` | yes | **yes**, only when the last phase is `Retreats` | GAME:256, 328-331 |
| `occupiedFrom` | yes | **yes**, only when the last phase is `Retreats` | GAME:257, 334-338 |
| `countryID` | no | no | ignored; the bot uses the `countryID` it got from the list call (GAME:348) |
| `potType` | no | no | ignored |
| `pressType` | no | no | ignored |
| `drawType` | no | no | ignored |
| `processTime` | no | no | ignored (the bot has no notion of deadlines) |
| `phaseLengthInMinutes` | no | no | ignored |
| `publicVotes` | no | no | ignored |
| `orderStatuses` | no | no | ignored |
| `votes` | no | no | ignored |
| `orderStatus` | no | no | ignored (never checks whether it already submitted) |
| `status` | no | no | ignored |

`standoffs[]`, read at GAME:329-331 through `center_dict_to_str` (GAME:84-116):

| Field | Read | Note |
|---|---|---|
| `standoffs[].terrID` | yes, `int()` GAME:100 | territory excluded as a retreat destination |
| `standoffs[].countryID` | yes, `int()` GAME:101, must be a key of `ix_to_power` (GAME:107) | The server sends `0` (`game_state.php:341`), which maps to `GLOBAL`. **If this key is dropped or invalid, the standoff is silently ignored** (error logged, `''` added to the invalid set) and the bot may order a retreat into a standoff territory |

`occupiedFrom`: must be a JSON **object** `{ "<terrID>": <fromTerrID>, ... }` or something falsy (PHP emits `[]`
when empty). Read with `.items()` at GAME:335; key and value both `int()`-cast (GAME:336-337). A non-empty
array would raise `AttributeError`. Use: a dislodged unit may not retreat to the territory it was attacked from.

Per element of `phases[]` (`process_phase_dict`, GAME:175-223):

| Field | Read | Where / use | Type requirement |
|---|---|---|---|
| `phases[].turn` | yes | GAME:185 -> `turn_to_phase` GAME:20-27: `year = 1901 + turn // 2`, season S/F from parity, `W` for Builds | **must be a JSON number** (`//` on a string is an uncaught `TypeError`). Defaults to 0 if absent |
| `phases[].phase` | yes | GAME:179, 185, 211. `'Diplomacy'`/`'Retreats'`/`'Builds'` -> `M`/`R`/`A`; `'Finished'` aborts the whole conversion; anything else (e.g. `'Pre-game'`) is an uncaught `KeyError` | string. Defaults to `'Diplomacy'` |
| `phases[].units` | yes | GAME:189-195 | list, default `[]` |
| `phases[].centers` | yes | GAME:199-205 | list, default `[]` |
| `phases[].orders` | yes | GAME:209-217 | list, default `[]` |
| `phases[].messages` | no | ignored | |
| `phases[].publicVotesHistory` | no | ignored | |

`phases[].units[]` (`unit_dict_to_str`, GAME:35-78). All four keys are required (GAME:46-49) or the unit is dropped with an error log:

| Field | Read | Use |
|---|---|---|
| `unitType` | GAME:52, must be `'Army'` or `'Fleet'` (GAME:58) | first letter -> `A`/`F` |
| `terrID` | GAME:53 `int()`, must be in the variant table (GAME:61) | location, coast-specific IDs give `STP/NC` etc. |
| `countryID` | GAME:54 `int()`, must be in the table (GAME:64) | owning power; `0` = `GLOBAL` is skipped later (GAME:279, 306) |
| `retreating` | GAME:55, must be `'Yes'` or `'No'` (GAME:67) | `'Yes'` -> unit is set as dislodged (`*A PAR`), and the engine computes its retreat options |

`phases[].centers[]` (`center_dict_to_str`, GAME:84-116): `terrID` (GAME:100) and `countryID` (GAME:101), both
required and `int()`-cast. Rows with `countryID` 0 become `GLOBAL` and are skipped (GAME:286, 313), so unowned
centers need not be sent.

`phases[].orders[]` (`order_dict_to_str` GAME:133-166, then `Order._build_from_dict` ORD:449-647):

| Field | Read | Use | Type requirement |
|---|---|---|---|
| `countryID` | GAME:143-149, required, `int()` | which power issued the order | number or numeric string |
| `terrID` | ORD:455 | unit location. If `null`, `toTerrID` is used instead (ORD:463) | **JSON number, not string** (uncast dict lookup at ORD:481, 489). A string is logged as "Received invalid loc" and the order is dropped |
| `unitType` | ORD:456 | `'Army'`/`'Fleet'`; overridden for `Build Army`/`Build Fleet` (ORD:466-469); `'?'` allowed for `Destroy`/`Wait` (ORD:470-471) | string |
| `type` | ORD:457, validated at ORD:477-480 | one of `Hold`, `Move`, `Support hold`, `Support move`, `Convoy`, `Retreat`, `Disband`, `Build Army`, `Build Fleet`, `Wait`, `Destroy` | string |
| `toTerrID` | ORD:458, 490 | destination / supported or convoyed destination / build or destroy location | **JSON number**; `0` or `''` = none |
| `fromTerrID` | ORD:459, 491 | source of a supported or convoyed move | **JSON number** |
| `viaConvoy` | ORD:460, must be `'Yes'`, `'No'`, `''` or `null` (ORD:484) | appends ` VIA` to a Move (ORD:516) | string |
| `turn` | no | ignored (the enclosing phase's `turn` is used) | |
| `phase` | no | ignored | |
| `success` | no | ignored; the bot re-adjudicates history with its own engine, then overwrites units and centers from the next phase | |
| `dislodged` | no | ignored | |

Known harmless noise: historical `Wait` orders arrive with `terrID: 0` (`intval(null)` in `order.php`), which
ORD:481 logs as "Received invalid loc" and drops.

What the history is actually used for (so a static file could be trimmed safely):

1. The model's `prev_orders_state` input: the orders, units and centers of the most recent Diplomacy phase
   among the last 3 history phases (`NB_PREV_ORDERS=1`, `NB_PREV_ORDERS_HISTORY=3`,
   `diplomacy_research/models/state_space.py:98-99, 166`; `models/policy/order_based/dataset/base.py:147-156`).
   Same for the 1v1 model.
2. "Stuck in local optimum" detection on the standard map: compares each power's unit set at the end of the
   current year, year-1 and year-2 (BOT:222-275). This is why 16 phases (3 years x 5 + current) are kept.
3. Everything older than the last 16 phases is sliced off before any processing (GAME:261-262).

The last phase supplies the live position: units (with dislodged flags), centers, and, from them, build/disband
counts and legal orders. Orders in the last phase are never read (GAME:300-315 only sets units and centers).

### 2.3 `game/orders` response

Expected: a JSON array of the country's current orders. Read only when the HTTP code is 200, the bot sent at
least one order, and the body is non-empty (API:216-227). Each element goes through the same
`Order._build_from_dict` as above (API:235), so the fields read are `terrID`, `unitType`, `type`, `toTerrID`,
`fromTerrID`, `viaConvoy` (numbers must be JSON numbers; `terrID: null` falls back to `toTerrID`, which is how
builds and destroys are matched). `countryID` in the response is ignored. See section 4 for how it is compared.

---

## 3. webDip state -> engine state

Conversion is `state_dict_to_game_and_power` (GAME:234-349):

1. `Game(game_id=str(gameID), map_name=CACHE['ix_to_map'][variantID])` (GAME:271).
2. For each of the last 16 phases except the final one: `set_current_phase`, `clear_units` + `set_units`,
   `clear_centers` + `set_centers`, `set_orders`, then `game.process()` (GAME:273-297). The engine's own
   adjudication result is thrown away because the next phase's units and centers are loaded from webDip again.
   The point of the replay is to populate `game.state_history` / `order_history`.
3. Final phase: set phase name, units, centers (GAME:300-315). Dislodged units (`retreating: 'Yes'`) get the
   engine's default retreat list, which is then filtered by occupied locations, `standoffs`, and `occupiedFrom`
   (GAME:318-345).
4. Power name = `CACHE[variantID]['ix_to_power'][countryID]` (GAME:348).

Phase naming: `turn` -> `S`/`F` + `1901 + turn//2` + `M`/`R`/`A`, with `W` for Builds (GAME:20-27). The start
year 1901 is hard-coded here. The reverse mapping for the POST is at API:187-191:
`turn = 2*(year - map.first_year) + (0 if season == 'S' else 1)`, `phase = {'M':'Diplomacy','R':'Retreats','A':'Builds'}`.

### Baked-in tables (UTIL:14-48, indexes built at UTIL:56-72)

| Table | Content |
|---|---|
| `CACHE['ix_to_map']` / `['map_to_ix']` | `1: 'standard'`, `15: 'standard_france_austria'`, `23: 'standard_germany_italy'` |
| `CACHE[1]['powers']` | index = countryID: `0 GLOBAL, 1 ENGLAND, 2 FRANCE, 3 ITALY, 4 GERMANY, 5 AUSTRIA, 6 TURKEY, 7 RUSSIA` |
| `CACHE[1]['locs']` | index = terrID, 81 entries: 1 `CLY` ... 75 `GAL`, then the coasts last: 76 `SPA/NC`, 77 `SPA/SC`, 78 `STP/NC`, 79 `STP/SC`, 80 `BUL/EC`, 81 `BUL/SC` |
| `CACHE[15]['powers']` | `0 GLOBAL, 1 FRANCE, 2 AUSTRIA` |
| `CACHE[15]['locs']` | 81 entries in a **different order**: coasts are inline (9 `SPA/NC`, 10 `SPA/SC`, 23 `BUL/EC`, 24 `BUL/SC`, 37 `STP/NC`, 38 `STP/SC`), ending 76 `TYR` ... 81 `GAL` |
| `CACHE[23]['powers']` | `0 GLOBAL, 1 GERMANY, 2 ITALY` |
| `CACHE[23]['locs']` | same order as variant 15 |
| Unit types | `'Army'` <-> `A`, `'Fleet'` <-> `F` (GAME:58, 77; ORD:209) |
| Order types | the 11 strings listed above (ORD:477-478) <-> engine letters `H - S C R D B` and `WAIVE` (ORD:201) |
| Phase names | `'Diplomacy'`/`'Retreats'`/`'Builds'` <-> `M`/`R`/`A`; `'Finished'` special-cased (GAME:26, 158, 179; API:191) |
| Yes/No enums | `retreating`, `viaConvoy`, `ready` |

I checked all three `locs` tables against `variants/{Classic,ClassicFvA,ClassicGvI}/cache/territories.js` in
the webDiplomacy repo. Classic: all 81 IDs read through by name against the abbreviations, no mismatch.
ClassicFvA and ClassicGvI: all 81 IDs compared by script (territory name -> Classic abbreviation -> bot table),
no mismatch.

Webdip's "Bulgaria (North Coast)" (terrID 80 in Classic) is the engine's `BUL/EC`.

### Variants and how `variantID` is used

- Supported: 1 (Classic), 15 (ClassicFvA), 23 (ClassicGvI). These are exactly `Config::$apiConfig['variantIDs']`
  on the server, which is the only thing stopping other variants reaching the bot.
- `variantID` picks the ID tables and the engine map. The engine map name then picks the model (BOT:183-219):
  `standard` -> DipNet SL model via TF Serving model `standard`; both 1v1 maps -> model `standard_1v1`. Any other
  map name -> `get_player` returns `None`, logged, **no `add_error`** (BOT:151-154).
- An unsupported `variantID` never gets that far: `CACHE[map_id]` at GAME:61 or GAME:271 raises `KeyError`,
  which `run()` does not catch (it only catches `DiplomacyException`, Tornado `TimeoutError`, `RuntimeError`,
  BOT:117). The exception unwinds to `main()`, which sleeps 5 s and restarts `bot.run()` (BOT:339-352),
  re-creating all 10 players and re-running the opening checks. Because the list order is stable, the same bad
  row is hit again and every game listed after it is starved.

The same crash-and-restart path applies to any schema deviation that raises something other than those three
exception types: string `phases[].turn`, string `countryID` in a list row, empty `phases`, non-object
non-empty `occupiedFrom`, unknown `phases[].phase`. A static JSON generator has to be schema-exact for this
client.

Model choice per power on the standard map is a hash of the game ID: `(3079 * gameID) % 2**7`, one bit per
power (BOT:164-181), using the `gameID` from the `game/status` body. If the static file carried a different ID
from the one the bot sees today (for example raw vs multiplexed), the per-power model assignment would change
mid-game.

---

## 4. Order submission

`API.set_orders(game, power_name, orders, wait=False)` (API:155-255), always called with `wait=False` (BOT:159).

Request: `POST {API_WEBDIPLOMACY}?route=game%2Forders`, body (API:199-206):

```json
{
  "gameID": 12345,
  "turn": 3,
  "phase": "Retreats",
  "countryID": 2,
  "orders": [ { "terrID": 47, "unitType": "Army", "type": "Move", "toTerrID": 48,
                "fromTerrID": "", "viaConvoy": "No", "convoyPath": [47, 60] } ],
  "ready": "Yes"
}
```

| Body field | Source |
|---|---|
| `gameID` | `int(game.game_id)`, i.e. the `gameID` from the `game/status` **body**, not from the list row (API:183) |
| `turn`, `phase` | derived from the engine's current phase, i.e. from the last `phases[]` element (API:185-194). `turn=-1`, `phase='Diplomacy'` if the engine says `COMPLETED` |
| `countryID` | `CACHE[map]['power_to_ix'][power_name]`, `-1` if unknown (API:184) |
| `orders[]` | one dict per successfully parsed order string (API:203) |
| `ready` | always `"Yes"` (API:204-205 with `wait=False`). The bot never saves without readying and never un-readies |

Per-order dict, by type (ORD:166-447). Absent values are the empty string `''`, not `null`, except `terrID` for `Wait`:

| Engine order | `terrID` | `unitType` | `type` | `toTerrID` | `fromTerrID` | `viaConvoy` | `convoyPath` |
|---|---|---|---|---|---|---|---|
| `A PAR H` | unit loc | Army/Fleet | `Hold` | `''` | `''` | `''` | - |
| `A PAR - BUR [VIA]` | unit loc | Army/Fleet | `Move` | dest (coast-specific ID for fleets) | `''` | Fleet: `'No'`. Army: `'Yes'` if the order ends in `VIA` **or the destination is not adjacent by land**, else `'No'` (ORD:242-249, 263) | for armies, whenever any chain of fleets at sea links the two coasts, **even when `viaConvoy` is `'No'`** (ORD:250, 264-265): `[src, fleet1, ...]`, destination excluded |
| `A PAR S A BUR` | unit loc | | `Support hold` | supported unit's loc, coast stripped (ORD:276) | `''` | `''` | - |
| `A PAR S A MAR - BUR` | unit loc | | `Support move` | dest, coast stripped (ORD:305) | mover's loc | `''` | if the supported unit is an army on a coast and a fleet chain exists that excludes the supporter (ORD:322-323, 332-333) |
| `F ENG C A LON - BRE` | unit loc | Fleet | `Convoy` | dest | army's loc | `''` | path including this fleet (ORD:366, 375-376) |
| `A PAR R BUR` (any ` - ` is rewritten to ` R ` in a retreat phase, ORD:174-175) | unit loc | | `Retreat` | dest | `''` | `''` | - |
| `A PAR D` in a retreat phase | unit loc incl. coast | | `Disband` | `''` | `''` | `''` | - |
| `A PAR B` / `F BRE B` | build loc | Army/Fleet | `Build Army` / `Build Fleet` | same as `terrID` | `''` | `''` | - |
| `A PAR D` in an adjustment phase | loc with coast stripped | | `Destroy` | same as `terrID` | `''` | `''` | - |
| `WAIVE` | `null` | `''` | `Wait` | `''` | `''` | `''` | - |

The bot does not send a per-order `countryID`; the server fills it in (`api.php` SetOrders, "Bots that were
done before sandbox mode won't provide this").

One client-side adjustment precedes submission: if two retreats target the same territory, the later ones are
turned into disbands (BOT:278-300).

Response handling and errors:

| Condition | Result |
|---|---|
| Connection error / timeout (`HTTP_ERRORS`, API:33-34) | logged, `False` (API:211-213) |
| HTTP code != 200 (e.g. 400 "Invalid turn, expected ...", 403) | warning with body, `False` (API:216-218) |
| 200 and the bot sent no orders | `True`, body not read (API:221-222). The POST is still sent, so it readies up |
| 200 with empty body, or bad JSON | warning, `False` (API:225-234) |
| 200 with JSON array | each returned order is normalised to a string keyed by unit (`'A PAR'`; disbands/destroys keyed `'? PAR'`), support/convoy unit letters and ` VIA` stripped (ORD:661-672). Every **submitted** order must equal the server's order for that unit, otherwise a warning `Submitted: "..." - Server has: "..."` and `False` (API:246-255). Extra orders on the server are ignored |

On `False` the only consequence is `add_error(gameID, countryID)` (BOT:160-161). There is no retry of the POST
by itself and no memory of computed orders. If the server still lists the pair next cycle (orders were not
saved), the bot repeats the full `game/status` fetch, replay and inference. Throttle: once a pair has 5 errors
inside 300 s it is skipped, without any HTTP call, until errors age out (BOT:28-30, 130-134, 310-327); that
settles at about one full retry per minute, indefinitely. If the server dropped only some orders as invalid, it
still saves the rest and sets Ready, so the pair leaves `missing_orders` and the mismatch is just a log line.

---

## 5. Auth and identity

- Keys come only from environment variables `API_KEY_CD_01`..`_20` and `API_KEY_USER_01`..`_20` (BOT:61-67),
  set in `docker/.env`. The current `.env` has 1 CD key and 7 user keys, pointed at the dev stack.
  One `API` object per key; the key is sent as `Authorization: Bearer <key>` on every request.
- **Keys are written to the log in plain text at startup** (BOT:64, 67), so they are in every
  `logs/**/log_bot_*.txt` and in `docker compose logs`.
- The bot never joins games and has no configured list of games or countries. It learns what it plays solely
  from the two list routes: a user key yields the (gameID, countryID) pairs its account is `Playing` with no
  saved orders; a CD key yields the global list of countries in civil disorder.
- One key = one webDip account = at most one country per game. A seven-bot game needs seven user keys, and the
  bot then makes seven separate `game/status` calls for the same game each phase, one per key, whose responses
  differ only in fields it ignores (`countryID`, `votes`, `orderStatus`, `status`, `messages`).
- Multiplexing: the bot has no multiplexing logic and never sends `multiplexOffset`. It is transparent to it,
  because it echoes whatever `gameID` the server returns: list row -> `game/status` query, `game/status` body ->
  orders POST. `MILA_BOT_API_MIGRATION.md` in the webDiplomacy repo states the MILA keys are not multiplexed.
  With a static game file, the POST `gameID` would come from that file, which matters only if a key ever is
  multiplexed.
- The pair is always served by the key that listed it (BOT:104-113).

---

## 6. Messages and votes

None. The bot never reads `messages`, `publicVotesHistory`, `votes`, `publicVotes`, `orderStatus` or
`orderStatuses`, and never calls any message or vote route. The model code calls
`extract_message_history_proto(game)` (`players/model_based_player.py:112`), but that reads the engine's
message store, which the converter never fills. The bots cannot vote for a draw, pause or cancel, and do not
notice if others do.

---

## 7. Polling cadence and load

Loop (BOT:94-119): all list calls in parallel -> de-duplicate -> for each pair sequentially: `game/status`,
replay, TF Serving inference (local gRPC, port 9503), `game/orders` -> sleep `PERIOD_SECONDS` = 5 s
(`--period` overrides). The sleep is added after the work, so the cycle is 5 s plus processing time.

| Quantity | Value |
|---|---|
| Idle calls per cycle | `#CD keys + #user keys` list GETs. With the current 1 + 7 keys: 8 per 5 s, about 1.6 req/s, about 138,000 list requests per day when nothing is happening. Each is a fresh connection |
| Calls per pending (game, country) | 1 `game/status` + 1 `game/orders`. In the Nov 2025 dev log six powers of one game were served in about 2.5 s, roughly 0.4-0.5 s per power |
| Calls per phase of a 7-bot game | 7 `game/status` (full history each time, 7 near-identical payloads) + 7 POSTs, all in the first cycle after the phase opens |
| Per-cycle cap | first 32 rows per key (`max_batch_size`, BOT:105, 334) |
| Payload used vs sent | the bot uses the last 16 phases; the server sends the whole game. Rough size: about 10 KB per late-game Diplomacy phase uncompressed (34 orders at about 190 B, 34 units at about 70 B, 34 centers at about 30 B), so a 20-year game is several hundred KB per fetch, times 7. These are estimates from the field layout, not measurements |
| Failure mode | any failed pair is re-fetched and re-inferred up to 5 times per 300 s, forever |
| Outage behaviour | during an outage the list calls keep firing every 5 s per key (71,520 "Unable to connect" lines in one log file) |

Efficiency implications for the planned design:

- Everything the bot reads from `game/status` is country-independent, so one static per-game JSON can replace
  it outright for these bots. A seven-bot game would hit one cacheable file seven times instead of running
  seven full state builds. The file only needs the last 16 phases and only the fields marked "yes" in 2.2.
- These bots need nothing from a per-player context except discovery: "which (gameID, countryID) pairs on this
  key need orders now". They read no member-specific field. If `players/missing_orders` and `players/cd` are
  among the routes being removed, `game/playercontext` (or `players/pulse`) has to provide that list per key,
  including `variantID` and ideally `turn`/`phase`, otherwise the bot would have to poll every active game.
- The client cannot do conditional GETs, so static files save server CPU and DB work, not request count. Request
  count only drops by polling less often or by polling the list from fewer keys.
- Ordering constraint: the bot trusts the last `phases[]` element as the current phase and posts that
  `turn`/`phase`. The static file must be regenerated before the game shows up in the missing-orders list for
  the new phase, otherwise the POST is rejected with "Invalid turn/phase" and the pair burns error slots.

---

## 8. Surprises

1. **Civil-disorder takeover looks broken on the server side.** The bot always sends `countryID` to
   `game/status` (API:127). Since commit `af4b0c5b` (2022-06-03) `api.php:792` throws
   `ClientForbiddenException('A user can only view game state for the country it controls.')` when the caller is
   not the member holding that country, which is always the case for a CD key. Per the code in
   `~/Desktop2/webdiplomacy` the CD path would get a non-200, log a warning, and count an error, 5 times per
   300 s inside the 60 s pre-deadline window. I did not check production logs to confirm. The `players/cd` poll
   is therefore probably pure overhead today.
2. API keys are logged in plain text (section 5).
3. The bot never looks at top-level `turn`, `phase`, `gameOver`, `orderStatus` or `countryID`, though five of the
   keys must exist. The current phase is whatever `phases[-1]` says.
4. `standoffs[].countryID` must be present and valid (the server sends 0) or standoffs are silently ignored.
5. Type strictness is inconsistent: unit and center IDs are `int()`-cast and may be strings, but order
   `terrID`/`toTerrID`/`fromTerrID`, `phases[].turn`, and list-row `countryID` must be JSON numbers.
6. Most malformed inputs do not produce a per-game error; they crash `run()` and restart the whole bot every
   5 s, starving all games behind the bad one (section 3).
7. `get_player() is None` (unsupported map) skips `add_error`, so it would retry unthrottled (BOT:151-154).
   GAME:146 returns a 3-tuple where a 2-tuple is unpacked, another latent crash if an order lacks `countryID`.
8. `convoyPath` is sent on ordinary army moves between two coasts whenever a fleet chain happens to exist, not
   only on convoyed moves; and `viaConvoy` is forced to `'Yes'` when the destination is not land-adjacent.
9. The POST sends JSON with `Content-Type: application/x-www-form-urlencoded`.
10. The hand-added "Finished" guard in GAME:178-183 is tab-indented inside a space-indented file. It works,
    but it is the kind of patch that is easy to lose; it is tracked in git now.
11. `missing_orders` has a `turn < 100` cutoff on the server, so a bot silently stops playing a game past
    W1950A. `turn_to_phase` hard-codes 1901 while the POST side uses `map.first_year`; both are 1901 for the
    three supported maps.
12. The webDiplomacy repo already holds a July 2026 plan for these bots, `MILA_BOT_API_MIGRATION.md`
    (move to `players/pulse`, add `maxPhases` to `game/status`). It agrees with this audit on routes and
    cadence; it does not mention item 1, the type requirements, or the crash-restart behaviour.
