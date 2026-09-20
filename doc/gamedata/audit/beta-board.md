# Beta board (beta-src) API consumption audit

Repo: `~/Desktop2/webdiplomacy` at commit `d0513c40` (clean tree). Read-only audit; nothing was modified.
Method: static reading of every file that touches the network or the API-shaped Redux state, plus a word-boundary grep of every declared response field across `beta-src/src` (excluding the declarations themselves, `state/game/initial-state.ts`, and `models/testData.ts` / `models/mockAPI.ts`, which nothing imports at runtime). Server shapes were cross-checked against `api.php`, `api/responses/*.php`, `ajax.php`, `board/orders/*.php`, `sse-server/server.js`. Nothing here was exercised against a running server; behaviours described for spectators etc. are derived from the code.

Unless stated otherwise, client paths are relative to `beta-src/src/`.

---

## 0. Headline findings

1. The board calls **13 api.php routes + `ajax.php` (order save) + the SSE server `/events`**. There is **no API polling**; all refetching is driven by SSE events, by POST failures, or by the overview response changing phase/processTime.
2. **`loadGame` never loads the game data itself.** `state/game/game-api-slice.ts:286` does `await loadGameData(gameID, countryID)` without `dispatch`, so the thunk is created and discarded. The data/status/active_games fetch actually happens because `fetchGameOverviewFulfilled` sees the phase key change from `0.Loading` and sets `needsGameData` (`utils/state/gameApiSlice/extraReducers/fetchGameOverview/fulfilled.ts:24-29`), which `WDMainController.tsx:83-94` acts on **during render**.
3. Large parts of every response are never read: the whole `overview.variant` object, 7 other overview scalars, 5 of 16 member fields, most of the top level of `game/status`, `phaseMarker` on messages, 7 of 12 `players/active_games` fields. Details in section 2.
4. **Adjacency is not bundled.** Legal-order computation depends entirely on `game/data` `territories[].Borders` / `CoastalBorders`. The map geometry, SC marker positions, coast slots, abbreviations and country colours are bundled. The two worlds are joined by **territory name string** (`webdipNameToTerritory[territories[id].name]`), not by ID.
5. The board depends on **per-endpoint value types**: `game/overview` is encoded with `JSON_NUMERIC_CHECK` (numbers), `game/data` is raw DB rows (strings), `game/status` is `intval`'d (numbers). Comparisons in the client are written around that (section 2.8). Static JSON replacements must reproduce those types exactly.
6. Order saving reads its result from the **`X-JSON` response header** of `ajax.php`, not the body, and needs `contextVars.context` + `contextKey` (an HMAC of the context JSON) from `game/data`. These are the only member-private, per-phase secrets the board needs besides the SSE token.
7. Spectators: `game/getmessages` is rejected with 403 for non-members (even for the global channel), and the SSE connection can never authenticate (token is issued for `private-gameN`, the SSE server validates against `private-gameN-countryNaN`), so a spectator tab re-POSTs `sse/authentication` and retries `/events` roughly every 17 s indefinitely and never gets live updates.

---

## 1. Every route called

Client plumbing: `utils/api/index.ts`. GET = `../api.php?route=<route>&<query>` (`:26-33`; params with falsy values are dropped by `buildQueryString` `:8-17`, so `countryID: undefined` is omitted, but the string `"0"` is kept). POST = `../api.php?route=<route>` with an axios JSON body (`:35-42`). Orders = POST `../ajax.php[?ready=on|?notready=on]` with a `FormData` body (`:44-62`). URLs are relative to `/beta/`.

| # | Route | Verb | Called from (thunk → call site) | When | Params |
|---|---|---|---|---|---|
| 1 | `game/overview` | GET | `fetchGameOverview` slice`:58-68` ← `loadGame` slice`:273-278` ← `App.tsx:14-16`; `WDMainController.tsx:36-45` (SSE handler, guarded by `outstandingOverviewRequests`); `WDMainController.tsx:79-82` (`needsGameOverview`) | Startup; SSE `set-vote`, `processed`, `catchup` after a reconnect, `resync`, 5 s no-catchup fallback after reconnect; after any failed POST | `gameID` |
| 2 | `game/data` | GET | `fetchGameData` slice`:48-56` ← `loadGameData` slice`:264-271` ← `WDMainController.tsx:83-94` | Whenever `needsGameData` is set: overview's `turn.phase` or `processTime` changed (incl. first load), or any failed POST. Skipped while phase is `Pre-game`/`Error` | `gameID`, `countryID` (members only; omitted for spectators) |
| 3 | `game/status` | GET | `fetchGameStatus` slice`:70-78` ← `loadGameData` | Same trigger as #2 (always fired together) | `gameID`, `countryID` (members only) |
| 4 | `players/active_games` | GET | `fetchPlayerActiveGames` slice`:101-107` ← `loadGameData` | Same trigger as #2 (always fired together) | none |
| 5 | `game/getmessages` | GET (60 s timeout) | `fetchGameMessages` slice`:80-99` ← `OpenModalButton.tsx:70-82` | Once when `gameID` becomes non-zero (`:85-87`); on every SSE `message` event (`:93-98`); on `resync` / reconnect fallback (`sselistener.ts:64-72`). Guarded by `outstandingMessageRequests`; skipped in `Pre-game` | `gameID`, `countryID` (members only), `sinceTime` = last response's `time` (first call sends `sinceTime=0`) |
| 6 | `sse/authentication` | POST | `lib/sselistener.ts:110-123` | Only if `overview.user.sseAuth` is absent, older than 23 h, or was refused (connection failed before opening) | body `{channel_name: "private-game<id>[-country<c>]", gameID}` |
| 7 | `game/sendmessage` | POST | `sendMessage` slice`:109-131` ← `WDPress.tsx:55-72` | User presses send / Enter | `{gameID, countryID, toCountryID, message}` |
| 8 | `game/messagesseen` | POST | `markMessagesSeen` slice`:182-195` ← `WDPress.tsx:74-87` | Clicking a country tab (`:111-114`) **and any click anywhere inside the press panel** (`:143`) | `{countryID, gameID, seenCountryID}` |
| 9 | `game/setvote` | POST | `setVoteStatus` slice`:133-147` ← `WDControl.tsx:45-55` | Vote button toggled | `{countryID, gameID, vote: "Draw"|"Pause"|"Cancel", voteOn: "Yes"|"No"}` |
| 10 | `game/markbackfromleft` | POST | `markBackFromLeft` slice`:197-206` ← `WDMainController.tsx:121-127` | "Rejoin Game" button on the overlay when `status.status === "Left"` | `{countryID, gameID}` |
| 11 | `sandbox/copy` | GET | slice`:149-158` ← `WDInfoDisplay.tsx:83-89` | "Sandbox: Create" button (shown for every game) | `copyGameID` |
| 12 | `sandbox/moveTurnBack` | GET | slice`:160-169` ← `WDInfoDisplay.tsx:90-94` | "Move back" button (only if `alternatives` contains `"Sandbox"`) | `gameID` |
| 13 | `sandbox/delete` | GET | slice`:171-180` ← `WDInfoDisplay.tsx:95-100` | "Delete" button (same condition) | `gameID` |
| 14 | `ajax.php` (not api.php) | POST | `saveOrders` slice`:208-262` ← `WDOrderStatusControls.tsx:108-159`; auto-save effect `:161-168` | Save / Ready / Unready buttons; automatically on every committed order when the `autoSave` setting is on (default on) | form fields `orderUpdates`, `context`, `contextKey`; query `ready=on` or `notready=on` |
| 15 | `/events` (SSE server, not PHP) | GET EventSource | `lib/sselistener.ts:130-134` | After the first `subscribe` with a country channel; re-opened by a 17 s watchdog if nothing (incl. the 13 s server ping) arrived for 30 s | `channelList=private-game<id>,private-game<id>-country<c>`, `auth`, `turn`, `phase`, `since` |

Routes declared in `enums/ApiRoute.ts` and all used: the 13 above. No other route strings exist in the source. `hooks/useInterval.ts` exists but has no callers.

SSE event → action mapping (`lib/sselistener.ts:196-249`): `message` → #5; `set-vote` / `processed` → #1; `catchup` → #1 only on reconnections; `resync` → #1 and #5; `ping` → resets the 30 s watchdog only. A `processed` event therefore costs 4 GETs (overview, then data + status + active_games); a `set-vote` costs 1.

---

## 2. Response interfaces and field-level usage

Legend: **read** = at least one runtime read (one citation given); **unused** = declared but never read; **opaque** = stored and passed back to the server without the client looking inside.

Envelope handling differs per route:

| Route | Server envelope | What the thunk keeps |
|---|---|---|
| `game/overview` | `{msg, success, referenceCode, data}` (`api.php:188-195`), `JSON_NUMERIC_CHECK` on | `data.data` only (slice`:61-66`) |
| `game/data` | same envelope, no numeric check | the **whole envelope** is stored as `state.data` (slice`:51-54`); `msg`, `referenceCode`, `success` are never read (`success` is not checked anywhere) |
| `game/getmessages` | same envelope | `data.data` (slice`:87-97`) |
| `game/status` | none: `json_encode(GameState)` | whole body |
| `players/active_games` | none: `{games: [...]}` | `games` |
| `game/sendmessage` | none: `{messages: [...]}` | whole body |
| `game/setvote` | none: bare comma-separated string | whole body (string) |
| `sse/authentication` | envelope | `response.data.data.auth` |

### 2.1 `game/overview` → `GameOverviewResponse` (`state/interfaces/GameOverviewResponse.ts`)

Server: `api.php:1008-1080` (+ `GetGameMembers::getData` `:917-990`).

| Field | Status | Read at (one example) / note |
|---|---|---|
| `alternatives` | read | `OpenModalButton.tsx:141` (display); `WDInfoDisplay.tsx:79` (`includes("Sandbox")` gates the sandbox buttons) |
| `anon` | read | `OpenModalButton.tsx:142` (display text only) |
| `drawType` | **unused** | |
| `excusedMissedTurns` | read | `OpenModalButton.tsx:167` → `WDCountryTable.tsx:118` (`x/maxDelays`) |
| `gameID` | read | `WDMainController.tsx:41,50`; all request params |
| `gameOver` | **unused** | finished-ness is judged from `phase === "Finished"` |
| `isTempBanned` | read | `WDGameProgressOverlay.tsx:65,76` |
| `members[]` | read | see 2.2 |
| `minimumBet` | **unused** | |
| `name` | read | `WDMainController.tsx:96-98` (document.title); `OpenModalButton.tsx:174` |
| `pauseTimeRemaining` | read | `WDPhaseUI.tsx:73-75` |
| `phase` | read | everywhere; `getPhaseKey` (`utils/state/getPhaseKey.ts:18`) |
| `phaseMinutes` | read | `WDPhaseUI.tsx:49`; `WDInfoDisplay.tsx:65` |
| `phaseMinutesRB` | read | `WDPhaseUI.tsx:11-12`; `WDInfoDisplay.tsx:66` |
| `playerTypes` | read | analytics event name only: slice`:127,258` → `utils/analytics.ts:20` |
| `pot` | read | `OpenModalButton.tsx:172` → `WDInfoDisplay.tsx:136` |
| `potType` | **unused** | |
| `pressType` | read | `WDPress.tsx:132-137` (can-message rule); `OpenModalButton.tsx:145,180` |
| `processStatus` | read | `WDPhaseUI.tsx:83`; `OpenModalButton.tsx:178` (`=== "Paused"`) |
| `processTime` | read | `WDPhaseUI.tsx:73-75`; change detection `fetchGameOverview/fulfilled.ts:26` |
| `season` | read | `WDUI.tsx:85`; `WDGameProgressOverlay.tsx:95` |
| `startTime` | **unused** | |
| `turn` | read | `getPhaseKey`; `WDMain.tsx:147`; `sselistener.ts:55` |
| `user.member` | read | see 2.2. Presence of `user` is the member-vs-spectator switch |
| `user.sseAuth` | read | `lib/sselistener.ts:43` |
| `variant` (whole object: `id, mapID, name, fullName, description, author, countries, variantClasses.{drawMap,adjudicatorPreGame}, codeVersion, cacheVersion, coastParentIDByChildID, coastChildIDsByParentID, terrIDByName, supplyCenterCount, supplyCenterTarget`) | **unused, every sub-field** | The server serialises the PHP `WDVariant` object (`api.php:1071`); the only references are the declaration and `initial-state.ts:97-127` |
| `variantID` | **unused** | the variant is hard-coded to Classic throughout (e.g. `WDMessage.tsx:74` `turnAsDate(turn, "Classic")`) |
| `year` | read | `WDUI.tsx:85`; `WDGameProgressOverlay.tsx:96` |

### 2.2 `MemberData` (`interfaces/state/MemberData.ts`), used for `members[]` and `user.member`

| Field | Status | Read at / note |
|---|---|---|
| `bet` | read (dynamic) | `WDCountryTable.tsx:47,121` and `WDGameFinishedOverlay.tsx:31,88` via `country[column.id]` |
| `country` | read | `WDUI.tsx:58-63` (name → enum, colour, abbr); `WDProvince.tsx:42-46` |
| `countryID` | read | most-read field (52 access sites), e.g. `fetchGameData/fulfilled.ts:46` |
| `excusedMissedTurns` | read (dynamic) | `WDCountryTable.tsx:53,118` |
| `missedPhases` | **unused** | |
| `newMessagesFrom` | **unused** | the unread list the UI uses comes from `game/getmessages` instead |
| `online` | **unused** | |
| `orderStatus.{Ready,Saved,Completed,None,Hidden}` | read | `processMapClick.ts:114` (Ready blocks input); `WDOrderStatusControls.tsx:88,93`; `WDOrderStatusIcon.tsx:20-21`; `WDPillScroller.tsx:93,118`; `WDCountryTable.tsx:108` (Hidden). Note: for anonymous games the server sends only `{Hidden: 1}` for other members, and never sends `Hidden` otherwise (`api.php:950-955`) |
| `pointsWon` | read (dynamic) | `WDGameFinishedOverlay.tsx:37,85` |
| `status` | read | `WDCountryTable.tsx:104`; `WDInfoPanel.tsx:25` (`=== "Left"`); `WDGameFinishedOverlay.tsx:25` (dynamic column) |
| `supplyCenterNo` | read | `precomputeLegalOrders.ts:175`; `BottomMiddle.tsx:103` |
| `timeLoggedIn` | **unused** | |
| `unitNo` | read | `precomputeLegalOrders.ts:175`; `WDCountryTable.tsx:39` |
| `userID` | **unused inside beta-src**; read externally | `user.member.userID` is read by `javascript/adsense.js:42-47` through `window.wDAds.shouldShowAds(user)` (`utils/adPlacement.ts:68-79`, called from `hooks/useAdPlacement.ts:29`, `hooks/useViewport.ts:15`). `members[].userID` is unused |
| `username` | read | `WDCountryTable.tsx:135`; `WDGameFinishedOverlay.tsx:100` |
| `votes` | read | `WDCountryTable.tsx:143-151`; `WDControl.tsx:46`. Overwritten locally from the `setvote` response (slice`:417-424`) |

The exported `Member` type in `GameOverviewResponse.ts:4-10` (`country, countryID, id, online, userID`) is not imported anywhere.

### 2.3 `game/data` → `GameDataResponse` / `GameData` (`state/interfaces/GameDataResponse.ts`, `models/Interfaces.ts`)

Server: `api.php:1087-1256`; territories from `InstallCache::terrJSONData` (`variants/install.php:43-82`, Redis-cached 1 day under `territories_<variantID>`; note it is called with `variantID` where the function takes a `mapID`); units / statuses from `board/orders/jsonBoardData.php:32-60`.

| Field | Status | Read at / note |
|---|---|---|
| `msg`, `referenceCode`, `success` | **unused** | |
| `data.turn`, `data.phase` | read | `fetchGameData/fulfilled.ts:26-34` (phase change clears `ordersMeta`); `WDMainController.tsx:34` |
| `data.isSandboxMode` | read | `fetchGameData/fulfilled.ts:47`; `precomputeLegalOrders.ts:114,174,182,224,339,478`; `processMapClick.ts:181`; `WDBoardMap.tsx:134,148`. Initial state is `true` (`initial-state.ts:16`) |
| `data.contextVars.context` | **opaque** | presence test `getOrdersMeta.ts:17`; re-serialised and posted `WDOrderStatusControls.tsx:141`. None of `countryID, gameID, memberID, maxOrderID, orderStatus, phase, tokenExpireTime, turn, userID, variantID` is read from the `game/data` copy. The server also puts `isSandboxMode` in the context (`api.php:1107`, `orderinterface.php:282`); it is not declared in `IContext` |
| `data.contextVars.contextKey` | **opaque** | posted back `WDOrderStatusControls.tsx:142` |
| `data.currentOrders[]` | read | see below. Members only; absent for spectators. In sandbox mode contains every country's orders |
| `data.territories{}` | read | see below |
| `data.units{}` | read | see below |
| `data.territoryStatuses[]` | read | see below |

`currentOrders[]` (`IOrderData`):

| Field | Status | Read at |
|---|---|---|
| `id` | read | `generateMaps.ts:41-43`; `getOrdersMeta.ts:19,32` |
| `unitID` | read | `generateMaps.ts:42`; `processMapClick.ts:138` |
| `type` | read | `getOrdersMeta.ts:23,38` |
| `toTerrID`, `fromTerrID`, `viaConvoy` | read | `getOrdersMeta.ts:32-41`; `WDMain.tsx:80-87` |
| `countryID` | read | `WDBuildContainer.tsx:52`; `getUnits.ts:118`; `processMapClick.ts:219` (always through `Number()`) |
| `convoyPath`, `error`, `fixed`, `saved`, `status` | **unused** (as server fields) | `saved` / `convoyPath` exist only on the client-side `ordersMeta` |

`territories{}` keyed by territory ID (`ITerritory`):

| Field | Status | Read at |
|---|---|---|
| `id` | read | `generateMaps.ts:21-25` |
| `name` | read | **the join key to bundled map data**: `generateMaps.ts:22`; `getUnits.ts:139`; `WDArrowContainer.tsx:47`; `precomputeLegalOrders.ts:79` |
| `type` | read | `precomputeLegalOrders.ts:292,300,341,371` (`=== "Sea"`) |
| `supply` | read | `precomputeLegalOrders.ts:189` (`!== "Yes"`) |
| `countryID` (home country) | read | `precomputeLegalOrders.ts:190`; `getUnits.ts:234`; via `territoriesMeta` at `processMapClick.ts:186,210` |
| `coastParentID` | read | `generateMaps.ts:25`; `WDArrowContainer.tsx:74-75,179-182,279`; `getTerritoriesMeta.ts:55` |
| `coast` | **effectively unused** | only copied into `territoriesMeta` (`getTerritoriesMeta.ts:39`), which nobody reads it from |
| `Borders[].{id,a,f}` | read | `precomputeLegalOrders.ts:286-288,362-364,501-504` (province-level: convoys, supports) |
| `CoastalBorders[].{id,a,f}` | read | `precomputeLegalOrders.ts:75-77,130-132` (territory-level: moves, retreats) |
| `smallMapX`, `smallMapY` | sent by server, **not declared, unused** | `variants/install.php:48` |

`units{}` keyed by unit ID (`IUnit`): `id`, `countryID`, `type`, `terrID` are all read (`fetchGameData/fulfilled.ts:44-50`, `generateMaps.ts:29-39`, `precomputeLegalOrders.ts:73-74`).

`territoryStatuses[]` (`IProvinceStatus`; province-level and sparse, see the comment at `GameDataResponse.ts:21-26`): `id` (`WDMain.tsx:182`), `ownerCountryID` (`WDMain.tsx:183`; `precomputeLegalOrders.ts:181`), `standoff` (`WDMain.tsx:315`; `precomputeLegalOrders.ts:145`), `unitID` (`getUnits.ts:152-153`; `precomputeLegalOrders.ts:120,301`), `occupiedFromTerrID` (`precomputeLegalOrders.ts:127-128`) are all read.

Derived `territoriesMeta` (`getTerritoriesMeta.ts`) is computed on every `game/data` response, but only its key existence and `.countryID` are ever read (`processMapClick.ts:121-124,186,210`); `coast, coastParentID, id, ownerCountryID, standoff, supply, type, unitID, coastChildIDs` on it are dead. `WDBoardMap.tsx:37-41` also builds a `provinceStatusByProvID` map that is never used.

### 2.4 `game/status` → `GameStatusResponse` (`state/interfaces/GameStatusResponse.ts`)

Server: `api/responses/game_state.php`.

| Field | Status | Read at / note |
|---|---|---|
| `gameID`, `variantID`, `potType`, `gameOver`, `pressType` | **unused** | |
| `turn`, `phase` | **effectively unused** | only feed `statusKey` (`WDMainController.tsx:33`) → `consistentPhase` (`:76-77`), which is itself never used |
| `countryID` | read | `WDMain.tsx:140` (country attributed to the live, unsaved orders in the orders panel; `null` for spectators; in sandbox mode every country's live orders get the viewer's countryID) |
| `status` | read | `WDMain.tsx:40` (`=== "Playing"` gates live mode); `WDMainController.tsx:101,121`; `WDGameProgressOverlay.tsx:57`; `WDPillScroller.tsx:82`. Empty string for spectators. Same value as `overview.user.member.status` |
| `orderStatus` | **unused** | the overview copy is used |
| `standoffs` | **unused** | declared `string[]`, server sends `{terrID, countryID}[]` |
| `occupiedFrom` | **unused** | declared `string[]`, server sends a map |
| `phases[]` | read | see below |
| `drawType`, `processTime`, `phaseLengthInMinutes`, `publicVotes`, `orderStatuses`, `votes` | sent by server, **not declared, unused** | `game_state.php:222-262` (`votes` is commented out in the TS interface with the note that status only refreshes per phase) |

`phases[]` (`IPhaseDataHistorical`):

| Field | Status | Read at |
|---|---|---|
| `turn`, `phase` | read | `WDMain.tsx:46` (`getPhaseKey`); `getPhaseSeasonYear.ts:41-42`; `WDBuildCounts.tsx:25` |
| `units[].{unitType, retreating, terrID, countryID}` | all read | `getUnits.ts:305-311,329,343` |
| `centers[].{countryID, terrID}` | all read | `WDMain.tsx:222-226` |
| `orders[].countryID` | read | `WDOrdersPanel.tsx:38-41`; `getUnits.ts:377` |
| `orders[].terrID`, `.toTerrID`, `.fromTerrID` | read | `WDArrowContainer.tsx:47-48`; `WDOrdersPanel.tsx:55-60` |
| `orders[].type` | read | `WDArrowContainer.tsx:36`; `WDMain.tsx:235,267` |
| `orders[].success` | read | `getUnits.ts:85,290`; `WDArrowContainer.tsx:53` |
| `orders[].unitType` | read | `WDOrdersPanel.tsx:42`; `WDPillScroller.tsx:72` |
| `orders[].viaConvoy` | read | `WDOrdersPanel.tsx:69`; `WDArrowContainer.tsx:64` (empty TODO branch) |
| `orders[].dislodged`, `orders[].phase`, `orders[].turn` | **unused** | the only `dislodged:` in code is the literal written at `WDMain.tsx:141` |

`phases.length` also drives the phase selector and the "viewing current phase" checks (`WDOrderStatusControls.tsx:43-44`, `processMapClick.ts:106`). On first load the viewed index jumps to the last phase (`fetchGameStatus/fulfilled.ts:19-22`).

### 2.5 `game/getmessages` → `GameMessages` (`state/interfaces/GameMessages.ts`)

Server: `api.php:1692-1772`.

| Field | Status | Read at / note |
|---|---|---|
| `messages[].fromCountryID`, `.toCountryID` | read | `WDMessageList.tsx:27-32`; slice`:477-478` |
| `messages[].message` | read | `WDMessage.tsx:65` (DOMPurify, only `br`/`strong` allowed) |
| `messages[].timeSent` | read | `WDMessage.tsx:42`; merge/dedupe key `mergeMessageArrays.ts:11` |
| `messages[].turn` | read | `WDMessageList.tsx:35-50`; `WDMessage.tsx:73-74` |
| `messages[].phaseMarker` | **unused** | |
| `messages[].status` | client-only | set in the reducer slice`:472-484`, not sent by the server |
| `newMessagesFrom` | read | slice`:470-505`; `WDPress.tsx:116` |
| `time` | read | slice`:507-510`; becomes the next `sinceTime` and the SSE `since` param (`sselistener.ts:54`) |
| `countryIDSelected` | client-only | UI state kept in the same slice object |

When the server's Redis shortcut fires ("no new messages", `api.php:1706-1720`) the payload is only `{messages: []}`; the reducer tolerates the missing `time` / `newMessagesFrom`.

### 2.6 `players/active_games` → `PlayerActiveGames` (`state/interfaces/PlayerActiveGames.ts`)

Server: `api/responses/active_games.php:82-93`. Consumer: `WDGamesList.tsx` only (the "Games" tab of the modal).

| Field | Status | Read at |
|---|---|---|
| `gameID` | read | `WDGamesList.tsx:24,83` |
| `name` | read | `:26` |
| `turn`, `phase` | read | `:30` |
| `processTime` | read | `:38` |
| `countryID`, `orderStatus`, `pressType`, `newMessagesFrom`, `unitNo`, `phaseMinutes` | **unused** | (`getOrderStates`/`WDOrderStatusIcon` are imported in `WDGamesList.tsx` but not used) |
| `alternatives` | **declared, unused, and not sent** by the server | |
| `variantID` | sent by server, **not declared, unused** | |

### 2.7 Fields read without a declared type

| Access | Where | Note |
|---|---|---|
| `response.data.data.auth` | `lib/sselistener.ts:119-122` | `sse/authentication`, untyped |
| `res.payload.gameID` | `WDInfoDisplay.tsx:86` | `sandbox/copy`, untyped |
| `action.payload.split(",")` | slice`:418` | `game/setvote` body treated as a string |
| `response.headers["x-json"]` | slice`:235-249` | `ajax.php` result, header not body |
| `country[column.id]` | `WDCountryTable.tsx:118,121`; `WDGameFinishedOverlay.tsx:85,88` | dynamic column access: `bet`, `excusedMissedTurns`, `unitNo`, `supplyCenterNo`, `status`, `pointsWon` |
| `border[borderKind]` | `precomputeLegalOrders.ts:77,132,504` | dynamic `"a"` / `"f"` |
| `game.*` typed `any` | `WDGamesList.tsx:14-16` | active-games rows |
| `user.userID` / `user.member.userID` | `javascript/adsense.js:42-47` | outside the bundle, receives `overview.user` |

### 2.8 Value types the client relies on

| Source | Type as sent | Client code that depends on it |
|---|---|---|
| overview `members[].countryID`, `user.member.countryID` | number (`JSON_NUMERIC_CHECK`) | `m.countryID === Number(ownerCountryID)` `WDProvince.tsx:43`; `member.countryID === state.overview.user?.member.countryID` slice`:421`; `.toString()` before comparing with unit strings `fetchGameData/fulfilled.ts:46` |
| overview (all) | any numeric-looking string becomes a number, including `name` and `username` | display only; no string methods are called on those two |
| data `units[].countryID`, `.terrID`, `.id` | string | `unit.countryID === ourCountryID` `precomputeLegalOrders.ts:114`; `member.countryID.toString() === unit.countryID` `getUnits.ts:142` |
| data `territories[].countryID`, `.supply` | string `"0"`…, `"Yes"/"No"` | `getTerritoriesMeta.ts:28`; `precomputeLegalOrders.ts:189-191` |
| data `territoryStatuses[].ownerCountryID`, `.unitID`, `.occupiedFromTerrID` | string or `null`; `.standoff` boolean | `precomputeLegalOrders.ts:120,145,181,192` |
| data `currentOrders[].id`, `.unitID`, `.toTerrID`, `.fromTerrID` | string / `null` | used as object keys and posted back unchanged |
| status `phases[].orders/units/centers` IDs | number (`intval`) | `order.terrID === Number(terrID)` `WDPillScroller.tsx:52`; `.toString()` in `getUnits.ts:308-310`, `WDMain.tsx:224` |
| messages `fromCountryID`, `toCountryID`, `timeSent`, `turn` | number | `cand.countryID === countryID` `WDMessage.tsx:34`; `newMessagesFrom.includes(m.fromCountryID)` slice`:477`. The `sendmessage` response sends `turn` uncast (string) |

---

## 3. Data from anywhere other than api.php

| Source | What | Where |
|---|---|---|
| URL query | `?gameID=` is the only input to the page | `App.tsx:11-16`. Other games are opened by navigating to `?gameID=<n>` (`WDGamesList.tsx:24`) |
| Hosting page | `/beta/` is a **static CRA build** (`package.json` `"homepage": "beta"`, build moves output to `../beta`). `public/index.html` has no PHP-injected values and defines no game globals. `board.php:90-99` merely 302-redirects to `beta/?gameID=<id>` when `Game::usePointAndClickUI()` is true (Classic games only, `objects/game.php:567-575`), and not when an `sbToken` sandbox share token is present |
| `window.gtag`, `window.dataLayer` | defined in `public/index.html:30-61`; GA IDs are baked in at build time (`REACT_APP_GA_ID_MAIN/PLAY`); also `ReactGA.initialize("G-MC45SZ2JEC")` in `index.tsx:11-12` | `utils/analytics.ts:20` |
| `window.wDAds`, `window.adsbygoogle` | from `../javascript/adsense.js` (loaded by `public/index.html:65`), outside the bundle | `utils/adPlacement.ts:68-79`; `components/ui/WDAds.tsx:42-77` |
| Cookies | `wD-Key` is read only by the inline GA script (`public/index.html:50`) and by `adsense.js`; the React code never touches `document.cookie`. API auth rides on the browser sending the session cookies with same-origin axios requests |
| `localStorage` | `wD-userType` (read by the inline GA script, written by PHP pages); `messageStack` (unsent drafts keyed by countryID only, so shared across games; `WDPress.tsx:30`); `settings` (`{autoSave}`; `WDControl.tsx:36`, `hooks/useSettings.tsx`) |
| Static fetches | none. No runtime request to `variants/`, `cache/`, `map.php`, mapstore or any JSON/SVG file. All map geometry, textures, icons and help GIFs are webpack imports under `src/assets` and `src/data` |
| SSE server | `/events` on the same origin | section 1 row 15 |
| Outbound links (navigation, not data) | `/board.php?gameID=…&view=dropDown[&viewArchive=Orders|Maps|Messages][#enterBar]`, `/modforum.php?fromGameID=`, `…&lodgeSuspicion=on`, `intro.php`, `faq.php`, `rules.php`, `points.php`, `contrib/phpBB3/`, `/` | `WDInfoDisplay.tsx:72-77`; `WDHelp.tsx:93-94,167-235`; `TopRight.tsx:9` |

---

## 4. Authentication, identity, and viewer modes

**API auth.** Session only. `api.php:2028-2038` picks `ApiSession` when the PHP session has a logged-in `$User` (`type['User']`), else `ApiKey`; the board sends no key, so a logged-out visitor gets `401 No API key provided` on everything. `ApiSession` grants only `getStateOfAllGames` (`api.php:1964-1972`), which is what lets any logged-in user call `game/overview`, `game/data`, `game/status`, `game/getmessages`, `sse/authentication` for games they are not in. Routes with an empty permission field and a `gameID` arg (`setvote`, `sendmessage`, `messagesseen`, `markbackfromleft`, `sandbox/moveTurnBack`, `sandbox/delete`) require membership (`api.php:1843-1874`).

**Order-save auth.** `ajax.php:122-128` uses the session plus `contextKey`, an `md5+sha1` HMAC of the context JSON with `Config::$jsonSecret` (`orderinterface.php:283-301`). The server rebuilds the JSON from the posted object in the posted key order (`newJSON` `:66-73`), so the client must return the context object with the same keys, order and value types it was given. The client does exactly `JSON.stringify(contextVars.context)`.

**SSE auth.** Token `md5(channel + sseSecret + ts + "generateToken")_ts` for the channel `private-game<id>-country<c>`, valid 24 h (`lib/auth.php:49-58`, `sse-server/server.js:35-67`). Members receive it as `overview.user.sseAuth`; `sse/authentication` is the fallback.

**How the board learns who the viewer is.** Solely from `game/overview`: `user` present ⇒ member, and `user.member.countryID` is the viewer's country (`WDMainController.tsx:30`). That value is then sent as `countryID` to `game/data`, `game/status`, `game/getmessages` and all POSTs; the server re-checks it against the session on each (`api.php:795-796,1198-1207,1742`). There is no separate "who am I" call and no user ID in the page.

| Viewer / game state | Behaviour |
|---|---|
| Member, active game | `isPlayingGame` (`WDMain.tsx:38-41`) needs `phase !== "Finished"`, `status.status === "Playing"` and `overview.user`. Then the latest phase is drawn from `game/data` (`units`, `territoryStatuses`, `currentOrders` + local `ordersMeta`); older phases from `status.phases[i]`. Legal orders are precomputed only when `overview.user` exists (`fetchGameData/fulfilled.ts:54-56`) |
| Member with `status === "Left"` | Overlay with "Rejoin Game" → `game/markbackfromleft` (`WDMainController.tsx:101-128`); disabled if `isTempBanned` |
| Spectator / non-member (logged in) | `overview.user` undefined ⇒ `countryID` omitted ⇒ `game/data` returns no `contextVars` / `currentOrders`; `status.status` is `""` and `status.countryID` null. Board is always in historical mode, so even the current position is drawn from the **last entry of `status.phases`**, not from `game/data.units`. Map clicks are rejected (`processMapClick.ts:95-98`); save/ready controls hidden (`BottomLeft.tsx:21`); press tab shows only the ALL channel with no input (`WDPress.tsx:130,163`). `game/getmessages` without `countryID` is answered 403 for non-members (`api.php:1741-1743`), so the global chat stays empty and one GET failure is counted. SSE: subscribes to `private-game<id>-countryundefined` (`OpenModalButton.tsx:89-91`) → `countryID = NaN` → token requested for `private-game<id>` but `/events` validates the `…-countryNaN` channel → 403 → watchdog retries (`sselistener.ts:163-195`) about every 17 s, each retry re-POSTing `sse/authentication`. No live updates for spectators |
| Logged-out visitor | every api.php call returns 401; phase stays `"Loading"`; after more than 3 consecutive GET failures an alert is shown (`handleSucceededFailed.ts:21-36`). Only the overview call is made, so in practice the page sits on the loading state |
| Pre-game | `loadGame` returns early; `noPhase` blocks data/overview refetches (`WDMainController.tsx:75-94`); overlay "Game is waiting to start"; no message fetch (`OpenModalButton.tsx:73`). SSE is still connected |
| Finished game | `phase === "Finished"`: historical mode only; `WDGameFinishedOverlay` on the last phase (needs `members[].supplyCenterNo/status/bet/pointsWon/username`); `BottomLeft` not rendered (`WDUI.tsx:118`); messaging allowed (`WDPress.tsx:132-133`). For the last phase the season/year come from overview rather than `status.phases` (`WDUI.tsx:93-103`). Server returns a context with empty `contextKey` and no orders (`api.php:1094-1112`) |
| Sandbox game | `data.isSandboxMode` ⇒ every unit is an own unit (`fetchGameData/fulfilled.ts:44-51`), legal orders are computed for all countries, `currentOrders` holds all countries' orders (each with `countryID`), and in Builds the acting country is inferred from the clicked territory's home country / unit owner (`processMapClick.ts:177-195`). Sandbox buttons are keyed off `overview.alternatives.includes("Sandbox")` instead (`WDInfoDisplay.tsx:79`). Shared sandbox links (`sbToken`) are not supported by the beta; `board.php:92` keeps those on the legacy board |
| Error phase | `"Error"` is handled in four components but nothing in the client ever sets it and the server does not send it |

---

## 5. Bundled static data that duplicates server data

| File | Contents | Duplicates | Used at |
|---|---|---|---|
| `data/map/ProvincesMapData.ts` (3188 lines) | Per `Province` (104 entries: 75 playable + 29 decorative): `abbr`, `type` (`Land`/`Coast`/`Sea`), SVG `path`, `x/y/width/height`, `labels[]`, `unitSlots[]` (`name` main/nc/sc, `x/y`, `arrowReceiver`, and for nc/sc the coast `Territory`), `centerPos` (34 provinces), `playable`, `texture`, `fill` | `type` ↔ `territories[].type`; `centerPos` presence ↔ `territories[].supply`; nc/sc slots ↔ coast child territories; `abbr` has no API counterpart | SC markers are drawn from `centerPos` alone (`WDProvince.tsx:103-110`); build-unit choice from bundled `type` (`WDBuildContainer.tsx:60-63`, `processMapClick.ts:246`); build destinations from bundled `unitSlots` (`precomputeLegalOrders.ts:198-202`); order text from `abbr` (`WDOrdersPanel.tsx:44-54`) |
| `data/map/variants/classic/TerritoryMap.ts` | `webdipNameToTerritory`: 81 API territory **names** → `Territory` enum; `coastData`: 6 coast children → parent + slot; derived `TerritoryMap` keyed by both enum and API name | names ↔ `wD_Territories.name`; coast parents ↔ `coastParentID` | `generateMaps.ts:21-27`, `getUnits.ts:139`, `WDArrowContainer.tsx:47-48`, `precomputeLegalOrders.ts:79` |
| `enums/map/variants/classic/Territory.ts`, `Province.ts` | 81 territories, 104 provinces | | everywhere |
| `data/map/variants/classic/CountryIDMap.ts` | hard-coded `1..7` → England, France, Italy, Germany, Austria, Turkey, Russia | `variant.countries` order / `members[].countryID` | only `WDBuildContainer.tsx:64` |
| `data/map/variants/classic/CountryMap.ts`, `enums/Country.ts` (`abbrMap`) | API country **name** → enum, 3-letter abbreviations | `members[].country` | `WDUI.tsx:58-63`; `getUnits.ts:197` |
| `webDiplomacyTheme.ts:106-135` and `tailwind.config.js:14-27` | country colours (`main`/`light`), the same 7 pairs in both files, keyed by country name | legacy board colours live in PHP/CSS; not in any API response | `WDProvince.tsx:46,58`; class names like `bg-${country}-main` (`WDPress.tsx:100`, `WDPhaseUI.tsx:58`) |
| `utils/state/getPhaseSeasonYear.ts:18-28`, `utils/formatTime.ts:32-46` | `year = floor(turn/2) + 1901`, Spring/Autumn/Winter naming | `overview.season` / `overview.year` (server `datetxt`) | historical phases, message headers, active-games list. The current phase uses the overview values |
| `state/game/initial-state.ts:111-123` | `coastParentIDByChildID` / `coastChildIDsByParentID` for IDs 76-81 | `overview.variant.*` | placeholder, never read |
| `data/BuildUnit.ts`, `models/enums.ts` | order-type and unit-type strings (`"Build Army"`, `"Support hold"`, …) | server order type strings | order construction |

Not bundled: adjacency/borders, territory IDs, home-centre ownership. They come only from `game/data.territories`.

**Reconciliation.** `generateMaps` (`utils/state/generateMaps.ts`) runs on every `game/data` response and builds `territoryToTerrID`, `terrIDToTerritory`, `terrIDToProvinceID` (`coastParentID || id`), `terrIDToProvince`, plus unit maps. The only join is `webdipNameToTerritory[territories[id].name]`; territory IDs are never hard-coded (apart from the unused placeholder above). Consequences: (a) the board cannot start without the `territories` block, which is static per variant yet is re-sent with every `game/data` call; (b) the server passes `name` through `l_t()` (`variants/install.php:61`), so a translated name would make `TerritoryMap[undefined].province` throw at `generateMaps.ts:26`; (c) both worlds are consulted for the same fact in places, e.g. the build popup uses bundled `type === "Coast"` while build legality uses API `supply` and home `countryID` (`precomputeLegalOrders.ts:186-195`).

---

## 6. Write operations

| Operation | Route / payload | Response fields used | Local effect |
|---|---|---|---|
| Save / Ready / Unready orders | POST `../ajax.php` (`?ready=on` to ready, `?notready=on` to unready, neither to save). Body is `FormData`: `orderUpdates` = JSON array with one entry per `currentOrders` row `{id, unitID, type, toTerrID, fromTerrID, viaConvoy}` overlaid with the local update, which can add `convoyPath` and leaks client fields (`inProgress`, `orderID`, `countryID`, `subsequentClicks`) (`WDOrderStatusControls.tsx:118-138`, `commitOrder.ts:8-20`); `context` = `JSON.stringify(contextVars.context)`; `contextKey` | From the **`X-JSON` header**, parentheses stripped (slice`:235-249`): `invalid`, `notice`, `orders[id].status` (`=== "Complete"`), `newContext` (stored whole; only `newContext.orderStatus` is parsed), `newContextKey`. Unused: `statusIcon`, `statusText`, `process`, `orders[id].changed`, `orders[id].notice`. A missing header is reported as "game already advanced" | `saveOrders/fulfilled.ts:22-70`: replaces `data.contextVars`, rewrites `overview.user.member.orderStatus` from `newContext.orderStatus`, marks `ordersMeta[id].saved`. No refetch on success. On `invalid` or network error: alert + `needsGameOverview` + `needsGameData` |
| Vote | POST `game/setvote` `{countryID, gameID, vote, voteOn}`; only Draw / Pause / Cancel are offered (`enums/Vote.ts`), the server also accepts Concede | whole body: comma-separated votes string | slice`:413-426`: writes `votes` on `user.member` and the matching `members[]` row. Other clients learn via SSE `set-vote` |
| Send message | POST `game/sendmessage` `{gameID, countryID, toCountryID, message}` | `messages[]` (`fromCountryID, message, timeSent, toCountryID, turn`; no `phaseMarker`) | merged into `messages.messages` (slice`:436-451`); GA event unless it is a note to self |
| Mark messages seen | POST `game/messagesseen` `{countryID, gameID, seenCountryID}` | none (server returns nothing) | local `processMessagesSeen` first (slice`:313-323`). Sent on every click inside the press panel, including for `seenCountryID=0`, with no de-duplication |
| Back from Left | POST `game/markbackfromleft` `{countryID, gameID}` | none | sets `status.status = "Playing"` (slice`:432-434`) |
| SSE token | POST `sse/authentication` `{channel_name, gameID}` | `data.auth` | used in the `/events` URL |
| Sandbox copy / move turn back / delete | **GET** `sandbox/copy?copyGameID=`, `sandbox/moveTurnBack?gameID=`, `sandbox/delete?gameID=` | `gameID` (copy only) | navigate to `/board.php?gameID=<new>`, `location.reload()`, or `/` (`WDInfoDisplay.tsx:83-100`). State-changing GETs |

There is no join/leave, no ready-without-orders, no pause/unpause other than the Pause vote, and no notes-to-self guard in the client.

---

## 7. Startup sequence

1. Browser loads the static `/beta/index.html?gameID=N` (usually via the `board.php` redirect). Inline GA script reads the `wD-Key` cookie and `localStorage["wD-userType"]`; `../javascript/adsense.js` is loaded (deferred).
2. `App` mounts → `dispatch(loadGame(N))` (`App.tsx:14-16`) → **GET `game/overview`**. Needed for: membership and `countryID`, `gameID`, phase/turn/season/year, timers, members table, press rules, `sseAuth`.
3. `loadGame` then calls `loadGameData` without dispatching it (slice`:286`), a no-op. (It would also have passed `countryID` as a number.)
4. `fetchGameOverview.fulfilled`: phase key changes from `0.Loading` ⇒ `needsGameData = true`; overview stored wholesale (`fetchGameOverview/fulfilled.ts:24-30`).
5. `WDMainController` re-renders and, in its render body, dispatches `loadGameData(N, countryID?)` (`:83-94`) → three GETs in parallel:
   - **`game/data`**: territories + borders (static per variant), live units, province statuses, and for members `contextVars` + `currentOrders`. Needed for `maps`, `ownUnits`, `territoriesMeta`, `legalOrders`, `ordersMeta`.
   - **`game/status`**: full phase history (units, centers, orders per phase), member `status`, `countryID`. Needed for the phase selector, previous-phase move arrows and "moved from" markers, and as the sole position source for spectators and finished games.
   - **`players/active_games`**: only for the Games tab.
6. In the same commit, `OpenModalButton`'s effect (`:85-109`, deps `[gameID, countryID]`) fires **GET `game/getmessages`** (`sinceTime=0`, all messages) and calls `client.subscribe("private-game<N>-country<C>")`. `WDMainController`'s effect (`:49-69`) subscribes `private-game<N>`, which the authorizer ignores for connection purposes but whose `overview` callback is registered.
7. SSE: `getToken()` uses `overview.user.sseAuth` if fresh, otherwise **POST `sse/authentication`**; then opens **`/events?channelList=…&auth=…&turn=<overview.turn>&phase=<overview.phase>&since=<messages.time || token time>`**. The SSE server replays missed `processed` / `message` events and ends with `catchup` (`sse-server/server.js:98-140`); on the first connection `catchup` triggers nothing.

Steps 5 and 6 are concurrent, so the steady-state startup cost for a member is **5 GETs + 1 EventSource** (6 GETs + 1 POST if the token is stale).

**Overlap between responses**

| Datum | Arrives in | Which copy the board uses |
|---|---|---|
| `turn`, `phase` | overview, data, status, every `status.phases[]`, `contextVars.context` | overview drives the UI; data's copy detects phase change for clearing orders; status's copy only feeds the unused `consistentPhase` |
| Current units, SC ownership | data (`units`, `territoryStatuses`) and the last entry of `status.phases` (`units`, `centers`) | data when the viewer is an active member on the latest phase; otherwise status |
| Viewer's order status | `overview.user.member.orderStatus`, `status.orderStatus`, `contextVars.context.orderStatus`, ajax `newContext.orderStatus` | overview's, patched from `newContext` after a save |
| Viewer's member status | `overview.user.member.status`, `status.status` | `status.status` for the Left/Playing gates; overview's for the tables |
| Viewer's `countryID` | `overview.user.member.countryID`, `status.countryID`, context | overview's (status's once, `WDMain.tsx:140`) |
| Votes | `overview.members[].votes`, `status.votes` / `status.publicVotes` | overview only |
| Unread senders | `overview.user.member.newMessagesFrom`, `getmessages.newMessagesFrom`, `active_games[].newMessagesFrom` | getmessages only |
| `pressType`, `potType`, `gameOver`, `variantID`, `gameID` | overview and status | overview's `pressType` and `gameID`; the rest unused in both |
| `processTime`, phase length | overview, status (undeclared), active_games | overview (current game); active_games (other games) |
| Coast parent/child relations | `overview.variant.*`, `territories[].coastParentID`, bundled `coastData` | the latter two |

---

## 8. Other surprises worth knowing before replacing endpoints

- **Full history on every phase.** `game/status` (all phases, all orders) and the static `territories` block inside `game/data` are re-downloaded on every `processed` event and after any failed POST, together with `players/active_games`.
- **Overview is replaced wholesale** on each fetch (`fetchGameOverview/fulfilled.ts:30`), and both the vote and order-save handlers patch it in place, so a late overview response can briefly revert a just-saved `orderStatus` / `votes`.
- **Unsaved orders survive refetches**: if any `ordersMeta` entry is unsaved, the `currentOrders` from a new `game/data` response are ignored (`fetchGameData/fulfilled.ts:57-66`).
- **Side effects in render**: `WDMainController.tsx:79-94` dispatches fetches from the render body, relying on the `setNeedsGame*` reset to avoid loops.
- **`messagesseen` is chatty** (every click in the press panel), and `messageStack` drafts are keyed by countryID only, so drafts leak across games in the same browser.
- **Reconnect authorizer check** uses `||` (`sselistener.ts:91`): a second game or country in the same page would be treated as already connected. Harmless today because a page only ever has one.
- **GET timeouts**: only `game/getmessages` has one (60 s). A hung `game/overview` leaves `outstandingOverviewRequests` true and blocks SSE-driven overview refetches until it settles.
- **Alerting**: more than 3 consecutive failed GETs of any kind raises the "Cannot connect to server" modal (`handleSucceededFailed.ts:21-36`); any successful GET or POST resets the counter.
- **Variant support**: everything is Classic-only (hard-coded enums, 1901 start year, `turnAsDate(…, "Classic")`), which is why `variant` / `variantID` can go unread; `board.php` only redirects Classic games to the beta.
