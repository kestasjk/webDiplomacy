---
license: cc-by-4.0
pretty_name: webDiplomacy games
tags:
- diplomacy
- board-games
- strategy-games
configs:
- config_name: default
  data_files: data/games.jsonl.gz
---

# webDiplomacy games

Every finished game played on [webDiplomacy](https://webdiplomacy.net): settings, players, every phase's units,
supply centres and adjudicated orders, and the messages sent to all players. Regenerated at the start of each month
from the live site, holding the games finished before that month began.

## Files

| File | What it is |
|---|---|
| `data/games.jsonl.gz` | One game per line, as JSON |
| `data/variants.json` | The maps the games were played on: countries, territories and their borders |
| `data/manifest.json` | Which month the export is, how many games it holds, and when it was made |

Earlier months are in this dataset's history: pass `revision=<commit>` to `load_dataset`, or download a file from
an earlier commit.

## Loading it

```python
from datasets import load_dataset
games = load_dataset("DanielPBak/webdiplomacy-games", split="train")
```

```python
import gzip, json
with gzip.open("games.jsonl.gz", "rt", encoding="utf-8") as f:
    for line in f:
        game = json.loads(line)
```

```sql
-- DuckDB
SELECT gameID, variant, gameOver, len(phases) FROM read_json('games.jsonl.gz') WHERE pressType = 'NoPress';
```

## A game

Times are Unix seconds. Names of enumerations are spelled as webDiplomacy's database spells them.

| Field | |
|---|---|
| `gameID` | The game's ID on the site: `https://webdiplomacy.net/board.php?gameID=<gameID>` |
| `name` | The game's name, as its creator typed it |
| `variantID`, `variant` | The map; see `data/variants.json` |
| `gameOver` | `Won` or `Drawn` |
| `turn`, `turnText` | The last turn, e.g. `"Autumn, 1910"`. Turn 0 is Spring 1901 on the classic map; two turns per year |
| `startTime`, `finishTime` | |
| `phaseMinutes`, `phaseMinutesRB`, `nextPhaseMinutes`, `phaseSwitchPeriod` | Phase lengths in minutes: `phaseMinutes` for the game; `phaseMinutesRB` for retreats and builds, if different; and, in games that changed speed part way through, `nextPhaseMinutes` and when they switched to it. `-1` means not used |
| `pot`, `potType`, `minimumBet` | The points at stake and how they were shared out |
| `pressType` | `Regular` (private and public messages), `PublicPressOnly`, `NoPress`, `RulebookPress` |
| `anon` | Whether players' names were hidden while it was played |
| `drawType` | `draw-votes-public` or `draw-votes-hidden` |
| `missingPlayerPolicy`, `excusedMissedTurns`, `minimumReliabilityRating` | How missed turns were handled, and who could join |
| `playerTypes` | `Members`, `Mixed` (empty places filled with bots) or `MemberVsBots` |
| `isPrivate` | Whether it needed an invite code to join |
| `members` | One per country, below |
| `civilDisorders` | Players who left and were replaced: `playerID`, `countryID`, the `turn`, and their `supplyCenterNo` then |
| `phases` | Every phase, oldest first, below |
| `messages` | The messages sent to all players: `turn`, `phase`, `timeSent`, `fromCountryID` (0: the GameMaster or a moderator), and `message`, which is HTML-escaped as stored |
| `voteLog` | In games with public draw votes, each vote cast or withdrawn: `turn`, `phase`, `timeSent`, `countryID`, `vote` (`Draw`, `Pause`, `Cancel`, `Concede`) and `on` |

### Members

| Field | |
|---|---|
| `countryID`, `country` | `countryID` is 1-based, matching `data/variants.json` |
| `playerID` | The player, hashed: the same player has the same `playerID` in every game and every month's export, but it can't be turned back into their account |
| `isBot` | Whether the player is one of the site's bots |
| `status` | `Won`, `Drawn`, `Survived`, `Defeated`, `Resigned` or `Left` |
| `supplyCenterNo`, `unitNo` | At the end |
| `bet`, `pointsWon` | Points put in and won |
| `missedPhases`, `excusedMissedTurns` | |

### Phases

```json
{ "turn": 0, "phase": "Diplomacy", "turnText": "Spring, 1901",
  "units":   [ { "countryID": 1, "type": "Fleet", "terrID": 6, "retreating": false } ],
  "centers": [ { "terrID": 6, "countryID": 1 } ],
  "orders":  [ { "countryID": 1, "terrID": 6, "unitType": "Fleet", "type": "Move",
                 "toTerrID": 17, "fromTerrID": null, "viaConvoy": false,
                 "success": true, "dislodged": false } ] }
```

- `phase` is `Diplomacy`, `Retreats` or `Builds`. The last phase is `Finished`, with the final position and no orders.
- `units` and `centers` are as the phase began; `orders` are what was ordered in it, and how each was adjudicated.
- `centers` is who owned each territory, not only the supply centres: `countryID` 0 is nobody. Keep the territories
  with `supply: true` in `data/variants.json` for supply centre counts. The first phase lists every territory.
- A drawn game's last turn has no orders: the orders standing when it was drawn were never adjudicated.
- `type` is one of `Hold`, `Move`, `Support hold`, `Support move`, `Convoy`, `Retreat`, `Disband`, `Build Army`,
  `Build Fleet`, `Wait` and `Destroy`. `toTerrID` is where a unit moved or a support or convoy was to; `fromTerrID`
  is where the supported or convoyed unit came from.
- `terrID`s are the `id`s of the territories in `data/variants.json` for the game's variant. Coasts are territories of
  their own (`coast: "Child"`), whose `coastParentID` is the territory they belong to.

## What isn't here

- Usernames, points totals, and anything else that identifies a player's account
- Private messages, and so the press of `Regular` games is only their public part
- Games still being played, and sandbox games

## Licence

[CC BY 4.0](https://creativecommons.org/licenses/by/4.0/): use it as you like, crediting webDiplomacy.
