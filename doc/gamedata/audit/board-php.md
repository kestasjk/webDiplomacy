# Audit: what `board.php` (classic board) needs, and where each piece comes from

Repo: `~/Desktop2/webdiplomacy` @ `d0513c40` (branch `master`, clean). Read-only audit; nothing was modified.
All `file:line` references are relative to that repo root; line numbers are real file lines (CRLF files read with `\r` stripped).
Goal context: move every board client onto (1) static per-variant JSON, (2) static per-game public `game.json` written by the gamemaster, (3) one authenticated `game/playercontext` endpoint for private data.

Legend for the "target" column used below: **V** = per-variant static file, **G** = public per-game `game.json`, **P** = private `game/playercontext`, **X** = stays server/page-only or not needed.

---

## 0. Page flow of `board.php` (orientation)

| Step | Code | Notes |
|---|---|---|
| Join / leave POST | `board.php:35-72` | Uses `processGame` (locks game `FOR UPDATE`), `Members->join()` `gamemaster/members.php:468`, `processMember->leave()` `gamemaster/member.php:40`. `libHTML::checkTicket()` CSRF. Always `die()`s. |
| Load game for display | `board.php:76-81` | `$Variant->panelGameBoard($gameID)` → `panelGameBoard extends panelGame extends Game` (`gamepanel/gameboard.php:32`, `gamepanel/game.php:46`, `objects/game.php:33`). Members loaded as `panelMembers`/`panelMember` (`gamepanel/members.php:29`, `gamepanel/member.php:28`). |
| Sandbox access gate | `board.php:83-87` | Non-creator, non-moderator, no valid `sbToken` → "Access denied". |
| Redirect to React board | `board.php:90-99`, `objects/game.php:567-575` | Classic/ClassicGvI/ClassicFvA games redirect to `beta/?gameID=` when the viewer is a guest, on the play-now domain, has the point-and-click option, or `view=pointAndClick`; `view=dropDown` forces the classic board. Not done when `sbToken` is present. |
| Member-only setup | `board.php:104-148` | `makeUserMember` → `userMember` (`board/member.php:29`): updates `timeLoggedIn` if >3 min stale (`:48-53`), runs `markBackFromLeft()` if status `Left` (`:44-47`, `objects/member.php:273`). Vote POSTs handled (`board.php:123-128`). Order interface built (`:132-139`). `processHint` appended to Redis if `needsProcess()` (`:143-146`). |
| Chatbox | `board.php:151-161` | Only when phase != `Pre-game` AND viewer is a member, a Moderator, or a director. Plain spectators get **no chatbox** on the board (they use Archive → Messages). |
| Archive / suspicion sub-pages | `board.php:176-219` | `viewArchive` = `Orders` / `Messages` / `Graph` / `Maps` / `Reports`, or `lodgeSuspicion`. |
| Render | `board.php:223-304` | header (`contentHeader()`), chatbox, map, orders, `summary()` (votes + members + links), mod actions (`:251-293`), director admin forms (`:295-300`). |

---

## 1. Data inventory

### 1(a) Game-level info

Source row: `Game::fetchRow()` `objects/game.php:619-658` (`wD_Games g LEFT JOIN wD_TournamentGames tg LEFT JOIN wD_Tournaments t`). Columns selected exactly: `id, variantID, LOWER(HEX(password)) password, turn, phase, processTime, name, gameOver, attempts, pot, potType, phaseMinutes, phaseMinutesRB, nextPhaseMinutes, phaseSwitchPeriod, processStatus, pauseTimeRemaining, minimumBet, anon, pressType, missingPlayerPolicy, drawType, minimumReliabilityRating, excusedMissedTurns, playerTypes, startTime, directorUserID, sandboxCreatedByUserID, t.directorID tournamentDirectorUserID, t.coDirectorID tournamentCodirectorUserID`. `private = isset(password)` (`objects/game.php:558`). `wD_CivilDisorders` loaded separately (`:605-616`: `userID, countryID, turn, SCCount`).

| Field | Where shown / used | Code | Target |
|---|---|---|---|
| `id` | all links, map URLs | everywhere | G |
| `name` | title bar, page `<title>` | `gamepanel/game.php:255`, `objects/game.php:885`, `board.php:102` | G |
| `variantID` → `Variant->name`, `fullName`, `countries[]`, `mapID`, `supplyCenterTarget`, `supplyCenterCount` | CSS class `variant<Name>`, variant link, country names, progress bars | `gamepanel/game.php:54`, `objects/game.php:489`, `gamepanel/member.php:340` | G (`variantID`) + V (rest) |
| `turn`, `phase` | "Spring 1901, Diplomacy" via `Variant->turnAsDate()`; JS `turnToText` = `Variant->turnAsDateJS()` for map history | `gamepanel/game.php:251`, `gamepanel/gameboard.php:78` | G (turn, phase); V (turn→date function/rule) |
| `processTime` | "Next: …" countdown (`.timeremaining[unixtime]` updated client side by `updateTimers()` `javascript/timeHandler.js:151`), "Start:" pre-game, "Finished:" when over | `gamepanel/game.php:129-151` | G |
| `processStatus` (`Not-processing`/`Processing`/`Crashed`/`Paused`) | Paused icon / "Crashed"; vote button label `Pause`→`Unpause`; mod "Process now" only if `Not-processing` | `gamepanel/game.php:136-139`, `gamepanel/gameboard.php:237,251`, `board.php:261` | G |
| `pauseTimeRemaining` | "(X left on unpause)" | `gamepanel/gameboard.php:102-112` | G |
| `phaseMinutes`, `phaseMinutesRB` | "**X** /phase (M) \| **Y** /phase (R, B)" | `gamepanel/game.php:297-304` | G |
| `nextPhaseMinutes`, `phaseSwitchPeriod`, `startTime` | "Changing phase length … In/At" | `gamepanel/game.php:188-222` | G |
| `pot` | "Pot: N", featured star if `pot > $Misc->GameFeaturedThreshold` | `gamepanel/game.php:179,258` | G (pot); featured threshold is site-global (`wD_Misc`) |
| `potType` (`Points-per-supply-center`/`Winner-takes-all`/`Unranked`/`Sum-of-squares`) | scoring long name; vote help text; per-member "worth" via `Scoring->pointsForDraw()` | `objects/game.php:405-422,514`, `gamepanel/gameboard.php:268-285`, `gamepanel/member.php:268` | G |
| `pressType` (`Regular`/`PublicPressOnly`/`NoPress`/`RulebookPress`) | "No messaging"/"Rulebook press"/"Public messaging only"; chat tab + send rules; RulebookPress hides Save button outside Diplomacy | `objects/game.php:491-499`, `board/chatbox.php:65,97-103,205-211,292`, `board/orders/orderinterface.php:403-405` | G |
| `anon` (`Yes`/`No`) | "Anonymous players"; drives all identity hiding | `objects/game.php:474,510` | G |
| `drawType` (`draw-votes-public`/`draw-votes-hidden`) | "Hidden draw votes"; draw-vote filtering | `objects/game.php:516`, `gamepanel/member.php:408-422`, `gamepanel/gameboard.php:286-297` | G |
| `missingPlayerPolicy` (`Normal`/`Wait`) | "Wait for orders"; notice bar when waiting past deadline | `objects/game.php:520`, `gamepanel/game.php:93-94` | G |
| `playerTypes` (`Members`/`Mixed`/`MemberVsBots`) | "Fill with Bots"/"Bot Game"; vote help; instant pause/cancel in bot games | `objects/game.php:501-508`, `gamepanel/gameboard.php:317-329`, `board/member.php:89` | G |
| `excusedMissedTurns` (game rule) | "N excused missed turn" in title bar; "Delays left: x of N" per member | `gamepanel/game.php:247`, `gamepanel/member.php:442-443` | G |
| `gameOver` (`No`/`Won`/`Drawn`) | "Game won by X" / "Game drawn" notice bar and map caption | `gamepanel/game.php:157-168`, `objects/game.php:720-733`, `map.php:536-537` | G |
| `password` → `private` | lock icon; invite-code box in join form. **Hash itself must never be published** (it is loaded into the object: `objects/game.php:626`) | `gamepanel/game.php:182,440-443,513` | G (boolean `private` only) |
| `minimumBet`, `minimumReliabilityRating` | join bar ("Bet to join", "Required Reliability") | `gamepanel/game.php:474-535` | G |
| `directorUserID`, `tournamentDirectorUserID`, `tournamentCodirectorUserID` | `isDirector()` only: reveals names in anon games, grants Global chat posting, admin forms. **Not displayed** on the board. No tournament links are rendered by the board panels. | `objects/game.php:451-456`, `gamepanel/member.php:185`, `board/chatbox.php:128,203`, `board.php:295` | P (as `isDirector` flag) / X |
| `sandboxCreatedByUserID` | "Sandbox game"; access gate; sandbox bar (copy / move back / delete / public link); order interface sandbox mode | `objects/game.php:524`, `board.php:83`, `gamepanel/game.php:410-422`, `board/orders/orderinterface.php:62` | G (`isSandbox` bool) + P (`isSandboxOwner`) |
| `civilDisorderInfo` (`wD_CivilDisorders`) | "Civil Disorders" table (username, points, country, turn, `SCCount`) — only moderators-not-in-game or Finished games | `gamepanel/members.php:102-122` | G only once Finished (contains user identities) |
| `attempts` | not displayed (gamemaster crash detection) | `gamemaster.php:226` | X |
| Watched state (`wD_WatchedGames`) | "Spectate game"/"Stop spectating" button | `gamepanel/game.php:537-550`, `objects/game.php:577-603` | P |
| Joinable / join form | depends on `$User->points`, `reliabilityRating`, temp ban, `civilDisorderInfo[$User->id]` | `objects/game.php:683-711`, `gamepanel/game.php:449-554` | P (viewer-specific) |

### 1(b) Per-member info

Source query: `Members::load()` `objects/members.php:206-234` — `wD_Members m INNER JOIN wD_Users u`. Columns exactly: `m.id, m.userID, m.gameID, m.countryID, m.status, m.orderStatus, m.bet, m.missedPhases, m.timeLoggedIn, m.newMessagesFrom, m.votes, m.supplyCenterNo, m.unitNo, m.excusedMissedTurns, COALESCE(m.hideNotifications,0), u.username, u.points, m.pointsWon, 0 as online, u.type as userType`.
Ordering: `m.status ASC, m.supplyCenterNo DESC, (anon=='Yes' ? m.countryID ASC : u.points DESC), m.id ASC` (`:229-231`) — **the anon branch exists so that row order does not leak identity (points)**; a static file must keep a non-identifying order (e.g. by `countryID`).
Display order on the board: by status `Won, Survived, Drawn, Playing, Left, Resigned, Defeated` (`gamepanel/members.php:69,91-98`).

| Field | Shown as | Visibility rule | Code | Target |
|---|---|---|---|---|
| `countryID` / `country` | coloured country name (`country<ID>`, `memberStatus<status>`, `memberYourCountry` for self) | always (0 = `Unassigned` pre-game) | `gamepanel/member.php:170-179`, `objects/member.php:181-184` | G |
| `userID`, `username`, `u.points`, `u.type` (type icons) | profile link + "(points, icons)" | replaced by `(Anonymous)` when `isNameHidden()` | `gamepanel/member.php:181-213` | G only when not hidden; never while anon & unfinished |
| `status` (`Playing,Defeated,Left,Won,Drawn,Survived,Resigned`) | status text if not Playing; fade class | always | `gamepanel/member.php:382-383,538` | G |
| `supplyCenterNo`, `unitNo` | "N supply-centers, M units"; progress bar vs `Variant->supplyCenterTarget`; occupation bar (`SCPercents`) | always (not for Defeated) | `gamepanel/member.php:240-252,309-364`, `objects/members.php:40-75`, `gamepanel/members.php:174-200` | G |
| `bet`, `pointsWon`, draw value | "Bet: N, worth: M" (`Scoring->pointsForDraw`) / "won: N" | always, board only (`panelGameBoard`) | `gamepanel/member.php:258-293,385-386` | G (`bet`, `pointsWon`; "worth" is derivable from pot/potType/SCs) |
| `orderStatus` set (`None,Saved,Completed,Ready`) | icon: `- ` (None) / tick / faded tick / minor alert / alert | real icon if `anon=='No'` or name not hidden (self, director, mod-not-in-game, Finished); otherwise `iconAnon()` = lock icon, **but still `- ` when `None`** | `gamepanel/member.php:468-480,522-531`, `objects/basic/set.php:55-95` | G when `anon=='No'`; for anon only the `None`/"has options" bit is public; own full status → P |
| Aggregate "At least 1 country still needs to enter orders!" / "All countries have entered orders." | member header bar (members only) | extra `SELECT count(1) FROM wD_Members …` on statuses; shown in anon games too (class `panelAnonOnlyFlag`) | `gamepanel/member.php:57-74` | G (single boolean `allOrdersEntered` is safe and is what anon games rely on) |
| `votes` (`Draw,Pause,Cancel,Concede`) | "Votes: …" per member; `Pause` shown as `Unpause` when game paused | only for `status=='Playing'` and phase != Finished; `Draw` filtered when `drawType=='draw-votes-hidden'` and viewer != that member (mods-not-in-game see "(Hidden Draw)"); others see "(any draw votes are hidden)" | `gamepanel/member.php:398-428,438,455-457` | G (non-Draw votes always; Draw only if `draw-votes-public`); own votes → P |
| `timeLoggedIn` | "Last seen: 3 hours (date)" | exact when not hidden; bucketed (`< 1 day`, `< 1 week`, `over a week`) when `isLastSeenHidden()` (= `isNameHidden()`) | `gamepanel/member.php:188-193,448-453`, `objects/member.php:242-259` | G: exact if non-anon, bucket if anon |
| `online` | **never shown on the board**; query hard-codes `0 as online` (`objects/members.php:224`). Site-wide online icons use `cache/stats/onlineUsers.json` (`gamemaster/backgroundTasks.php:87-99`, `javascript/cacheUpdate.js:22-27`) but `libHTML::loggedOn()` is not called by any gamepanel code. | — | — | X |
| `missedPhases` | "Delayed last turn" when `>= 1` | hidden when `isMissedTurnsHidden()` (= `isNameHidden()`) | `gamepanel/member.php:195-198,445-446` | G only if non-anon |
| `excusedMissedTurns` (member) | "Delays left: x of N" (if game's `excusedMissedTurns > 0`) | hidden when `isMissedTurnsHidden()` | `gamepanel/member.php:440-444` | G only if non-anon; own value → P |
| `newMessagesFrom` (set of countryIDs, `0` = Global) | envelope icon next to each country that has sent the viewer unread messages; header "Unread messages"/"Unread global messages"; tab icons | viewer's own row only | `gamepanel/member.php:34-40,136-164`, `board/chatbox.php:311-315` | P |
| Mute state (`wD_MuteCountry`: `userID, gameID, muteCountryID`) | mute/unmute icon per other member; toggled by `?toggleMute=<countryID>` GET | viewer-specific; any logged-in user | `gamepanel/member.php:482-513`, `objects/user.php:1258-1361` | P |
| `hideNotifications` | not shown on board (home page toggle `index.php:57-61`) | — | — | P/X |
| `id` (member id) | DOM ids `member<id>StatusIcon/Text`, `orderDiv<id>`; part of order `context` | — | `gamepanel/member.php:129,472`, `board.php:135` | P (own memberID only; other member ids have no use) |

Pre-game member list: names only, comma separated (`(Anonymous)` when hidden), no countries (`gamepanel/members.php:79-87`); occupation bar shows joined/not-joined ratio (`:187-193`).

### 1(c) Map state

| Item | Source | Code | Target |
|---|---|---|---|
| Small map image | `<img id="mapImage">` → static file `cache/games/<floor(id/100)>/<id>/<turn>-small.map` if it exists **and** user option `showMoves=='Yes'`, else `map.php?gameID=&turn=[&hideMoves]` | `gamepanel/gameboard.php:34-53` | X (server-rendered PNG; unchanged by the plan) |
| Which turn is drawn | `mapTurn = (phase in Pre-game, Diplomacy) ? turn-1 : turn` | `gamepanel/gameboard.php:38`, `map.php:163-170` | derivable from G |
| Territory owners on map | `wD_TerrStatusArchive (gameID, turn, terrID, countryID, standoff)` LEFT JOINed from `wD_Territories`; turn −1 (non-sandbox) uses `wD_Territories.countryID` defaults | `map.php:219-261` | G (current owners) / per-turn archive file |
| Standoffs on map | `ts.standoff=='Yes'`, drawn only in Retreats phase or on non-small maps | `map.php:256-260` | G |
| Units + previous orders drawn | `wD_MovesArchive (type, terrID, countryID, toTerrID, fromTerrID, viaConvoy, unitType, success, dislodged)` for the turn, `ORDER BY type DESC`; de-coast reconstruction `map.php:278-313`; turn −1 of a just-started game draws `wD_Units` as Holds (`:320-331`) | `map.php:333-450` | per-turn public archive (already public) |
| Game-over caption | `Game->gameovertxt(TRUE)` = "Game won by `<username>`" baked into PNG | `map.php:536-537`, `objects/game.php:720-733` | — (only when Finished → identities public) |
| Large map | `map.php?…&mapType=large` (link, new window) | `gamepanel/gameboard.php:41,61`, `javascript/mapUI.js:139-143` | X |
| History navigation | `loadMap()/loadMapStep()` swap `mapImage.src` to `map.php?gameID=&turn=N[&hideMoves][&preview…]`; `turnToText(turn)` label | `javascript/mapUI.js:67-147`, `gamepanel/gameboard.php:55-64,76-82` | X |
| Preview of own saved orders | `map.php?...&preview` draws viewer's `wD_Orders` (own country; all countries for sandbox owner) on top; never cached (`PREVIEW` → `writeToBrowser`) | `map.php:455-518,568-571`, `gamepanel/gameboard.php:65` | P-equivalent (private; needs session) |
| Colour-blind variants | `colorCorrect=Protanope|Deuteranope|Tritanope`, `countryNames`; client-side Daltonize of the loaded image | `map.php:56-78,557-566`, `javascript/mapUI.js:149-160` | X |
| Live units (for the order form) | `wD_Units (id, terrID, countryID, type)` for the game | `board/orders/jsonBoardData.php:32-42` | G |
| Live territory status (for the order form) | `wD_TerrStatus`: `terrID as id, standoff (bool), occupiedFromTerrID, occupyingUnitID as unitID, countryID as ownerCountryID` | `board/orders/jsonBoardData.php:49-60` | G |
| Retreating units | **Not explicit**: `retreatingUnitID` is *not* selected. The JS infers a retreating unit as any `Units` entry that no `TerrStatus.unitID` points at (`javascript/board/load.js:76-97`); its `terrID` is where it was dislodged; illegal retreat targets come from `standoff` and `occupiedFromTerrID` (`javascript/orders/phaseRetreats.js:51-55`) | | G (suggest making `retreating` explicit) |

No variant overrides unit/territory/order visibility (no fog of war): variant class overrides are limited to `drawMap`, `adjudicatorPreGame`, `processGame`, `processMembers`, `processOrder*`, `userOrder*`, `OrderInterface`, `Chatbox`, `panelMembersHome`, `OrderArchiv` (`grep variantClasses variants/*/variant.php`). None touches `jsonBoardData`, `panelGameBoard::mapHTML`, `map.php` queries or the archive pages.

### 1(d) Orders — what the order interface needs

Built only when viewer is a member, `status=='Playing'`, phase not Pre-game/Finished (`board.php:117-141`). `OrderInterface::newBoard()` → `newContext()` (`board/orders/orderinterface.php:54-64`), variant-overridable class.

**Injected page JS** (`orderinterface.php:312-319`):
```js
context=<json>;          // object literal
contextKey="<72 hex chars>";
ordersData=[ ... ];
```

**`context` fields** — produced by `getContext()` (`:282-301`) iterating the object's properties in declaration order and keeping those in `$contextVars`, then appending `orderStatus`:

| Field | Type / value | Origin |
|---|---|---|
| `gameID` | int | `$Game->id` |
| `variantID` | int | `$Game->Variant->id` |
| `userID` | int | `$User->id` |
| `memberID` | int | `$Member->id` |
| `turn` | int | `$Game->turn` |
| `phase` | string `Diplomacy`/`Retreats`/`Builds` | `$Game->phase` |
| `countryID` | as loaded from DB (numeric string) | `$Member->countryID` |
| `tokenExpireTime` | int = `processTime + 6*60*60` (`:62`) — **never enforced**, the check is commented out (`:160`) | |
| `maxOrderID` | highest `wD_Orders.id` loaded, or `false` | `:141-157` |
| `isSandboxMode` | bool = `sandboxCreatedByUserID !== null` | `:62` |
| `orderStatus` | string, comma list e.g. `"Saved,Completed"` | `setMemberOrderStatus::__toString` |

**`contextKey`** = `md5(Config::$jsonSecret.$json) . sha1(Config::$jsonSecret.$json)` where `$json = json_encode($context)` (`:298-300`). It is a stateless bearer token:
- `ajax.php` defines `AJAX` so `header.php:207` skips session auth entirely; the key is the *only* authentication for order saving (`ajax.php:122-128`).
- Verification re-encodes the client-supplied context and compares keys (`:66-73`), so **field order and types must round-trip exactly**.
- `orderStatus` is trusted from the signed context, not re-read from `wD_Members` (`:88`, `:135` runs the `SELECT … FOR UPDATE` only as a lock and discards the row; `set()` bails if the *context* says Ready `:167`). A new context+key is issued whenever status changes (`:272-274`), but older keys for the same turn/phase remain valid.
- The only staleness guard is `turn`/`phase` vs `wD_Games` (`:153-155`), which answers with an HTML `libHTML::notice` page.

**`ordersData`** = `json_encode($this->Orders)` → public properties of `userOrder` objects (`board/orders/base/order.php:38-99`, `board/orders/order.php:52-54`), loaded from `SELECT id, type, unitID, toTerrID, fromTerrID, countryID, viaConvoy FROM wD_Orders WHERE gameID=… [AND countryID=…]` (`orderinterface.php:137-138`; the country filter is dropped in sandbox mode):

| Field | Meaning |
|---|---|
| `id` | `wD_Orders.id` |
| `type` | `Hold`, `Move`, `Support hold`, `Support move`, `Convoy` / `Retreat`, `Disband` / `Build Army`, `Build Fleet`, `Wait`, `Destroy`; null if unset |
| `countryID` | owner country |
| `unitID` | `wD_Units.id` (null for build-phase orders) |
| `toTerrID`, `fromTerrID` | territory ids or null |
| `viaConvoy` | `Yes`/`No`/null |
| `isSandboxMode` | bool |
| `error` | null or validation message |
| `status` | `Loading`/`Loaded`/`Validating`/`Incomplete`/`Invalid`/`Complete` |

JS consumes `id, status, error, unitID, type, toTerrID, fromTerrID, viaConvoy, countryID` (`javascript/orders/order.js:21-33`) and posts back per complete order `{id, unitID, type, toTerrID, fromTerrID, viaConvoy[, convoyPath]}` (`:38-47`). Server accepts only `type,toTerrID,fromTerrID,viaConvoy` (+`convoyPath` int array) from input (`board/orders/order.php:95,143-153`).

Order rows are created per phase by the gamemaster (`gamemaster/game.php:886-919`); members with no rows get `orderStatus='None'`.

**JS files loaded** (`orderinterface.php:321-329,347-350`): `board/model.js`, `board/load.js`, `orders/order.js`, `orders/phase<Phase>.js`, `orders/form.js`, the variant `territories.js`, then calls `loadTerritories(); loadBoardTurnData(); loadModel(); loadBoard(); loadOrdersModel(); loadOrdersForm(); loadOrdersPhase();` and `OrdersHTML.formInit(context, contextKey)`. Variant `OrderInterface` overrides splice extra JS after `loadBoard()` etc.: BuildAnywhere / Modern2 / ClassicChaos `supplycenterscorrect.js`, Zeus5 `AlternateBuildSC.js` + `coastConvoy_V1.3.js` fed with static `$Variant->convoyCoasts = ['38','61','71','87']` (`variants/Zeus5/classes/OrderInterface.php:28-49`, `variants/Zeus5/variant.php:75`) — i.e. **a few variants need static data beyond territories.js** (build-anywhere rule, convoy-capable coasts) → V.

**`territories.js`** (per variant, static) — generated by `InstallCache::terrJSON()` `variants/install.php:43-95`:
```js
function loadTerritories() {
Territories = $H({"1":{...},"2":{...}});
}
```
Per territory (all scalar values are **strings** because they come from `tabl_hash`): `id, name` (passed through `l_t()` at install time), `type` (`Coast`/`Land`/`Sea`), `supply` (`Yes`/`No`), `countryID` (initial/home owner, `0` neutral), `coast` (`No`/`Parent`/`Child`), `coastParentID` (= own id unless a child coast), `smallMapX`, `smallMapY`, `Borders[]` and `CoastalBorders[]` each `{id, a (armysPass bool), f (fleetsPass bool)}` from `wD_Borders` / `wD_CoastalBorders`. Large-map coordinates (`mapX/mapY`) are not included. Client derives `coastParent`, inherits `supply` and `Borders` from the parent for child coasts (`javascript/board/model.js:40-54`). Home-SC test for builds uses `countryID` + `ownerCountryID` (`javascript/board/load.js:99-113`, `javascript/orders/phaseBuilds.js:66-89`).

**Per-turn JSON ("json" map type)** — despite the name it is a JS file:
```js
function loadBoardTurnData() {
Units = $H({"<unitID>":{"id","terrID","countryID","type"},...});

TerrStatus = [{"id","standoff":bool,"occupiedFromTerrID","unitID","ownerCountryID"},...];
}
```
- Generator: `jsonBoardData::getBoardTurnData()` `board/orders/jsonBoardData.php:27-65`, invoked by `map.php:189-208` for `mapType=json`.
- Path: `Game::mapFilename($gameID, $turn, 'json')` = `cache/games/<floor(id/100)>/<id>/<turn>-json.map` (`objects/game.php:52-64,144-150`), where `<turn>` is `turn-1` in Diplomacy else `turn` (`orderinterface.php:353`). Hence **one filename serves up to three phases** (turn N Retreats, turn N Builds, turn N+1 Diplomacy) and always contains the *live* `wD_Units`/`wD_TerrStatus`, not archived state; correctness relies entirely on the cache wipe after processing.
- When generated: lazily, on first board view after a wipe. If the file is missing the page includes `map.php?gameID=&turn=&phase=&mapType=json&nocache=<rand>` (`orderinterface.php:355-356`); `nocache` sets `IGNORECACHE`, `map.php` takes lock `generate_map_<gameID>` (`:151`), builds the data, writes the file and serves it (`:193-199`) unless Redis key `processing<gameID>` is set (`map.php:44-47`, set for 10 s by `gamemaster.php:214`), in which case it is served uncached (`:200-206`). If the file exists it is referenced directly as a static URL `cache/games/…/<turn>-json.map?phase=…&nocache=<rand>` (`orderinterface.php:357-358`).
- Public: the cached branch of `map.php` runs **before** `header.php` (no session, no sandbox check: `map.php:81-107`), and the file is directly web-served.

### 1(e) Chat / messages

| Aspect | Behaviour | Code |
|---|---|---|
| Who gets a chatbox | phase != Pre-game and (member \| Moderator \| director) | `board.php:151` |
| Rendering | fully server-rendered HTML table; no client polling. Scrolls to bottom via footer script. | `board/chatbox.php:151-275` |
| Tab selection | `?msgCountryID=N` else `$_SESSION[<gameID>_msgCountryID]`; 0 = Global; own countryID = "Notes"; forced to 0 for non-Regular/non-Rulebook press (except Notes) and for temp-banned members | `:40-80` |
| Tabs shown | 0..N countries; for `PublicPressOnly`/`NoPress` only Global + Notes | `:283-327` (rule at `:292`) |
| Query | `SELECT message, toCountryID, fromCountryID, turn, timeSent FROM wD_GameMessages WHERE gameID=… AND (<where>) ORDER BY id DESC LIMIT 50` | `:344-387` |
| Per-tab filter | Global: `toCountryID = 0`; country tab: `(to=me AND from=X) OR (from=me AND to=X)`; Notes = X==me (to=me AND from=me); "All" (`-1`, archive only): `toCountryID=0 OR from=me OR to=me`; non-members always forced to Global | `:348-367` |
| Limit | 50 newest on the board; archive page paginates 20/page | `:344`, `board/info/messages.php:30` |
| New-message flags | `wD_Members.newMessagesFrom` set, appended in `libGameMessage::notify()` (`lib/gamemessage.php:136-160`; Global → all members except sender get `'0'`); cleared for the opened tab by `userMember::seen()` (`board/member.php:111-125`) from `findTab()` (`chatbox.php:71-78`); "Mark unread" re-adds (`chatbox.php:135-140`) | |
| Row markup | each `<TR>` gets class `gameID<id>countryID<fromCountryID>`; old-turn messages prefixed with the date; own messages wrapped in `messageFromMe`; colour-blind option prefixes country name; World/ClassicChaos variants always prefix `[Country]:` | `:389-456`, `variants/World/classes/Chatbox.php`, `variants/ClassicChaos/classes/Chatbox.php` |
| Message content | stored already HTML-escaped via `$DB->msg_escape()`; max 65000 bytes; GameMaster/Moderator messages are `fromCountryID=0` with a bold prefix | `lib/gamemessage.php:51-65` |
| Header above messages | Global: list of `memberNameCountry()` (username links, or country names when hidden); country tab: that member's `memberBar()` | `chatbox.php:171-181` |
| Sending | form posts (Prototype `Form.request`, AJAX) to `message.php?gameID=&msgCountryID=` with `newmessage` (+`formTicket`, which `message.php`/`postMessage` do not check); response is the re-rendered `<TABLE class="chatbox">` which replaces `#chatboxscroll` | `chatbox.php:213-238`, `javascript/message.js:1-24`, `message.php:41-65` |
| Send permission | member AND (`Regular` \| Notes tab \| `RulebookPress` in Diplomacy/Finished \| Global tab in `PublicPressOnly` \| Global tab in finished `NoPress`); non-member Moderator/director may post to Global only, prefixed `Moderator`/`Game/Tournament Director (username)` | `chatbox.php:88-133` (same predicate for showing the send box `:203-211`) |
| Mute rules | (1) sender-side block: if recipient has muted the sender's country the message is not delivered; instead a fake message "Cannot send message; this country has muted you." is inserted *to the sender, from the recipient's countryID* (`chatbox.php:105-117`). (2) viewer-side hide: page gets `muteCountries=[[gameID,countryID],…]` and `muteAll()` hides rows with class `gameID<g>countryID<c>` client-side — muted messages **are still in the HTML** (`lib/html.php:1466-1479`, `javascript/mute.js:25-40`). Global messages from a muted country are hidden the same way. | |
| Side effects of a send | insert into `wD_GameMessages (gameID,toCountryID,fromCountryID,turn,message,phaseMarker,timeSent)`; Redis `lastmsgtime_<gameID>_<countryID>` keys; `newMessagesFrom`; Redis publish `private-game<id>-country<N>` event `message` data `messageSent`; Web Push queue | `lib/gamemessage.php:42-127` |
| Vote log | every vote toggle writes a note-to-self message "Voted for X"/"Un-Voted for X" (`to=from=countryID`) | `board/member.php:91,100`, `api.php:520,585` |

Target: Global (`toCountryID=0`) messages are already public to any visitor (archive page has no membership check) → could be G or a separate public file; everything else (private, notes, `newMessagesFrom`, mute list) → P.

### 1(f) Votes

| Aspect | Behaviour | Code |
|---|---|---|
| Vote types | `Members::$votes = ['Draw','Pause','Cancel','Concede']`; `Concede` offered only if `Config::$concedeVariants` empty or contains the variantID | `objects/members.php:87`, `gamepanel/gameboard.php:132-152` |
| Form | two `<form method=post action="board.php?gameID=…#votebar">` (vote / cancel), each with `formTicket`; one submit button per vote named by the vote (`Pause` relabelled `Unpause` when paused); JS `confirm()` | `gamepanel/gameboard.php:228-260` |
| Who sees the form | members only, not Pre-game/Finished | `gamepanel/gameboard.php:121-124`, `:417-426` |
| Processing | `board.php:121-128` → `userMember::toggleVote()` `board/member.php:69-103`: writes `wD_Members.votes` + `votesChanged=UNIX_TIMESTAMP()`; `MemberVsBots` Pause/Cancel is applied to **all** members so it passes immediately (`:89-97`). **No Redis publish** here (the API routes `game/togglevote`/`game/setvote` do publish `set-vote`: `api.php:545,610`). | |
| Tallying | gamemaster cycle: `libGameMaster::findAndApplyGameVotes()` `gamemaster/gamemaster.php:448-518` — games with `votesChanged >= LastVotesCounted`, voters = `status='Playing'` non-bot members; unanimous → `processGame::applyVote()` `gamemaster/game.php:42-57` → `setDrawn`/`setCancelled`/`togglePause`/`setConcede` | |
| Display of others' votes | per-member "Votes:" line, see 1(b) | `gamepanel/member.php:398-428` |

### 1(g) History / archive panels

Links: `archiveBar()` `gamepanel/game.php:398-404` (Orders, Maps, Messages; `Graph` and `Reports` are reachable by URL but not linked there). All are served by `board.php?gameID=&viewArchive=<X>` (`board.php:176-204`) and inherit only the sandbox gate; **no membership check**.

| Panel | Data source | Code | Public? |
|---|---|---|---|
| Orders | `SELECT turn, countryID, LOWER(unitType), LOWER(type), terrID, toTerrID, fromTerrID, viaConvoy, success, dislodged FROM wD_MovesArchive WHERE gameID ORDER BY turn DESC, countryID ASC`; territory names from `wD_Territories (id,name)`; links to `map.php?…&largemap=on&turn=` | `board/info/orders.php:31-33,201-258` | yes |
| Maps | one `<img src="map.php?gameID=&turn=i">` for every turn `Game->turn … 0` + large-map links | `board/info/maps.php:32-44` | yes |
| Messages | same `Chatbox::getMessages()` with `msgFilter` (−1 all-mine+global, 0 global, N country) and `LIMIT offset,20`; non-members forced to global | `board/info/messages.php:35-53,95-96` | global part yes |
| Graph | per turn `1..turn-1`: SC counts per country from `wD_TerrStatusArchive` ⋈ `wD_Territories (supply='Yes', coastParentID=id)` | `board/info/graph.php:31-40` | yes |
| Reports | `libModNotes` (mod notes/reports) | `board.php:195-201` | moderators |

---

## 2. Runtime network calls made by the classic board after page load

| # | Call | When | Params | Response / fields used | Code |
|---|---|---|---|---|---|
| 1 | `POST ajax.php` (Save) | click "Save" | form-encoded `orderUpdates` (JSON array of `{id,unitID,type,toTerrID,fromTerrID,viaConvoy[,convoyPath]}` for complete orders), `context` (JSON), `contextKey` | JSON body + duplicate `X-JSON` header: `orders{<orderID>:{status,notice,changed}}`, `notice`, `statusIcon` (HTML), `statusText`, `invalid`, and when status changed `newContext`, `newContextKey`; on Ready transition also `process:'Checked'` | `javascript/orders/form.js:57-89,103-164`, `ajax.php:122-157,217-220`, `orderinterface.php:202-280` |
| 2 | `POST ajax.php?ready=on` / `?notready=on` | click "Ready"/"Not ready" | as #1 | as #1; server sets/clears `Ready`, writes `wD_Members.orderStatus` + `orderStatusChanged`; on new Ready appends `,<gameID>` to Redis `processHint` if `needsProcess()` | `form.js:85-89`, `ajax.php:131-156`, `orderinterface.php:230-276` |
| 3 | `POST message.php?gameID=&msgCountryID=` | click "Send" | `newmessage`, `formTicket`, `Send` | HTML `<TABLE class="chatbox">…` replaces `#chatboxscroll` | `javascript/message.js`, `message.php` |
| 4 | `GET /events?auth=&channelList=private-game<ID>,private-game<ID>-country<C>&turn=&phase=&since=<renderTime>` (SSE, `EventSource`) | 7 s after load (members with an order interface only; `configureSSE(gameID,countryID,turn,phase,time(),token)` emitted at `orderinterface.php:331-344`) | token = `md5(channel.sseSecret.ts.'generateToken')_ts` for the country channel (`lib/auth.php:49-58`), valid 24 h | events (all `event: message`, `data: {channel, message}`): `message` containing `processed` → show "Game has been processed … Click here" in `#sseGameProcessed`; containing `message` (`messageSent`) → "New message received" in `#sseMessageSent`; `set-vote` ignored; `ping` every 13 s; `catchup` cancels the fallback timer; `resync` → call #5. Reconnects if nothing received for 30 s (checked every 5 s). **The board never refreshes itself; it only shows a notice with a reload link.** | `javascript/api.js:92-252`, `sse-server/server.js:108-145,175-290`, placeholders `gamepanel/member.php:76-80` |
| 5 | `GET api.php?route=game/playercontext&gameID=` (was `game/pulse&gameID=&countryID=`) | only if no `catchup` event within 5 s of connecting, or on `resync` | session cookie auth (`ApiSession`) | uses only `game.turn`, `game.phase`, `member.lastMessageTime` (`data.turn`, `data.phase`, `data.lastMessageTimeSent` from the old route). Full response: `gameID, countryID, variantID, potType, turn, phase, gameOver, pressType, drawType, processTime, phaseLengthInMinutes, votes, orderStatus, status, members[{countryID, orderStatus ('Hidden' if anon), status, supplyCenterNo, unitNo[, votes if draw-votes-public]}], lastMessageTimeSent, lastVoteTime` | `api.js:118-133`, `api.php:808-910` |
| 6 | `POST api.php?route=sse/authentication` (JSON `{channel_name, gameID}`) | only when the page token is missing/refused/older than 23 h | session | `data.auth` | `api.js:239-250`, `api.php:621-665` |
| 7 | `GET map.php?gameID=&turn=N[&hideMoves][&preview&noCache=…]` (image) | history arrows / preview toggle; also initial image when no cached file or `showMoves=='No'` | — | PNG (`Content-Type: image/png`) | `javascript/mapUI.js:115-147`, `gamepanel/gameboard.php:39-53` |
| 8 | `GET cache/games/…/<turn>-small.map?nocache=` (static PNG) | initial image when cached and `showMoves=='Yes'` | — | static file | `gamepanel/gameboard.php:46-49` |
| 9 | `GET cache/games/…/<turn>-json.map?phase=&nocache=` **or** `map.php?…&mapType=json&nocache=` (`<script src>`) | page load (members with orders) | — | JS defining `loadBoardTurnData()` | `orderinterface.php:352-361` |
| 10 | `GET variants/<Name>/cache/territories.js?ver=JSVERSION` (`<script src>`) | page load (members with orders) | — | JS defining `loadTerritories()` | `orderinterface.php:326`, `lib/html.php:1516-1517` |
| 11 | `GET cache/stats/onlineUsers.json?ver=` (`<script src>`, every page) | page load | — | `onlineUsers=$A([...])`; unused by board markup | `lib/html.php:1513` |
| 12 | `GET api.php?route=sandbox/copy|sandbox/moveTurnBack|sandbox/delete` | sandbox bar links | `copyGameID` / `gameID` | `data.gameID` then redirect | `javascript/api.js:61-90`, `gamepanel/game.php:410-422` |
| 13 | Full-page navigations (not XHR): vote POST `board.php?gameID=#votebar`; tab change `board.php?gameID=&msgCountryID=`; mute `board.php?gameID=&toggleMute=<countryID>`; "Mark unread" POST; join/leave POST; watch/unwatch POST to `index.php`; suspicion POST to `group.php` | user action | | | see §1 |

**Polling / auto-refresh: none.** Timers (`updateTimers`) are purely client-side countdowns; the legacy `monitorForUpdate` poller is commented out (`javascript/api.js:253-298`).

---

## 3. Visibility / security rules a public `game.json` must respect

| # | Rule | Exact logic | Cite |
|---|---|---|---|
| S1 | **Anonymity master switch** | member info hidden iff `anon=='Yes'` AND `phase!='Finished'` AND NOT `hasModeratorPowers()` (= viewer is Moderator and **not** a member of the game) | `objects/game.php:462-485,543-548` |
| S2 | Per-member name hidden | `isNameHidden() = isMemberInfoHidden() && $User->id != member.userID && !Game->isDirector($User->id)` → self and game/tournament directors always see names | `gamepanel/member.php:181-186`, `objects/game.php:451-456` |
| S3 | What S2 hides | `username`, `userID` (profile link), `u.points`, user-type icons → `(Anonymous)`; chat header shows country names instead of usernames; suspicion form lists countries | `gamepanel/member.php:204-234`, `board/chatbox.php:171-177`, `gamepanel/gameboard.php:188-196` |
| S4 | Reveal moment | identities become public when `phase=='Finished'` (S1). Note the JSON API differs: `game/members` reveals `username` when `gameOver!='No'` but keeps `userID=-1`, `orderStatus={'Hidden':1}`, `timeLoggedIn=time()` for anon games forever (`api.php:950-962`) — the two clients are already inconsistent. | |
| S5 | Order status / readiness in anon games | others see lock icon (`iconAnon`) for Saved/Completed/Ready/none-submitted alike, but `- ` when `None` → **"has no orders this phase" is public even in anon games**; own, director, mod-not-in-game and Finished see the real icon. Non-anon: real icon visible to **everyone including non-members/guests**. API pulse: `orderStatus:'Hidden'` for all members when anon (`api.php:845`). | `gamepanel/member.php:468-480,519-531`, `objects/basic/set.php:84-95` |
| S6 | Aggregate readiness | "At least 1 country still needs to enter orders!" is shown to members in all games including anon (one boolean, no per-country info) | `gamepanel/member.php:57-74` |
| S7 | Last-seen | exact `timeLoggedIn` hidden → 3 buckets when S2 applies | `gamepanel/member.php:448-453`, `objects/member.php:250-259` |
| S8 | Missed turns | `missedPhases` ("Delayed last turn") and member `excusedMissedTurns` hidden when S2 applies | `gamepanel/member.php:195-198,440-447` |
| S9 | **Hidden draw votes** | when `drawType=='draw-votes-hidden'`, a member's `Draw` vote is omitted for every viewer except that member; Moderators not in the game see "(Hidden Draw)"; others see "(any draw votes are hidden)". `Pause`/`Cancel`/`Concede` votes are **always public**. API equivalents: `api.php:850-851,932-940`. | `gamepanel/member.php:398-428` |
| S10 | Votes shown only for `Playing` members while game unfinished | | `gamepanel/member.php:438` |
| S11 | Member ordering must not leak identity | anon games order by `countryID`, non-anon by `u.points DESC` | `objects/members.php:229-231` |
| S12 | Join notices in anon games | "Someone has joined / taken over" instead of username | `gamemaster/members.php:499-502,555-558` |
| S13 | Civil-disorder history (`wD_CivilDisorders`: usernames of players who left) | only for mod-not-in-game or Finished | `gamepanel/members.php:102-122` |
| S14 | **Press types** | tabs: only Global+Notes unless `Regular`/`RulebookPress` (`chatbox.php:65-67,292`). Sending: see 1(e) (`chatbox.php:96-103`). `RulebookPress`: private press only in `Diplomacy` (or `Finished`); in Retreats/Builds the send box is replaced by a "Mark unread" link (`:245-253`) and the orders "Save" button is removed, leaving only Ready (`orderinterface.php:403-405`). `NoPress`: Global posting only once Finished. Notes-to-self always allowed. API predicate is slightly looser (anything once Finished): `api.php:1635-1639`. | |
| S15 | Message privacy | only `toCountryID=0` rows are ever shown to non-members; private rows require `$Member` (`chatbox.php:348-367`, `board/info/messages.php:40-53`). Global chat is readable by **any** visitor via `viewArchive=Messages` and via `message.php` (which, unlike `board.php:151`, has no member/mod gate: `message.php:41-65`). | |
| S16 | **Private / passworded games** | the invite code gates *joining only* (`gamemaster/members.php:478-479`); viewing the board, map, archives of a private game is unrestricted. `wD_Games.password` (hex MD5) is loaded into the Game object (`objects/game.php:626`) — publish only a boolean. | `gamepanel/game.php:182-183,513-514` |
| S17 | **Pre-game** | members list = names only (or `(Anonymous)`), no countries (`countryID=0` → `Unassigned`), `ByCountryID` is null (`objects/members.php:182-185`); no chatbox (`board.php:151`), no votes (`gameboard.php:124`), no orders (`board.php:119`), no archive bar (`gameboard.php:88`); map turn −1 shows default territory colours, no units (`map.php:219-225,320-331`). Leave allowed until 30 min before a full game's start (`objects/members.php:315-332`). | `gamepanel/members.php:79-87` |
| S18 | **Moderator-only views** | Moderator *not in the game*: sees identities in anon games (red "Anonymous"/`modEyes` banner `gamepanel/game.php:356-363`), hidden draw votes, CD list, Global chat + can post as "Moderator", mod action links & multi-account links (`board.php:251-293`, suppressed for anon via `isMemberInfoHidden()` at `:273`), `viewArchive=Reports`. A Moderator who *is* a member is treated as a normal player (`hasModeratorPowers()` `objects/game.php:543-548`). Directors: names (S2), Global chat posting, admin forms (`board.php:295-300`). None of this can live in a public file → P (role flags) or stays page-only. | |
| S19 | **Muted countries** | sender blocked server-side when recipient muted them (`chatbox.php:107-114`, API `api.php:1652-1657` silently drops); viewer-side muting is client-side hiding only (`lib/html.php:1466-1479`, `javascript/mute.js`). Mute list is per viewer → P. | |
| S20 | **Sandbox games** | `board.php:83-87`: viewable only by creator, Moderators, or with `sbToken = substr(md5('SandboxToken_'.gameID.'_'.Config::$secret),0,8)` (`lib/auth.php:196-203`). **`map.php` has no such check** — cached PNG/JSON of a sandbox game are fetchable by gameID (`map.php:81-107,138-152`), and the legacy board is redirected away from only when no `sbToken` (`board.php:90`). In sandbox mode the creator gets every country's orders (`orderinterface.php:135-138`, `map.php:461`) and Ready sets all members Ready (`:269-270`). A public `game.json` for sandbox games would widen exposure vs. `board.php`; either skip them or accept parity with `map.php`. | |
| S21 | **Fog of war / unit hiding** | none. No variant overrides anything that filters units, territory owners or archived orders (see 1(c)). | `variants/*/variant.php` `variantClasses` |
| S22 | **Current-phase orders are private** | `wD_Orders` only ever reaches the owner (`orderinterface.php:137-138`, preview `map.php:455-462`); everything in `wD_MovesArchive`/`wD_TerrStatusArchive` is public immediately after adjudication (map, Orders archive). Diplomacy-phase results of the current turn are therefore public during that turn's Retreats/Builds. | |
| S23 | Temp-banned members | member with `status=='Left'` and a temp ban: treated as non-member for orders/chat, forced to Global tab, "blocked from rejoining" banner | `objects/members.php:144-149`, `board.php:104`, `gamepanel/member.php:53-56`, `chatbox.php:65` |
| S24 | Guests | guests can load `board.php` (guest `$User`, 5-minute private browser cache `header.php:225-238`); for Classic-map games they are redirected to `beta/` (`objects/game.php:572`). | |
| S25 | `ajax.php` trust model | no session; bearer `contextKey` only; `tokenExpireTime` unenforced; `orderStatus` trusted from the token (see 1(d)). `game/playercontext` will be the place that mints this token, so it must be session/API-key authenticated and member-checked. | `ajax.php:21,122-128`, `orderinterface.php:66-95,160` |

Known open items from the repo's own `SECURITY_AUDIT_MESSAGES_ANONYMITY.md` still present in code and relevant to the design: `game/getmessages` Redis short-circuit before the auth check (`api.php:1704-1723` vs `:1741`), SSE server subscribes to every client-supplied channel after validating only the country channel (`sse-server/server.js:192-201,264`, so overview channels of other games are subscribable — trigger strings only).

---

## 4. How the static files are generated and invalidated today

### 4.1 `variants/<Name>/cache/`
| File | Writer | Trigger | Invalidation |
|---|---|---|---|
| `territories.js` | `InstallCache::terrJSON($this->territoriesJSONFile(), $this->mapID)` — last line of every `variants/<Name>/install.php` (e.g. `variants/Classic/install.php:550`); data from `terrJSONData()` `variants/install.php:43-82`; path from `WDVariant::territoriesJSONFile()` `variants/variant.php:552-554` = `variants/<Name>/cache/territories.js` | `install.php` is `require`d by `WDVariant::initialize()` (`variants/variant.php:417-421`), which runs from the constructor only when the serialized variant cache `data.php` is missing (`lib/variant.php:123-142`). Re-running also **deletes and reinserts** `wD_Territories/Borders/CoastalBorders/UnitDestroyIndex` for the map (`variants/install.php:139-179`). Variants that reuse another install (FleetRome: `require_once('variants/Classic/install.php')`) write into their *own* cache dir because `$this` is the child variant. | `libVariant::wipe()` deletes `data.php` (`lib/variant.php:111-116`), called from the admin "wipe variant" action (`admin/adminActionsRestricted.php:271`) or automatically when `codeVersion > cacheVersion` (`lib/variant.php:150-161`; only Empire4, Modern2, ColdWar, Zeus5 set `codeVersion`). Browser cache busting by `?ver=JSVERSION` (`lib/html.php:1517`). Territory `name` is localised with `l_t()` at generation time. |
| `data.php` | `serialize($Variant)` of `__sleep()` fields: `cacheVersion, variantClasses, coastParentIDByChildID, coastChildIDsByParentID, terrIDByName, supplyCenterCount, supplyCenterTarget` | same | same |
| `sampleMap.png`, `sampleMap-thumbnail.png` | `map.php?variantID=` | on request | never |

Duplicate source of the same territory data: `game/data` API calls `InstallCache::terrJSONData($game->variantID)` and caches it in Redis `territories_<variantID>` for a day (`api.php:1227-1241`). It passes **variantID where the function expects mapID** — harmless only while `variantID == mapID`.

Nothing else per-variant is exported today: country names, turn→date rule, `supplyCenterTarget`, map image URLs, colours exist only in PHP (`canvasBoardConfigJS()` `variants/variant.php:124-303` emits some of it as inline JS for the sandbox creator page).

### 4.2 Per-game cache (`cache/games/…`)
- Layout: `libCache::dirID('games',$id)` = `cache/games/<floor(id/100)>/<id>/` (dirs auto-created 0775) — `lib/cache.php:55-82`, `objects/game.php:144-150`. Other bases using the same scheme: `cache/users/<floor/100>/<id>/` (`readThreads.js`, `lib/html.php:1490`), `cache/stats/` (`onlineUsers.json`), `cache/errorlogs/`, `cache/gamebackups/`. With `$absolute=true` the base is outside `cache/` (order logs: `../orderlogs/<floor>/<id>/<countryID>.txt`, `orderinterface.php:182-200`, `config.sample.php:363-366`). `libCache::privateFilename()` (`lib/cache.php:22-30`) can produce unguessable names (`<name>-md5(dir.name.secret)`) and drops an empty `index.html` in the dir.
- Files: `<turn>-small.map`, `<turn>-large.map`, `<turn>-json.map`, `<turn>-xml.map`, plus suffix variants `-hideMoves`, `-Protanope|-Deuteranope|-Tritanope`, `-names` (`objects/game.php:52-64`, `map.php:93-94,547-566`). DATC uses `datc/maps`.
- Generation: lazily by `map.php` on first request (see 1(d)); written with `$drawMap->write()` / `file_put_contents` under DB named lock `generate_map_<gameID>`; skipped while Redis `processing<gameID>` exists.
- Invalidation: `Game::wipeCache($gameID[, $turn])` `objects/game.php:115-133` → deletes Redis `gameStateArchive_<gameID>` and unlinks `*.*` (or `*<turn>-*.*`) in the game folder via `libCache::wipeDir()` (`lib/cache.php:15-20`). Call sites: `gamemaster.php:294` (whole folder, after any game update or failed process), `gamemaster/game.php:368` (moveTurnBack), `:391` (eraseGame), `:1437` (setDrawn, turn only), `:1476` (setConcede, turn only), DATC. Note the `*<turn>-*.*` glob also matches e.g. turn `12-…` when wiping turn `2`.
- **Not wiped** by: vote-applied pause/unpause (`togglePause`), admin actions on `wD_Games`, member status changes outside processing, background-task auto-draws. That is fine today because the cached files contain only map state, but a `game.json` holding game/member fields would need those hooks (see §5).

### 4.3 Web accessibility
- Dev docker nginx (`phpdocker/nginx/nginx.conf:51-69`): `root /application/.`, static files served directly; only `location ~* (\.env|gamebackups|errorlogs) { deny all; }`. So `cache/games/**`, `cache/stats/**`, `cache/users/**` and `variants/*/cache/**` are directly fetchable. Verified against the running dev stack (`localhost:43000`): `variants/Classic/cache/territories.js` → 200 `application/javascript`; `cache/errorlogs/`, `cache/gamebackups/` → 403; directory listings → 404 (no autoindex). `variants/Classic/cache/data.php` is also served (200; the serialized variant is echoed because it contains no `<?php`).
- Repo `.htaccess` (Apache/production): only blocks `/.git` and passes the `Authorization` header; no rule protects `cache/`. `sse-server/.htaccess` blocks `.env`.
- The code *relies* on direct access: `gameboard.php:46-49` and `orderinterface.php:357-358` emit `cache/games/…` URLs into the page. `.gitignore` ignores `/cache/` and `variants/*/cache/`.
- nginx config note (`:54-58`): `try_files` fallback is deliberately disabled because "missing json files instead return HTML" — a missing static JSON yields a plain 404, which clients of `game.json` can rely on.
- `/events` is proxied to the Node SSE server (`nginx.conf:34-49`).

---

## 5. Hooks where a static `game.json` could be (re)written

`RedisInterface::trigger($channel,$event,$data)` publishes `{"event":…,"data":…}` on a Redis pub/sub channel (`objects/redis.php:174-179`); the SSE server forwards it. Existing publishes: `processed` (overview), `set-vote` (overview, API only), `messageSent` (country channels).

### 5.1 Central hook (covers all turn processing)
| Where | What changes | Existing publish / cache handling |
|---|---|---|
| **`gamemaster.php:260-297`** — after `process()` + `COMMIT` (`:251-254`), when `$gameUpdated` | everything below in 5.2 | `:268` `Redis->trigger("private-game<id>",'overview','processed')`; `:294` `Game::wipeCache($Game->id)` (also runs after exceptions, `:273,290-295`); `:285` `Game::wipeTurnPhaseCache()` on rollback; `:214/:297` `processing<id>` key; Web Push `:299-315`. **Best single place to write `game.json`: after the COMMIT at `:254`, before the publish at `:268`**, so clients reacting to `processed` find the new file. Must not be written when the transaction rolled back (`:280-287`); "Abandoned"/"Cancelled" (`:275-279`) means the game rows were deleted → delete the file. |

### 5.2 State changes inside processing (all inside the transaction committed at `gamemaster.php:254`)
| Function | file:line | Public fields changed | Publish? |
|---|---|---|---|
| `processGame::process()` | `gamemaster/game.php:713-884` | orchestrates everything below | no |
| `switchPhaseTime()` | `:667-691` | `phaseMinutes`, `phaseMinutesRB=-1`, `processTime` | no |
| `recordNMRs()` / `Members->registerNMRs()` | `:575-634`, `gamemaster/members.php:614-633` | member `missedPhases` | no |
| `Members->handleNMRs()` → `removeExcuse()` / `setLeft()` | `gamemaster/members.php:651-700`, `gamemaster/member.php:501-509,329-351` | member `excusedMissedTurns`; `status='Left'` (+`wD_CivilDisorders` row); then `resetMinimumBet()` | no |
| Extension branch (`withActiveNMRs`) | `gamemaster/game.php:783-799` | `unreadyMembers()` (`objects/members.php:110-124`: `orderStatus`, `orderStatusChanged`), `resetProcessTimeForMissedTurns()` (`:649-662`: `processTime`), Global GameMaster message | message publish via `libGameMessage::send` |
| `adjudicate()` | `:926-1040` | `wD_Units`, `wD_TerrStatus` (owners `updateOwners()` `:1046-1101`, standoff/occupiedFrom/retreating), `wD_MovesArchive`, `wD_TerrStatusArchive` (`archiveTerrStatus()` `:1106-1118`), orders wiped; pre-game: country assignment (`gamemaster/adjudicator/pregame.php:124` updates `wD_Members.countryID`; `:249` flips `wD_Games.playerTypes` `Mixed`→`Members` when a fill-with-bots game fills with humans) | no |
| `Members->countUnitsSCs()` | `gamemaster/members.php:80-124` | member `supplyCenterNo`, `unitNo` | no |
| `changePhase()` → `setPhase()` | `gamemaster/game.php:1241-1324`, `:1144-1170` | `phase`, `turn` (+1 on new Diplomacy), `gameOver` | Redis `gameTurnPhase_<id>` via `Game::cacheTurnPhase()` `:1167` (key, not a publish) |
| `Members->findSetDefeated()` → `setDefeated()` | `gamemaster/members.php:147`, `gamemaster/member.php:429-449` | member `status='Defeated'`, `pointsWon` | no |
| `setWon()` | `gamemaster/game.php:1202-1233`, `gamemaster/members.php:394`, `gamemaster/member.php:472-482,297,412` | `finishTime`, `phase='Finished'`, `gameOver='Won'`, member `status` (`Won/Survived/Resigned`), `pointsWon`; identities become public (S4) | no |
| `cleanTerrStatus()` | `gamemaster/game.php:1123-1133` | `occupiedFromTerrID`, `standoff`, `retreatingUnitID` reset on new turn | no |
| Finished cleanup | `:839-847` | `wD_TerrStatus`, `wD_Units` deleted | no |
| `resetMinimumBet()` | `:538-566` | `minimumBet` | no |
| `generateOrders()` | `:886-920` | all members' `orderStatus` (`'None'` or `''`), `orderStatusChanged` | no |
| `resetProcessTime()` | `:639-644` | `processTime` | no |
| Bots-only auto draw | `:866-883` → `setDrawn()` | as `setDrawn` | no |
| `crashed()` | `:492-517` | `processStatus='Crashed'`, `processTime=NULL` | covered by `gamemaster.php:229-233,260-269` |

### 5.3 State changes **outside** the main processing loop (each needs its own `game.json` write)
| Trigger | file:line | Public fields changed | Publish / wipe today |
|---|---|---|---|
| Vote tally → `applyVote()` | `gamemaster/gamemaster.php:448-518` (called at `gamemaster.php:152`, before and outside the per-game loop), `gamemaster/game.php:42-57` | see next three rows | **none** — no `processed` publish, no `$gameUpdated` |
| `togglePause()` | `gamemaster/game.php:1337-1388` | `processStatus`, `pauseTimeRemaining`, `processTime`; all members' `votes` (Pause removed), `votesChanged` | none; no cache wipe |
| `setDrawn()` | `:1395-1438` | `finishTime`, `phase='Finished'`, `gameOver='Drawn'`, member `status='Drawn'`, `pointsWon`, archives copied, units/orders/terrstatus deleted | `cacheTurnPhase` via `setPhase`; `Game::wipeCache(id,turn)` `:1437`; no publish |
| `setConcede()` | `:1446-1478` | as setWon | `wipeCache` `:1476`; no publish |
| `setCancelled()` / `setAbandoned()` / `setNotEnoughPlayers()` → `eraseGame()` | `:62-97,380-392` | game deleted from all `wD_*` game tables | `wipeCache` `:391` (would also delete `game.json` if stored in the same folder — desirable) |
| `moveTurnBack()` (admin / sandbox owner via `sandbox/moveTurnBack`) | `:295-372` | `turn`, `phase='Diplomacy'`, `gameOver='No'`, `processTime`, `processStatus`, members `votes=''`, `orderStatus=''`, `status`; units/terrstatus/orders restored; archives of that turn deleted | `cacheTurnPhase` `:364`, `wipeCache` `:368`, Global message (publishes `messageSent`) |
| **Order save / ready via `ajax.php`** | `ajax.php:122-157` → `OrderInterface::writeOrderStatus()` `board/orders/orderinterface.php:256-276` | that member's `orderStatus`, `orderStatusChanged` (sandbox+Ready: all members) | **no publish**; only `Redis->append('processHint', ',<gameID>')` `ajax.php:148-156` |
| Order save / ready via API `game/orders` | `api.php:1346-1606` (status write `:1563-1567`) | same; additionally `:1416` sets the caller's own member row to `status='Playing', missedPhases=0, timeLoggedIn=now` (so a `Left` member submitting through the API flips back to `Playing` without `markBackFromLeft()`); keys with explicit permission may submit for a country in CD within 60 s of the deadline (`:1424-1438`, no status change) | no publish; `processHint` `:1600-1603` |
| Vote toggle on the board | `board.php:123-128` → `board/member.php:69-103` | member `votes`, `votesChanged` (all members in `MemberVsBots`) | **no overview publish**; the "Voted for X" note-to-self goes through `libGameMessage::send`, which skips `notify()` for self-messages (`lib/gamemessage.php:88-91`) but still publishes `messageSent` on the voter's *own* country channel (`:99-101`) — other players' clients hear nothing |
| Vote toggle via API | `api.php:498-549`, `:558-614` | member `votes`, `votesChanged` | `Redis->trigger("private-game<id>",'overview','set-vote')` `:545,610` |
| Message send | `lib/gamemessage.php:42-127` | (public only if `toCountryID=0`) new Global message; `lastmsgtime_*` keys; `newMessagesFrom` (private) | publishes `message`/`messageSent` on each recipient country channel `:93-102` (spectators have no channel; `lastmsgtime_<id>_0` is set for them) |
| Member page view | `board/member.php:36-59` | `timeLoggedIn` (every >3 min), `missedPhases` reset in memory only; `status Left→Playing` via `markBackFromLeft()` `objects/member.php:273-344` (+`orderStatus`, CD row removed, `resetMinimumBet`) | none. `timeLoggedIn` changes far too often to drive file rewrites — keep it out of G or refresh it lazily |
| `game/messagesseen`, `game/markbackfromleft` API | `api.php:671-695` (`newMessagesFrom`, `timeLoggedIn`), `:755+` | private / `status` | none |
| Join / take over CD | `gamemaster/members.php:468-566` (`processMember::create` `gamemaster/member.php:87-101`; takeover update `:533-536`), bets via `User::pointsTransfer` `objects/user.php:336-375` (`pot`, `bet`, `pointsWon`) | member list, `userID`, `status`, `orderStatus`, `pot`, `bet`, `minimumBet` | none; picked up by `findGamesWithRecentlyJoinedPlayers()` `gamemaster/gamemaster.php:567-590` |
| Leave (pre-game) | `gamemaster/member.php:40-79` | member deleted, `pot`, `minimumBet`, or game erased | none |
| Admin / director actions | `admin/adminActions.php:222` (`drawType`), `:243` (`pressType`), `:270` (`phaseMinutes`,`processTime`), `:295` (`missingPlayerPolicy`), `:325,365,621` (`processTime`), `:336,348` (`password`), `:432,517,607` (member removed / `userID` swapped), `:741-778` (`excusedMissedTurns`), `:870` (`minimumReliabilityRating`); `admin/adminActionsRestricted.php:362-369,953-974` (`processTime`,`processStatus`,`pauseTimeRemaining`); `admin/adminActionsSeniorMod.php:178` (`directorUserID`), `:519` (`userID`); `admin/adminActionsTD.php:244-498` (same set for tournament directors); `contactUsDirect.php:283-290` (emergency pause) | as listed | none |
| Background tasks | `gamemaster/backgroundTasks.php:311,333,337` (raw SQL sets `gameOver='Drawn', phase='Finished'` for stale bot/sandbox games), `:329` (`processTime`), `:341` (`status='Survived'`) | game over without going through `setDrawn()` | none, no cache wipe |
| Constructor self-heal | `objects/game.php:427-438` | `processTime=NULL, pauseTimeRemaining=600` for inconsistent paused games, on **any** Game load | none |
| Sandbox / bot game creation | `gamemaster/sandboxGame.php:55,64`, `botgamecreate.php:95,357` | new game + members | `processHint` |
| Game creation | `processGame::create()` `gamemaster/game.php:412-486` | new game row | none |

Observations for the design:
1. There is **no single choke point** for member-level changes (orderStatus, votes, join/leave, admin edits). A helper such as `Game::writePublicJSON($gameID)` called from: `gamemaster.php:254-268`, `findAndApplyGameVotes` after each `applyVote` commit (`gamemaster/gamemaster.php:512-513`), `OrderInterface::writeOrderStatus()` (covers `ajax.php` and `game/orders`), `userMember::toggleVote()` + the two API vote routes, `processMembers::join()`/`processMember::leave()`, `moveTurnBack()`, and the admin action handlers would cover the table above; alternatively keep per-request-volatile fields (`orderStatus`, `votes`) out of the file and serve them from `game/pulse`-style data.
2. Writes from `ajax.php`/`game/orders` happen under only a member-row lock, concurrently for different countries → the file write needs to be atomic (temp file + `rename`) and must re-read all members after COMMIT, otherwise two simultaneous Ready clicks can publish a file missing one of them.
3. For anon games `orderStatus` must be reduced to the `None` bit (S5) and Draw votes dropped when hidden (S9), so for those games a Ready click changes nothing public and the rewrite can be skipped.
4. `Game::wipeCache()` deletes `*.*` in the game folder; a `game.json` stored there is wiped on every process (`gamemaster.php:294`) and whenever a draw/concede/turn-back happens — write it **after** the wipe, or store it under a different glob/dir.
5. Board-side vote toggles and order-status changes currently emit no SSE event; if other clients are to refetch `game.json` on change, a publish (e.g. reuse `set-vote`, add `order-status`) has to be added at `board/member.php:101` and `orderinterface.php:269`.
