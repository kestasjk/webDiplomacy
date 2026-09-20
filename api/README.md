API Documentation

### HOW-TO

To make a call to API, you must send a request to page `api.php` with:
- GET parameter `route` to indicate API entry.
- Other parameters related to API entry in either GET or POST format (depending on API entry type).
- API access key in HTTP request header.
  - Example: `Authorization: Bearer <API_KEY>`
  - To get an API key, please contact web site administrator.

### HOW A CLIENT READS A GAME

Everything about a game that is public — its settings and members, the units on the board, every completed
turn, the global messages and the draw vote log — is in static JSON files the site writes when the game
changes. They need no API key, and they are the same files for every reader, so they can be fetched and
cached freely.

Everything private to one player — their own orders, their private messages, their votes and order status,
and a token for live updates — comes from one authenticated route, `game/playercontext`, which also says
where that game's files are and which version of each is current.

So a client:

1. calls `game/playercontext` with no `gameID` to list the games its key plays, each with its file URLs and
   versions;
2. fetches the files whose version it doesn't already hold;
3. calls `game/playercontext` for one game when it needs that player's own orders or messages;
4. submits with `game/orders`, `game/sendmessage` and `game/togglevote`.

A file URL is relative to the site root, and a version is an opaque string that changes whenever the file
does. Fetch it as `<site>/<url>?v=<version>`: the query makes each version its own URL, so nothing in
between can hand back an older copy. Asking `game/playercontext` about a game rewrites any of its files that
are out of date, so a file is never stale for longer than the call that reported it.

The full specification of the files and this route, including how a live board follows changes over SSE, is
in `doc/gamedata/02-spec.md`.

#### The files

| File | Contents |
|---|---|
| `game.json` | The settings, the members (with usernames unless the game is anonymous), the units on the board now, territory ownership and civil disorders |
| `status.json` | Each member's order status and votes for the turn being played |
| `history.json` | Every phase: its units, centers and the orders given, in the order they were played. The last phase is the one in progress |
| `messages.json` | The global messages and, in a public-draw-votes game, the vote log |
| `variants/<Variant>/cache/variant.json` | The variant's territories, borders and coasts. It changes only when the variant is reinstalled |

An anonymous game's files carry no usernames and no per-country order status while it is running, exactly as
the site's own board shows it.

### API ENTRIES

#### `game/playercontext`

* Type: `GET`
* Description: Everything the calling user can see about a game that the public files don't carry: its own
  member row, orders, private messages, and where the game's files are. With no `gameID`, the same thing for
  every game the user is a member of, without the orders and messages.
* Parameters:
  * `gameID`: ID of game. Leave it out for the list of the user's games.
  * `countryID`: optional; only for a user who holds more than one country in the game (a sandbox).
  * `orders`: `1` to include the user's current orders, and the token needed to submit them. Only with a
    `gameID`.
  * `messages`: `1` to include the user's private messages. Only with a `gameID`.
  * `messagesSince`: only return messages sent after this unix timestamp. Use the `lastMessageTime` of the
    previous call, which trails the clock slightly so that nothing is missed.
  * `messageTurns`: how many turns back to read messages for. The default is the current turn.
  * `sbToken`: a sandbox game's token, for a sandbox the caller doesn't own.
* Return:
  * On error, a non-200 status code with the error has the body.
  * On success, a JSON object.
* URL example: `api.php?route=game/playercontext&gameID=5&orders=1&messages=1`
* Return example (trimmed):
```
{
   "schema": 1,
   "game": {
      "gameID": 5, "realGameID": 5, "variantID": 1, "name": "A game",
      "turn": 1, "phase": "Diplomacy", "gameOver": "No",
      "processTime": 1789873387, "processStatus": "Not-processing",
      "pressType": "Regular", "potType": "Unranked", "phaseMinutes": 60
   },
   "files": {
      "variant": {"url": "variants/Classic/cache/variant.json", "version": "6aaf3edb"},
      "game":    {"url": "cache/games/0/5/game.json",    "version": "6aaf3edbc517b8"},
      "status":  {"url": "cache/games/0/5/status.json",  "version": "6aaf3edffce256"},
      "history": {"url": "cache/games/0/5/history.json", "version": "6aaf3edb76d17a"},
      "messages":{"url": "cache/games/0/5/messages.json","version": "6aaf3edf0aed7d"}
   },
   "viewer": {"userID": 12, "isMember": true, "isModerator": false, "isDirector": false,
              "isSandboxOwner": false},
   "member": {
      "countryID": 2, "memberID": 8, "status": "Playing",
      "orderStatus": ["Completed"], "votes": ["Draw"],
      "missedPhases": 0, "excusedMissedTurns": 4, "bet": 5,
      "newMessagesFrom": [3], "mutedCountryIDs": [],
      "ordersPending": true, "lastMessageTime": 1789869791
   },
   "sseAuth": "ce61600137c47acba149364453d16277_1789871995",
   "orders": {
      "context": "{\"gameID\":5, ... }",
      "contextKey": "91387dce567a77cc...",
      "orders": [ {"id": 32, "countryID": 2, "unitID": 23, "type": "Hold", ...} ]
   },
   "messages": {
      "messages": [
         {"id": 1, "turn": 1, "phase": "Diplomacy", "timeSent": 1789869790,
          "fromCountryID": 2, "toCountryID": 3, "message": "Hello"}
      ]
   }
}
```
* The list form, `api.php?route=game/playercontext`, returns `{"schema": 1, "games": [ ... ]}`, where each
  row has the game, the caller's `countryID`, `status`, `orderStatus`, `votes`, `newMessagesFrom`,
  `lastMessageTime` and that game's `files`.
* For a multiplexed key (one key playing several accounts), `gameID` is the multiplexed ID as with the other
  routes, and `realGameID` is the game's own ID.

#### `game/orders`

* Type: `POST`
* Description: Submits orders to a game for a specific country.
* Parameters: None
* POST body (JSON):
  * `gameID`: ID of game.
  * `turn`: game turn number for which the orders are submitted.
  * `phase`: phase type of turn for which the orders are submitted.
  * `countryID`: ID of country in targeted game.
  * `ready`: string to tell if game member a ready (`Yes`) or not (`No`) to submit orders. Wait flag.
  * `orders`: array of JSON order objects. An order object must have following fields:
    * `type`: order type.
    * `terrID`: ID of territory to order. Required to identify corresponding placeholder-order in database.
    * `fromTerrID`: required for some order types.
    * `toTerrID`: required for some order types.
    * `viaConvoy`: required for some order types.
* **Order object required fields per order type**:

| Type           | Fields                               |
|----------------|--------------------------------------|
| `Hold`         | `type, terrID`                       |
| `Move`         | `type, terrID, toTerrID, viaConvoy`  |
| `Support hold` | `type, terrID, toTerrID`             |
| `Support move` | `type, terrID, fromTerrID, toTerrID` |
| `Convoy`       | `type, terrID, fromTerrID, toTerrID` |
| `Retreat`      | `type, terrID, toTerrID`             |
| `Disband`      | `type, terrID`                       |
| `Build Army`   | `type, terrID, toTerrID`             |
| `Build Fleet`  | `type, terrID, toTerrID`             |
| `Wait`         | `type`                               |
| `Destroy`      | `type, terrID, toTerrID`             |
|----------------|--------------------------------------|
* Return:
  * On error, a non-200 status code with the error has the body.
  * On success, a JSON object containing the list of orders on the server
* Return example:
```
[
      {
         "turn":0,
         "phase":"Diplomacy",
         "countryID":3,
         "terrID":15,
         "unitType":"Army",
         "type":"Hold",
         "toTerrID":0,
         "fromTerrID":0,
         "viaConvoy":"No",
      }, ...
   ]
```

#### `game/sendmessage`

* Type: `POST` (JSON)
* Description: Sends a message in a game with press.
* POST body (JSON):
  * `gameID`: ID of game.
  * `countryID`: ID of the country sending, which must be the caller's.
  * `toCountryID`: ID of the country to send to, or `0` for a global message.
  * `message`: the text.
* Return: on success, `{"messages": [{"fromCountryID": 1, "toCountryID": 2, "message": "Hello",
  "timeSent": 1789872077, "turn": "0"}]}`. A global message also appears in `messages.json`; a private one
  comes back from `game/playercontext` with `messages=1`.

#### `game/togglevote`

* Type: `GET`
* Description: Turns one of the caller's votes on or off.
* Parameters:
  * `gameID`: ID of game.
  * `countryID`: ID of the caller's country in that game.
  * `vote`: one of `Draw`, `Pause`, `Cancel`, `Concede`.
* Return: on success, the country's votes after the change as a comma-separated list, which may have a
  leading comma (`,Pause`), and is empty when it has none. The caller's votes are also in
  `game/playercontext`; in a public-draw-votes game every country's draw votes are in the vote log in
  `messages.json`.

### ROUTES REMOVED ON 2026-09-20

| Removed | Use instead |
|---|---|
| `game/status` | `game.json` and `history.json` for the board and the turns, `game/playercontext` for the caller's own orders and status |
| `game/getmessages` | `messages.json` for global messages, `game/playercontext?messages=1` for private ones |
| `players/missing_orders` | the list form of `game/playercontext`: a game needs orders when its `orderStatus` has no `Ready`/`Completed` and its `processStatus` is `Not-processing` |
| `players/cd` | nothing. Taking over a country in civil disorder through the API had not worked since 2022 |
| `players/active_games`, `players/pulse`, `game/pulse` | the list form of `game/playercontext` |
| `game/overview`, `game/data`, `game/members` | `game.json`, `status.json`, `variant.json` |
