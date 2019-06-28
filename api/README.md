API Documentation

### HOW-TO

To make a call to API, you must send a request to page `api.php` with:
- GET parameter `route` to indicate API entry.
- Other parameters related to API entry in either GET or POST format (depending on API entry type).
- API access key in HTTP request header.
  - Example: `Authorization: Bearer <API_KEY>`
  - To get an API key, please contact web site administrator.

### API ENTRIES

#### `players/cd`

* Type: `GET`
* Description: Retrieves list of games in civil disorder waiting for orders.
* Parameters: None
* Return:
  * On error, a non-200 status code with the error has the body.
  * On success, a JSON array of objects with 2 fields:
    * `gameID`: ID of game.
    * `countryID`: ID of country waiting for orders.
* URL example: `api.php?route=players/cd`
* Return example:
```
[
  {"gameID":5,"countryID":1},
  {"gameID":5,"countryID":1},
  {"gameID":5,"countryID":1},
  {"gameID":5,"countryID":5}, ...
]
```

#### `players/missing_orders`

* Type: `GET`
* Description: Retrieves list of games where player has not yet submitted orders.
* Parameters: None
* Return:
  * On error, a non-200 status code with the error has the body.
  * On success, a JSON array of objects with 2 fields:
    * `gameID`: ID of game.
    * `countryID`: ID of country waiting for orders.
* URL example: `api.php?route=players/missing_orders`
* Return example:
```
[
  {"gameID":5,"countryID":1},
  {"gameID":5,"countryID":1},
  {"gameID":5,"countryID":1},
  {"gameID":5,"countryID":5}, ...
]
```

#### `game/status`

* Type: `GET`
* Description: Retrieves the status of a country in a game.
* Parameters:
  * `gameID`: ID of game.
  * `countryID`: ID of country in targeted game.
* Return:
  * On error, a non-200 status code with the error has the body.
  * On success, a JSON object containing game country status.
* URL example: `api.php?route=game/status&gameID=5&countryID=2`
* Return example:
```
{
   "gameID":5,
   "countryID":2,
   "variantID":1,
   "turn": 1,
   "phase":"Diplomacy",
   "gameOver":"No",
   "standoffs": [],
   "occupiedFrom": {72: 32, 42: 28, ...},
   "units":[
      {
         "unitType":"Army",
         "terrID":47,
         "countryID":2,
         "retreating":"No"
      }, ...
   ],
   "centers": [
        {"terrID":5, "countryID":1},
        {"terrID":24, "countryID":1},
        ...
   ],
   "orders":[
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
         "success":"No",
         "dislodged":"No"
      }, ...
   ]
}
```

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