# Audit: what the Cicero / Dora bots consume from the webDiplomacy API

Date: 2026-09-19. Read-only audit; nothing in any repo was modified.

Scope: `/home/kestasjk/Desktop2/webdiplomacy_bots` (branch `singlegpusinglegameswap`, HEAD `08781ed5`).
All API traffic originates in ONE file: `fairdiplomacy_external/webdip_api.py` (4036 lines), called
`W` below (`W:123` = line 123). Support code:

| File | Role |
|---|---|
| `fairdiplomacy_external/webdip_api.py` | every HTTP call, status-JSON -> `pydipcc.Game` replay, main loops |
| `fairdiplomacy_external/run.py` | entrypoint, registers the `play_webdip` task |
| `fairdiplomacy/webdip/utils.py` | `turn_to_phase(turn, subphase)` / `phase_to_turn` / `phase_to_phasetype` |
| `fairdiplomacy/data/build_dataset.py` | static tables: `GameVariant` (26), `TERR_ID_TO_LOC` (41), `COUNTRY_ID_TO_POWER[_OR_ALL]` (126/136), FvA tables (148/233/235), `*_BY_MAP`/`*_MY_MAP` (237-250), `COUNTRY_POWER_TO_ID` (252), vote strings (280-286), `get_valid_coastal_variant` (814) |
| `fairdiplomacy/webdip/message_approval_cache_api.py` | Redis proposal store (no webDip calls). `PRESS_COUNTRY_ID_TO_POWER = COUNTRY_ID_TO_POWER_OR_ALL` (40), `botgame_fp_to_context` (145) |
| pip `diplomacy==1.1.2`: `diplomacy/integration/webdiplomacy_net/{orders.py,utils.py}` | outbound order string -> webDip order dict (`Order.to_dict()`), with its own baked `CACHE` loc<->ID tables. In the container: `/opt/conda/envs/diplomacy_cicero/lib/python3.7/site-packages/diplomacy/...` |
| `bin/game_france_austria.json` | baked FvA starting position |
| `conf/c07_play_webdip/play.prototxt`, `play_dora_fva.prototxt`, `conf/conf.proto:1221` (`PlayWebdipTask`) | config |
| `runWebDip_Live_CICERO.sh`, `runWebDip_2player_DORA_FvA.sh` | the two live launchers (docker services `cicero` and `dora`, `docker/compose.yml`) |

Server-side field lists were cross-checked against `/home/kestasjk/Desktop2/webdiplomacy/api.php` and
`api/responses/*.php` (read only).

No other file in the bot repo talks to webDip (`webTest.py` only GETs the home page; `fairdiplomacy_external/ui` is rendering only).

---

## 0. Headline findings

1. The code references **9 routes**; **4 are hit routinely in the live configuration** (`players/pulse`, `game/status`,
   `game/orders`, `game/sendmessage`) and **2 rarely** (`game/togglevote` with its `game/pulse` pre-check, only when Cicero casts a draw vote).
   `players/missing_orders` is dead code, `players/active_games` is only used with a `game_name` filter (not configured),
   and `game/getmessages` + the hot `game/pulse` polling are **disabled by the live Cicero flags**
   (`only_bump_msg_reviews_for_same_power=True` with no `recipient` makes `should_stop` a constant `False`, `W:2185-2187`;
   0 "Should stop check" lines in the 40 most recent game logs).
2. The bot **never calls** `game/messagesseen`, `players/cd`, `game/setvote`, `game/members`, `game/overview`, `game/data`, `game/markbackfromleft`, `sse/*`.
3. `game/status` is consumed as a **full replay**: the bot rebuilds a `pydipcc.Game` from S1901M by re-adjudicating every
   archived order itself. It **ignores** `centers`, `standoffs`, `occupiedFrom`, `orderStatuses`, `publicVotes`, `drawType`, `status`,
   unit `countryID`/`retreating`, and order `success`/`dislodged`. Units are only used as a terrID -> unit-type lookup.
4. Message history is needed **in full on every rebuild** (no incremental path exists); any change in the pulse key triggers a
   full re-download + full replay. Each bot-to-bot message costs about 3 full-history downloads (see 1.3).
5. All ID mapping tables are baked into the bot (two independent copies that currently agree); the static variant JSON does not need to supply them, but **must keep emitting integer IDs with `0` for "no territory"** (see 4).
6. Type trap: in `game/status`, `processTime` and `phaseLengthInMinutes` are JSON **strings** (mysqli strings passed through), while in
   `players/pulse` / `game/pulse` they are ints. `W:2040` passes the status string un-cast into `Timestamp.from_seconds`, which does
   `int(x * 100)` = string repetition (see 8.1). Pick one type in the new spec and tell me; the bot needs a cast either way.
7. Bot persistent state (Redis proposals, checkpoints, game JSON/log file names) is keyed by the **multiplexed** gameID + power. If the
   new scheme changes the ID the bot sees, all of that state is orphaned.
8. Dora (FairBot) busy-loops `players/pulse` at ~0.85 req/s, 24/7 (15,814 sweeps between 04:38 and 09:48 today) (see 8.2).
9. API keys are written in clear text to `logs/main.log` and the per-game logs on every sweep (`Context` repr includes `api_key`; also `W:3800`, the `logs/cfg` dump, the checkpoint file name `W:780`, and the process command line). Not reproduced here.

---

## 1. Routes called

URL is always `cfg.webdip_url + "/api.php"` (`W:3704`; live value `https://webdiplomacy.net//api.php`, note the double slash).
The `WEBDIP_URL` env var is mandatory (`W:119`) but only logged; it is not used to build requests.
Transport: `requests` session with 5 adapter retries, 60 s timeout, header `Authorization: Bearer <key>` (`W:356-379`, `W:766`).
GET params go in the query string; POST sends `route` in the query string and a JSON body.
Most wrappers are decorated with `retry_on_connection_error` (`W:783`): retries after 5/10/30/60/120/300 s on connection errors, read timeouts
and **any non-JSON body** (webDip `die()` pages surface as `WrappedJSONDecodeError`).

### 1.1 Route table

| # | Route | Verb | Function (file:line) | Params sent | Live? |
|---|---|---|---|---|---|
| 1 | `players/pulse` | GET | `WebdipBotWrapper.get_player_pulse_json` `W:1311-1336`; callers `find_context` `W:1404`, `collect_actionable_work` `W:1524` | `route`, plus `multiplexOffset`=1..7 (7 sequential requests) when `USE_MULTIPLEXING` is set, else none | Yes, the sweep driver for both bots |
| 2 | `game/status` | GET | `get_status_json` `W:382-405` (wrapper `W:1970`); callers `get_current_game_state` `W:1379`, `send_message` `W:2636`/`W:2680`, `poll_until` `W:899`, batch finalize `W:3442`, per-visit loop `W:3832`/`W:3973` | `route`, `gameID` (multiplexed), `countryID` | Yes |
| 3 | `game/orders` | POST | `submit_orders` `W:2983-3100` (post at `W:3043`), `set_orders_ready` `W:2955-2980`, `check_orders_corner_cases` `W:3103-3145` (post at `W:3140`) | body: `gameID`, `countryID`, `turn`, `phase`, `orders[]`, `ready` ("Yes"/"No") | Yes |
| 4 | `game/sendmessage` | POST | `send_message` `W:2621-2692` (post at `W:2655`) | body: `gameID`, `countryID`, `toCountryID`, `message` | Yes (Cicero) |
| 5 | `game/togglevote` | POST (server declares GET) | `set_draw_vote` `W:830-887` (post at `W:860`) | ALL in the query string: `route`, `gameID`, `countryID`, `vote`="Draw"; body is JSON `null` | Yes but rare (1 of the 150 most recent game logs, last seen 2026-08-19) |
| 6 | `game/pulse` | GET | `get_pulse_json` `W:408-442` (wrapper `W:1977`); callers `set_draw_vote` `W:841`/`W:881`, `should_stop` `W:2209`, fallback `W:2350` | `route`, `gameID`, `countryID` | Only inside `set_draw_vote` in the live config |
| 7 | `game/getmessages` | GET | `get_messages_json` `W:445-477` (wrapper `W:1982`); only caller `should_stop` `W:2229` | `route`, `gameID`, `countryID`, `sinceTime` (= last known message second + 1; server treats it as inclusive) | **No** in live config (see 6) |
| 8 | `players/active_games` | GET | `get_active_games_json` `W:1284-1308`; only caller `game_matches_filters` `W:1359` | `route` (+ `multiplexOffset` 1..7) | **No**: only when cfg `game_name` is set, once per unseen gameID |
| 9 | `players/missing_orders` | GET | `get_missing_orders_json` `W:1279-1281` | `route` | **Dead**: defined, never called. Missing orders are derived from `orderStatus` in the pulse rows instead |

Not called at all: `game/messagesseen`, `players/cd`, `game/setvote`, `game/members`, `game/overview`, `game/data`, `game/markbackfromleft`, `sandbox/*`, `sse/authentication`, `push/*`.

### 1.2 `game/orders` request detail

Built at `W:3018-3040`:

| Body field | Value |
|---|---|
| `gameID` | multiplexed gameID from the pulse row |
| `countryID` | from the pulse row |
| `turn` | `status_json["phases"][-1]["turn"]`, or 0 if `phases` is empty (`W:3018`, `W:2966`). Note: taken from the last phase entry, not top-level `turn` |
| `phase` | "Diplomacy" / "Retreats" / "Builds", derived from the dipcc phase name (`webdip_order_phase`, `W:260-267`), not from the JSON |
| `orders` | list of `WebdipOrder(...).to_dict()`: `terrID`, `unitType` ("Army"/"Fleet"/""), `type`, `toTerrID`, `fromTerrID`, `viaConvoy` ("Yes"/"No"/""), optional `convoyPath` (list of terrIDs). `fromTerrID` coast IDs are collapsed to the parent territory (`W:3037-3040`). No `countryID` per order (server fills it in) |
| `ready` | "Yes"/"No" from `orders_should_be_ready` (`W:2013-2040`): Yes when `ready_immediately` (Dora), in Retreats, in RulebookPress Builds, in NoPress / non-dialogue games, or (Cicero, `ready_when_no_pending_messages=True`) when the current proposal will not be sent before `processTime`; otherwise No |

`set_orders_ready` posts the same envelope with `"orders": []` purely to flip the ready flag (`W:2960-2972`), when the pulse row's `orderStatus`
disagrees with what the bot wants (`_orders_ready_flag_to_set`, `W:1669-1700`).

Order `type` strings sent: `Hold`, `Move`, `Support hold`, `Support move`, `Convoy`, `Retreat`, `Disband`, `Build Army`, `Build Fleet`, `Destroy`, `Wait`
(pip `orders.py:166-448`).

### 1.3 When / how often

**Cicero (batch mode, `WEBDIP_BATCH_CYCLE_SECONDS=20`, `_play_webdip_batch_cycle_loop` `W:3482`)**. One loop iteration =

| Step | Calls |
|---|---|
| Fast sweep `collect_actionable_work` (`W:1511`) | 7x `players/pulse` (offsets 1..7). Then, per game that has missing orders or is a press game this bot talks in: `game/status` **only if** `pulse_change_key` differs from the cached one (`get_current_game_state`, `W:1364-1387`). First sweep after a restart fetches every game (about 90 today). |
| Light service `service_light_item` (`W:1764`) per actionable game | SEND: 1x POST `game/sendmessage` + 1x `game/status` (`W:2680`; up to 10 more at 1 s intervals only if the message is not visible). Missing orders: 1x POST `game/orders`. Ready flip: 1x POST `game/orders` with empty list. Draw-vote message: `game/pulse` + POST `game/togglevote` (+ `game/pulse` on odd body) + `game/status`. |
| Heavy cycle `run_batch_cycle` (`W:3226`), at most every 20 s, only if some game needs a new proposal | No polling in phase A/B (the sweep's items are reused). Finalize (`W:3399-3475`): POST `game/orders` whenever the (phase, message count) dialogue state changed, orders are missing, or the ready flag is outdated; if the new proposal's wakeup has already passed: 1x `game/status` (`W:3442`) then the SEND sequence above. |
| Idle | `time.sleep(5)` when an iteration did nothing (`W:3631`). |

Observed today (`logs/main.log`): idle sweeps about every 13 s with 90 active games; while heavy cycles run (about 650-700 s each, 32-33 games per cycle) one sweep per cycle, i.e. every 10-12 minutes.
In the 40 most recent game logs: 1635 `game/orders` POSTs vs 1414 `game/sendmessage` POSTs, i.e. orders are recomputed and re-posted after practically every message.

Full-history downloads per sent message: the post-send status (`W:2680`) is **not** written back into `cached_status_by_ctx`, so the
next sweep sees a changed `lastMessageTimeSent` and downloads the sender's status again; if the recipient is one of the other six bot
accounts, its context downloads it too. With the wakeup-passed path there is a fourth fetch (`W:3442`). Roughly 3-4 `game/status` per bot-to-bot message.

**Dora / FairBot (per-visit loop, `_play_webdip_without_retries` `W:3785-4036`)**: every iteration calls `find_context` (`W:1389`) = 1x `players/pulse`
(no multiplexing). Missing orders -> 1x `game/status` (`W:3832`, never from cache on this path) + 1x POST `game/orders` (`ready="Yes"`).
Otherwise it walks non-NoPress games round-robin and fetches `game/status` only on a pulse-key change. See 8.2 for why this loop never sleeps.

**On error**: `WebdipGameNotFoundException` (exact HTML body match `GAME_NOT_FOUND_RESP` `W:304`, or for pulse HTTP 400 containing `Unknown game ID`) purges the context for the
life of the process (`purge_ctx`, `W:1075`). Body `Access to this page denied for your account type.` or any non-200 -> `None` -> game skipped this sweep.
`game/orders` 400 bodies starting `Invalid turn, expected` / `Invalid phase` are swallowed (`W:3061-3069`); `Invalid phase, expected \`Retreats\`, got ` and
`Invalid phase, expected \`Builds\`, got \`Diplomacy\`` trigger a re-post of the previous phase's orders (`W:3112-3143`).

---

## 2. Response fields read, per route

### 2.1 `players/pulse` -> `{"games": [row, ...]}`

Envelope: `resp["games"]` (`W:1322-1327`, `W:1332-1336`); missing key -> treated as no games. Rows are de-duplicated by `gameID` across the 7 offsets.

| Row field | Read? | Where | Used for |
|---|---|---|---|
| `gameID` | yes | `W:1327`, `W:1340`, `W:1353`, `W:1539` | Context key (multiplexed ID, passed back verbatim on every call); `game_ids` filter |
| `countryID` | yes | `W:1341` | Context key; this is the ONLY place the bot learns its country |
| `variantID` | yes | `W:1574` (batch loop only) | skip games whose variant != `cfg.variant_id` |
| `pressType` | yes | `W:1449`, `W:1567` | `!= "NoPress"` decides whether dialogue is handled |
| `orderStatus` | yes | `W:1429-1431`, `W:1541-1543`, `W:1694-1698` | comma-split set. "Missing orders" = none of `Saved`, `Ready`, `None` present. `Ready` presence compared with the desired ready flag |
| `turn` | yes (key only) | `W:487` | change key |
| `phase` | yes (key only) | `W:488` | change key |
| `gameOver` | yes (key only) | `W:489` | change key |
| `processTime` | yes | `W:490`, `W:2065` | change key; "will this proposal go out before the deadline" for the log summary. `null`/0 = no deadline |
| `lastMessageTimeSent` | yes (key only) | `W:491` | change key |
| `lastVoteTime` | yes (key only) | `W:492` | change key (`or 0` when absent) |
| `potType`, `drawType`, `phaseLengthInMinutes`, `votes`, `status` | **ignored** | | |

**`pulse_change_key`** (`W:480-493`) = `(turn, phase, gameOver, processTime, lastMessageTimeSent, lastVoteTime or 0)`.
It gates (a) re-downloading `game/status` (`W:1373-1377`) and (b) the "still waiting for wakeup" skip (`_waiting_unchanged`, `W:1724-1762`).
`orderStatus`, `votes`, `status` and `pressType` are NOT in the key. The same function is written to accept a `game/pulse` dict, but today it is only ever fed `players/pulse` rows.

Server note: `players/pulse` only lists games with member `status='Playing'` and `phase IN (Diplomacy, Retreats, Builds)`; finished games and
defeated countries simply vanish from the list, which is how the bot normally stops servicing them.

### 2.2 `game/status` (the big one)

Top level:

| Field | Read? | Where | Used for |
|---|---|---|---|
| `gameID` | log only | `W:3855` | |
| `countryID` | **ignored** | | the bot uses the Context's countryID |
| `variantID` | yes | `W:506-514`, `W:1605`, `W:1741`, `W:2984`, `W:3843`, `W:3863` | choose start position + ID tables; must be 1 or 15 else `ValueError` |
| `potType` | yes | `W:517-527`, `W:3840` | `Unranked`/`Sum-of-squares`/`Points-per-supply-center` -> `SCORING_SOS`; `Winner-takes-all` -> `SCORING_DSS`; anything else raises. `POT_TYPE_CONVERSION[...]` KeyErrors on unknown values |
| `phaseLengthInMinutes` | yes | `W:530-531` | `game.set_metadata("phase_minutes", str(v))`: feeds the sleep classifier and dialogue prompt, and the `== "5"` live-game special cases. Arrives as a **string** |
| `turn` | yes | `W:572`, `W:1996`, `W:3856` | None-phase message handling; duplicate-orders corner case |
| `phase` | yes | `W:572`, `W:1997`, `W:2008`, `W:3857` | `"Finished"` handling for post-game messages; corner case |
| `gameOver` | yes | `W:1494`, `W:1611` | `!= "No"` -> purge context |
| `pressType` | yes | `W:1850`, `W:2025`, `W:2033`, `W:2770`, `W:3869`, `W:4009` | `NoPress` = no dialogue; `RulebookPress` = no messages in R/A phases, Builds ready at once, 5-15 s jitter in 5-minute games |
| `processTime` | yes | `W:2040`, `W:2507`, `W:2513` | phase deadline: ready-flag decision and `phase_end_timestamp` in the stored proposal. Arrives as a **string** or `null` |
| `phases` | yes | see below | |
| `orderStatus` | legacy only | `W:3877` | per-visit loop in parallel-recipient mode |
| `votes` | not from this route | | `get_agent_draw_vote` (`W:816-826`) reads `votes`, but is only ever handed a `game/pulse` dict now |
| `status` | **ignored** | | |
| `drawType` | **ignored** | | models hard-code `draw_type="PUBLIC"` (`parlai_diplomacy/wrappers/base_wrapper.py:307`) |
| `publicVotes` | **ignored** | | |
| `orderStatuses` | **ignored** | | |
| `standoffs` | **ignored** | | |
| `occupiedFrom` | **ignored** | | |
| `anon` | not in payload, not consumed | | models hard-code `anon="ANON"` (`base_wrapper.py:289`) |

Per `phases[]` entry:

| Field | Read? | Where | Used for |
|---|---|---|---|
| `turn` | yes | `W:538`, `W:553`, `W:730`, `W:735`, `W:1996`, `W:2966`, `W:3018`, `W:3858` | message phase derivation; `turn` posted with orders = `phases[-1].turn` |
| `phase` | yes | `W:594`, `W:647`, `W:730`, `W:735`, `W:1997`, `W:3859` | sync (skip an empty Builds phase webDip omitted), retreat-disband handling |
| `units[]` | partly | `W:602-606` | only `terrID` + `unitType` (first letter), to fill in the unit type of supported/convoyed units and of orders whose `unitType` is "". `countryID`, `retreating` **ignored** |
| `orders[]` | yes | `W:608-679` | `countryID`, `terrID`, `fromTerrID`, `toTerrID`, `unitType`, `type`, `viaConvoy`. Converted to dipcc strings, validated against `game.get_all_possible_orders()`, then `game.set_orders` + `game.process()`. `success`, `dislodged`, `turn`, `phase` are **not used for adjudication**; they only take part in the whole-dict equality test of `is_duplicate_last_phase_orders` (`W:729-744`) |
| `centers[]` | **ignored** | | ownership comes from the replay |
| `messages[]` | yes | `W:537-542`, `W:578-589`, `W:496-502` | `message`, `fromCountryID`, `toCountryID`, `timeSent`, `phaseMarker` (all five) |
| `publicVotesHistory[]` | yes | `W:549-569`, `W:2639-2644` | `vote`, `countryID`, `timeSent`, `phaseMarker`. Only "Voted for Draw" / "Un-voted for Draw" (case-insensitive) are kept and turned into `<DRAW>` / `<NODRAW>` messages to ALL; Pause/Cancel/Concede votes are dropped |
| per-phase `orderStatuses` | not present in payload | | |

Order translation rules the payload must keep satisfying (`W:615-638`): IDs are **ints used as dict keys** (`terr_id_to_loc[order_json["terrID"]]`), and
"no territory" must be `0` (maps to `""`), not `null`/`""`. `type[0]` gives the order letter (`M` -> `-`); `Build Army`/`Build Fleet` take the unit from the second word;
`viaConvoy == "Yes"` adds `VIA`; a missing coast is repaired by trying `""`, `/NC`, `/EC`, `/SC`, `/WC` (`get_valid_coastal_variant`). An order that is still
illegal asserts, unless it is the known "final phase duplicated" case.

Consequence: because the board is reconstructed by re-adjudication, the bot needs **every order of every country for every past phase**, from turn 0.
A static per-phase file layout works, but the bot has no code path that starts from a mid-game position (units + centers); see 9.

### 2.3 `game/pulse` -> `{"data": {...}}`

Envelope: must be a dict with key `data` (`W:438-442`).

| Field | Read? | Where | Used for |
|---|---|---|---|
| `votes` | yes | `W:819-826`, written `W:876` | comma-split; is "Draw" present |
| `turn`, `phase` | disabled path | `W:2217` | current phase for the interruption check |
| `lastMessageTimeSent` | disabled path | `W:2220-2222` | "anything new since generation started" |
| `lastVoteTime` | disabled path | `W:2261`, `W:2276` | other countries' draw votes |
| `processTime` | unreachable | `W:2350` -> `W:2507` | both callers of `generate_message_for_approval` pass a status, so this fallback never runs |
| `gameID`, `countryID`, `variantID`, `potType`, `gameOver`, `pressType`, `drawType`, `phaseLengthInMinutes`, `orderStatus`, `status`, `members[]` (`countryID`, `orderStatus`, `status`, `supplyCenterNo`, `unitNo`, `votes`) | **ignored** | | |

### 2.4 `game/getmessages` -> `{"data": {"messages": [...], "time", "newMessagesFrom"}}` (disabled in live config)

| Field | Read? | Where |
|---|---|---|
| `data.messages[].fromCountryID`, `.toCountryID` | yes | `W:2233` |
| `data.messages[].message` | yes | `W:2240`, compared only with the two draw-vote strings |
| `data.messages[].timeSent`, `.turn`, `.phaseMarker`, `data.time`, `data.newMessagesFrom` | **ignored** | |

### 2.5 `game/sendmessage` response

`W:2664-2671`: either a bare int (old server) or `{"messages": [ {...} ]}`; the bot **asserts exactly one element** and reads only `messages[0].timeSent`.
`fromCountryID`, `toCountryID`, `message`, `turn` are ignored. Non-200 -> `WebdipSendMessageFailedException`. The `timeSent` is then looked up in a fresh `game/status` (`message_exists_in_status`, `W:496`).
Note the server returns `{"messages": []}` when the recipient has muted the sender, which would trip the assert.

### 2.6 `game/togglevote` response

Plain-text body (not JSON): the member's new votes string, e.g. `Draw` or `,Draw` (`W:871-877`). Trusted only if every comma token is one of `""`, `Draw`, `Pause`, `Cancel`, `Concede`.

### 2.7 `game/orders` response

JSON list of the country's current orders. Read: `terrID`, `toTerrID`, `type` (`W:3082-3100`) for a sanity check (same number of units, same `type` per unit, tolerating `Destroy` vs `Retreat`).
`unitType`, `fromTerrID`, `viaConvoy`, `countryID` ignored. A mismatch raises `RuntimeError` (kills the process; docker restarts it).

### 2.8 `players/active_games` (only with `game_name`)

Read: `games[].gameID`, `games[].name` (`W:1297-1298`, `W:1360`). Everything else (`countryID`, `orderStatus`, `pressType`, `newMessagesFrom`, `unitNo`, `turn`, `phase`, `processTime`, `phaseMinutes`, `variantID`) ignored.

---

## 3. Message history

| Question | Answer |
|---|---|
| Full history or only new? | **Full, every time.** `webdip_state_to_game` (`W:505-697`) always builds a fresh `Game` and adds every message from every phase. There is no append path. The (status, Game) pair is cached per context and reused only while `pulse_change_key` is unchanged (`W:1373-1387`). |
| How far back is it really needed? | Bot-side bookkeeping scans everything: `get_message_history_length` (`W:2134`) counts all messages ever visible to the power (this count IS the dialogue-state change detector, `W:2079-2106`); `compute_phase_message_history_state_with_power` (`message_approval_cache_api.py:653`) counts all; draw state (`fairdiplomacy/utils/game.py:125`) replays every `<DRAW>`/`<NODRAW>` since the start. The dialogue/draw models themselves truncate to 2048 tokens (`models/dialogue.opt`: `message_history_truncation: 2048`), so old text is not seen by the LM, but counts and draw state need the whole log. |
| Which messages are kept | From `phases[].messages[]`: everything except `fromCountryID == 0` (GameMaster/system) and `toCountryID == fromCountryID` (vote log rows) (`W:541`). So private messages to/from this country **and public broadcasts from players (`toCountryID == 0` -> recipient "ALL") are consumed**. Draw votes come separately from `publicVotesHistory` and are injected as messages from `countryID` to ALL (`W:549-569`). |
| Sender / recipient | `fromCountryID` / `toCountryID` through `COUNTRY_ID_TO_POWER_OR_ALL_MY_MAP[variantID]` (0 = "ALL") (`W:584-585`). |
| Phase | NOT from the phase entry the message sits in (the server files every message under that turn's `Diplomacy` entry). The bot recomputes it as `turn_to_phase(phases[i].turn, message.phaseMarker)` (`W:538`), e.g. (3, "Builds") -> `W1902A`. `phaseMarker` values outside Diplomacy/Retreats/Builds give `None`, then: `"Finished"` while top-level `phase == "Finished"` -> `COMPLETED`; leading ones -> `S1901M`; otherwise an exception (`W:700-726`). Messages are added when the replay reaches that phase (`W:576-599`). |
| Time | `timeSent` (unix seconds) -> `Timestamp` in centiseconds, used as the message key; collisions are bumped by +1 cs (`increment_on_collision=True`, `W:587-588`). |
| Text | `<br />` -> newline, then `html.unescape` (`W:581-582`). |
| Integrity check | after replay `len(all_messages) == len(added)` is asserted (swallowed to Slack handler) (`W:693-695`). A message whose derived phase never occurs in the replay breaks this. |
| Timestamps drive | (1) `wakeup_time = last message time in the current phase (or now, if none) + model sleep time` (`_compute_message_timing`, `W:2285-2327`); the phase start time is unknown to the bot, so it approximates it with "now"; (2) the sleep classifier and dialogue prompt condition on message times and `phase_minutes`; (3) `last_timestamp_when_produced` decides whether a stored proposal is stale (`W:2792-2799`); (4) send confirmation by `timeSent` (`W:2681`); (5) `processTime` vs wakeup decides the orders `ready` flag (`W:2040`, `W:250-257`). A flat 1000 s is subtracted from every wakeup (`W:2470`). |
| Public/global needed? | Player broadcasts: yes (they are part of the model context and the message count). System messages from country 0: fetched and discarded. |
| Redaction | The server serves `game/status` messages from `wD_GameMessages_Redacted` unless `Config::$allowBotsAccessToUnredactedMessages`; the bot blocks redacted tokens in generation (`W:1056`). `game/getmessages` reads the unredacted table. |

---

## 4. ID / type mapping baked into the bot

| Mapping | Inbound (status -> Game) | Outbound (orders/messages -> webDip) |
|---|---|---|
| Variant | `GameVariant` IntFlag: `CLASSIC=1`, `FVA=15` (`build_dataset.py:26-28`). Start position: classic = dipcc built-in `Game()`, FvA = `bin/game_france_austria.json` (`W:506-512`) | pip `CACHE['ix_to_map']` = {1: standard, 15: standard_france_austria, 23: standard_germany_italy} (`webdiplomacy_net/utils.py:14-15`) |
| Territory ID <-> loc | `TERR_ID_TO_LOC` (classic, 0..81, coasts are 76-81) and `TERR_ID_TO_LOC_FRANCE_VS_AUSTRIA` (coasts interleaved at 9,10,23,24,37,38) via `TERR_ID_TO_LOC_BY_MAP` (`build_dataset.py:41-124`, `148-231`, `237-240`). Key `0` -> `""` | pip `CACHE[1]['locs']`, `CACHE[15]['locs']` (`utils.py:18-40`). I diffed both copies against each other: identical for both maps. Coast -> parent collapse for `fromTerrID` via `_build_coast_id_to_loc_id` (`W:347-353`) |
| Country ID <-> power | `COUNTRY_ID_TO_POWER_OR_ALL_MY_MAP` (`build_dataset.py:247-250`): classic 0=ALL, 1=ENGLAND, 2=FRANCE, 3=ITALY, 4=GERMANY, 5=AUSTRIA, 6=TURKEY, 7=RUSSIA; FvA 0=ALL, 1=FRANCE, 2=AUSTRIA | inverse of the same dict for `toCountryID` (`W:2627`, `W:2650`). Caveat: `PRESS_COUNTRY_ID_TO_POWER` (classic only) is used for game file names, Redis keys and the send path (`W:1093`, `W:2919`), so FvA + dialogue would mislabel powers; harmless today because Dora never talks |
| Unit types | `"Army"`/`"Fleet"` -> first letter; `""` skipped (`W:604-606`, `W:619-621`) | `A`/`F` -> `"Army"`/`"Fleet"` (pip `orders.py:207`) |
| Order types | first letter of `type`, `M` -> `-`, `B` + unit from `"Build Army"` (`W:622-626`); `viaConvoy == "Yes"` | see list in 1.2; convoy paths computed locally from the pip `diplomacy.Map` |
| Turn/phase | `turn_to_phase` (`fairdiplomacy/webdip/utils.py:11-41`): year = `turn // 2 + 1901`; even turn = Spring, odd = Fall; `Builds` -> `W{year}A` | `phase_to_turn` exists (`W:333`) but the posted `turn` is copied from `phases[-1].turn`; `phase` from `webdip_order_phase` |
| Pot type | `W:517-527` and `POT_TYPE_CONVERSION` (`parlai_diplomacy/utils/game2seq/format_helpers/misc.py:35`) | n/a |
| Vote strings | `"Voted for Draw"`, `"Un-voted for Draw"` compared lower-cased (`build_dataset.py:280-286`); the server writes `"Un-Voted for Draw"` | `vote=Draw` |
| Map adjacency, SCs, legal orders | dipcc C++ engine (classic map compiled in) | pip `diplomacy.Map('standard' / 'standard_france_austria')` |

So the new static variant JSON does **not** need to provide territory names, adjacency, country names or start positions for these two variants; it just has to keep
the same integer IDs. If you want the static files to become the source of truth, the two tables to replace are `TERR_ID_TO_LOC_BY_MAP`/`COUNTRY_ID_TO_POWER_OR_ALL_MY_MAP` in
`build_dataset.py` and `CACHE` in the pip package (the latter is inside the docker image, not the repo).

---

## 5. Auth, multiplexing, identity

| Item | Detail |
|---|---|
| Key source | env `DIPGPT_APIKEY` (Cicero) / `FAIRBOT_APIKEY` (Dora) from `docker/.env`, passed on the command line as cfg `api_key=`. `api_key` may be a comma list, paired 1:1 with `account_name` (`W:3707-3709`); one `WebdipBotWrapper` per key. Live: one key each. |
| Header | `Authorization: Bearer <api_key>` on every request (`W:766-767`). No cookies, no other auth. |
| Account | Cicero: `account_name='dipgpt'`, a **multiplexed** key (up to 7 webDip user accounts behind one key, selected server-side by `wD_ApiKeys.multiplexOffset`). Dora: `account_name=FairBot`, plain 1:1 key, `USE_MULTIPLEXING` unset. `account_name` is never sent to webDip; it only names Redis keep-alive / backup-gating keys (`W:920-956`, `W:3187-3205`). |
| Multiplex encoding | Server: `muxID = realGameID * 10 + multiplexOffset + 100000000`; decode: if `gameID > 100000000` then `offset = (id - 1e8) % 10`, `real = (id - 1e8) // 10` (`api.php:228-258`, `288-309`). The bot **never encodes or decodes**: it asks `players/pulse` once per `multiplexOffset` 1..7 (`W:1319-1320`), gets already-multiplexed `gameID`s, and echoes them back. A game with 6 bot countries therefore appears as 6 contexts with 6 different gameIDs. Example from the logs: `game_111941452_GERMANY.log` = real game 1194145, offset 2. The offset is the account slot, not the countryID. |
| countryID per game | taken from the `players/pulse` row (`make_context_from_json`, `W:1338-1344`) and sent back as `countryID` on every game call. Exception: one-shot debug modes use `COUNTRY_POWER_TO_ID[cfg.force_power]` (`W:1399-1401`). |
| `Context` | namedtuple `(gameID, countryID, api_url, api_key)` (`fairdiplomacy/typedefs.py:70`). It keys every per-game cache, the pickled checkpoint, `PLAYER_STATE_DICTS/<key>/<gameID>_<countryID>.pt` (`W:1241-1248`), game files `game_<gameID>_<POWER>.json`, and the Redis proposal key (the game file path). All of these use the multiplexed ID. |
| Secrets hygiene | the `Context` repr (with the key) is logged at INFO on most sweeps; `W:3800` logs the key outright; the checkpoint file is named `<urlhash>__<api_key>.pt` (`W:776-780`). |

---

## 6. Legacy / disabled paths

| Path | Status | Routes/fields only it uses |
|---|---|---|
| `get_missing_orders_json` (`W:1279`) | dead, no caller | `players/missing_orders` (whole route) |
| `game_name` wildcard filter (`W:1346-1362`) | not configured in either launcher | `players/active_games`: `gameID`, `name` |
| `should_stop` interruption check (`W:2207-2283`) | compiled out in live Cicero: `only_bump_msg_reviews_for_same_power=True` and no `recipient` -> `lambda x: False` (`W:2185-2187`). Would be active if that flag were False or in parallel-recipient mode; then it fires roughly 3-8 times per message generation (`raise_if_should_stop` call sites in `searchbot_agent.py:1735,1839,2010,2024,2061`, `parlai_message_handler.py:1360,1395`, `bqre1p_agent.py:1175`) | `game/getmessages` (whole route); `game/pulse`: `turn`, `phase`, `lastMessageTimeSent`, `lastVoteTime` |
| `generate_message_for_approval(status_json=None)` fallback (`W:2346-2350`) | unreachable | `game/pulse.processTime` |
| `draw_on_stalemate_years` (`W:1836-1847`, `W:3410-3419`, `W:3984-3994`) | unset in both launchers and both prototxts | extra `game/pulse` + `game/togglevote` per order submission |
| Draw vote via `<DRAW>`/`<NODRAW>` message (`W:2630-2644`) | **live** (cicero config loads `models/draw_classifier`) but rare | `game/togglevote`, `game/pulse.votes`, `game/status.phases[-1].publicVotesHistory[].countryID/.timeSent` |
| `poll_until_message_appears` (`W:907`) | fallback only when the sent message is missing from the first status | repeated `game/status` |
| Per-visit loop (`W:3785-4036`) and `find_context` (`W:1389`) | live for Dora; Cicero only if `WEBDIP_BATCH_CYCLE_SECONDS=0` or `_batch_cycle_enabled` falls back | |
| Parallel-recipient / SLURM mode (`cfg.recipient`, `W:3639-3655`) | legacy | `game/status.orderStatus` (`W:3877`) |
| `check_phase` / `force` / `json_out` one-shot modes (`W:1394-1402`) | debug | |
| `require_message_approval`, Slack review URL | off | none (Redis only) |
| Bare-int `game/sendmessage` response (`W:2666`) | old server format | |
| Rulebook R/A send-error special case (`W:1820-1831`, `W:3457-3466`, `W:3949-3959`) | effectively dead: the exception raised at `W:2661` has no `.message` attribute and carries the status code, not the body | |
| `self.latest_status_json` (`W:1036`, `W:1973`) | written, never read | |

---

## 7. Variants and game types supported

| Dimension | Support |
|---|---|
| Variants | `variantID` 1 (Classic) and 15 (ClassicFvA, France vs Austria) only. Anything else: `ValueError("Bad variant")` in `webdip_state_to_game` (`W:512`), and the constructor asserts `cfg.variant_id in GameVariant` (`W:1050`). The pip order table also knows 23 (Germany vs Italy) but the bot does not. |
| One variant per process | games whose `variantID != cfg.variant_id` are skipped (`W:1574-1581`, `W:3843-3850`). Live: `cicero` = variant 1 (`play.prototxt`), `dora` = variant 15 (`play_dora_fva.prototxt`, agent `searchbot_neurips21_fva_dora`, `allow_dialogue=false`, `ready_immediately: true`). |
| Press types | `NoPress`: orders only. `RulebookPress`: dialogue in Diplomacy phases only, proposals deleted in R/A, Builds orders marked ready immediately, 5-15 s delay before R/A orders in 5-minute games. Everything else (`Regular`, and also `PublicPressOnly`) is treated as full private press; there is no `PublicPressOnly` handling, so the server would reject its private messages. |
| Pot types | `Unranked`, `Sum-of-squares`, `Points-per-supply-center` (approximated as SOS), `Winner-takes-all`. Unknown -> exception. |
| Draw votes | bot assumes public draw votes (models hard-code `PUBLIC`). Draw state is rebuilt only from `publicVotesHistory`, which the server only emits for `drawType == "draw-votes-public"`. |
| Anonymity | not consumed (`ANON` hard-coded). |
| Phase length | any; `phaseLengthInMinutes == 5` switches on the live-game heuristics. |
| Game membership | whatever `players/pulse` returns for the account(s): member `Playing`, phase in Diplomacy/Retreats/Builds, variant in the server's `apiConfig.variantIDs`. Pre-game and finished games are never seen. Paused games appear with `processTime` null. |

---

## 8. Surprises worth knowing before redesigning

### 8.1 Type differences between routes
`game/status` passes `processTime` and `phaseLengthInMinutes` through as MySQL strings (`api/responses/game_state.php:289-290`; the repo fixture
`unit_tests/data/status_json_with_post_game_messages.json` shows `"processTime": "1651957501"`, `"phaseLengthInMinutes": "1440"`), whereas both pulse routes `intval` them.
The bot copes in most places (`int(... or 0)` at `W:2507`, `str(...)` at `W:531`) but not at `W:2040` -> `ProposalSend.sends_before` -> `Timestamp.from_seconds(process_time)`
(`fairdiplomacy/timestamp.py:29`: `int(x * 100)`). With a string that is a 1000-digit number (I ran `Timestamp.from_seconds("1651957501")` in isolation to confirm), so
"sends before the deadline" is always true and orders are never marked ready while a proposal with a recipient exists, even one scheduled after the deadline. The end-to-end
effect was found by reading, not reproduced against the live server. The log summary line uses the pulse row (int) and is unaffected.

### 8.2 Dora never idles
In `find_context`, FvA bot games are not `NoPress`, so they go down the press branch; with `allow_dialogue=false` no proposal is ever stored, so
`check_for_actionable_message_state` returns True ("No message review", `W:2564-2567`) for the first game every time, the main loop does nothing with it, sets
`last_cycle_processed = True` and skips the 5 s sleep. Result: one `players/pulse` request about every 1.2 s around the clock (plus Redis reads), with 16 active games today.

### 8.3 Fields fetched and discarded
`centers` for every phase (34 entries x every phase), every unit's `countryID`/`retreating`, `success`/`dislodged` on every order, `orderStatuses`, `publicVotes`, `standoffs`,
`occupiedFrom`, all GameMaster messages, and the whole `members[]` array of `game/pulse`. In `players/pulse`: `potType`, `drawType`, `phaseLengthInMinutes`, `votes`, `status`.

### 8.4 Fragile spots (by reading)
- `send_message` draw path reads `phases[-1]["publicVotesHistory"]` (`W:2639-2643`); the server attaches vote history to the turn's `Diplomacy` entry, so during Retreats/Builds, or in hidden-draw games, that key is absent on the last phase.
- `assert len(resp_msgs) == 1` (`W:2669`) vs the server's muted-recipient `{"messages": []}`.
- The comment at `W:1044-1045` says our own orders change the pulse row; they change `orderStatus`, which is not part of `pulse_change_key`. Harmless today because nothing order-related is read from the cached status.
- Exact-bytes matching of two HTML/text error bodies (`W:304-305`) is how "game gone" is detected.

---

## 9. Minimum data contract implied for static JSON + `game/playercontext`

What the bot must be able to obtain, in today's terms:

| Need | Today | Fields |
|---|---|---|
| List my (game, country) pairs + cheap change token | `players/pulse` | `gameID` (stable per account slot), `countryID`, `variantID`, `pressType`, `orderStatus`, `turn`, `phase`, `gameOver`, `processTime`, `lastMessageTimeSent`, `lastVoteTime` |
| Game constants | `game/status` top level | `variantID`, `potType`, `pressType`, `phaseLengthInMinutes` |
| Live game header | `game/status` top level | `turn`, `phase`, `gameOver`, `processTime` |
| Public history (static-file friendly, identical for all countries) | `game/status.phases[]` | per phase: `turn`, `phase`, `orders[]` {`countryID`, `terrID`, `unitType`, `type`, `toTerrID`, `fromTerrID`, `viaConvoy`} for ALL countries, `units[]` {`terrID`, `unitType`}; public draw-vote log {`vote`, `countryID`, `timeSent`, `phaseMarker`}; public broadcasts (`toCountryID == 0`) |
| Private, per country (the natural `game/playercontext` payload) | `game/status.phases[].messages[]`, `votes`, `orderStatus` | messages {`message`, `fromCountryID`, `toCountryID`, `timeSent`, `phaseMarker`, plus the `turn` they belong to}, own `votes`, own `orderStatus`, `lastMessageTimeSent` |
| Writes (unchanged) | `game/orders`, `game/sendmessage`, `game/togglevote` | as in 1.1 / 1.2 |

Bot-side work the switch would need regardless: a loader that merges static phase files + the private context into the structure `webdip_state_to_game` expects
(or a rewrite of it), int/0 conventions preserved (section 2.2), one agreed type for `processTime`/`phaseLengthInMinutes`, and either the same multiplexed IDs or a one-off
migration of Redis keys / checkpoints / game file names.
