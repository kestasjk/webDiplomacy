/* 
A province is a bordered drawable region on the map.

The north/south coasts of STP, SPA, BUL are not considered
separate provinces.

A lot of the rules of the game are phrased in terms of provinces,
(e.g. only one unit per province except during retreat phases,
supports consider movement capability to the province, rather than
movement capability to the territory, ownership of supply centers
is at the province-level rather than the territory-level, etc).

So it is very common in movement and order logic to work with
provinces rather than territories.

When interacting with the game API, it is also common to work
with province IDs from the API. It is guaranteed that a 
provinceID numerically or stringwise matches the territoryID
of the root territory (i.e. non-special-coast territory) of the 
province.

Likewise, it is guaranteed that the Province enum for playable
provinces is stringwise equal to the Territory enum for
the root territory of that province.

However, we should try not to rely too heavily this mixing
up of these types if possible.

See GameStateMaps.ts for converting Provinces to/from IDs.
See ProvincesMapData for getting detailed data about the properties
of a province and what Territories it has within it.
*/

enum Province {
  ADRIATIC_SEA = "ADRIATIC_SEA",
  AEGEAN_SEA = "AEGEAN_SEA",
  ALBANIA = "ALBANIA",
  ANKARA = "ANKARA",
  APULIA = "APULIA",
  ARMENIA = "ARMENIA",
  BALTIC_SEA = "BALTIC_SEA",
  BARENTS_SEA = "BARENTS_SEA",
  BELGIUM = "BELGIUM",
  BERLIN = "BERLIN",
  BLACK_SEA = "BLACK_SEA",
  BOHEMIA = "BOHEMIA",
  BREST = "BREST",
  BUDAPEST = "BUDAPEST",
  BULGARIA = "BULGARIA",
  BURGUNDY = "BURGUNDY",
  CHANNEL_1 = "CHANNEL_1",
  CLYDE = "CLYDE",
  CONSTANTINOPLE = "CONSTANTINOPLE",
  DENMARK = "DENMARK",
  EASTERN_MEDITERRANEAN = "EASTERN_MEDITERRANEAN",
  EDINBURGH = "EDINBURGH",
  ENGLISH_CHANNEL = "ENGLISH_CHANNEL",
  FINLAND = "FINLAND",
  GALICIA = "GALICIA",
  GASCONY = "GASCONY",
  GREECE = "GREECE",
  GULF_OF_BOTHNIA = "GULF_OF_BOTHNIA",
  GULF_OF_LYONS = "GULF_OF_LYONS",
  HELIGOLAND_BIGHT = "HELIGOLAND_BIGHT",
  HOLLAND = "HOLLAND",
  IONIAN_SEA = "IONIAN_SEA",
  IRISH_SEA = "IRISH_SEA",
  KIEL = "KIEL",
  LIVERPOOL = "LIVERPOOL",
  LIVONIA = "LIVONIA",
  LONDON = "LONDON",
  MARSEILLES = "MARSEILLES",
  MIDDLE_ATLANTIC = "MIDDLE_ATLANTIC",
  MOSCOW = "MOSCOW",
  MUNICH = "MUNICH",
  NAPLES = "NAPLES",
  NEUTRAL_1 = "NEUTRAL_1",
  NEUTRAL_2 = "NEUTRAL_2",
  NEUTRAL_3 = "NEUTRAL_3",
  NEUTRAL_4 = "NEUTRAL_4",
  NEUTRAL_5 = "NEUTRAL_5",
  NEUTRAL_6 = "NEUTRAL_6",
  NEUTRAL_7 = "NEUTRAL_7",
  NEUTRAL_8 = "NEUTRAL_8",
  NEUTRAL_9 = "NEUTRAL_9",
  NORTH_AFRICA = "NORTH_AFRICA",
  NORTH_ATLANTIC = "NORTH_ATLANTIC",
  NORTH_ATLANTIC2 = "NORTH_ATLANTIC2",
  NORTH_SEA = "NORTH_SEA",
  NORWAY = "NORWAY",
  NORWEGIAN_SEA = "NORWEGIAN_SEA",
  PARIS = "PARIS",
  PICARDY = "PICARDY",
  PIEDMONT = "PIEDMONT",
  PORTUGAL = "PORTUGAL",
  PRUSSIA = "PRUSSIA",
  ROME = "ROME",
  RUHR = "RUHR",
  RUMANIA = "RUMANIA",
  SAINT_PETERSBURG = "SAINT_PETERSBURG",
  SERBIA = "SERBIA",
  SEVASTOPOL = "SEVASTOPOL",
  SILESIA = "SILESIA",
  SKAGERRACK = "SKAGERRACK",
  SKAGERRACK2 = "SKAGERRACK2",
  SMYRNA = "SMYRNA",
  SPAIN = "SPAIN",
  SWEDEN = "SWEDEN",
  SYRIA = "SYRIA",
  TRIESTE = "TRIESTE",
  TUNIS = "TUNIS",
  TUSCANY = "TUSCANY",
  TYROLIA = "TYROLIA",
  TYRRHENIAN_SEA = "TYRRHENIAN_SEA",
  UKRAINE = "UKRAINE",
  UNPLAYABLE_LAND1 = "UNPLAYABLE_LAND1",
  UNPLAYABLE_LAND2 = "UNPLAYABLE_LAND2",
  UNPLAYABLE_LAND3 = "UNPLAYABLE_LAND3",
  UNPLAYABLE_LAND4 = "UNPLAYABLE_LAND4",
  UNPLAYABLE_LAND5 = "UNPLAYABLE_LAND5",
  UNPLAYABLE_LAND6 = "UNPLAYABLE_LAND6",
  UNPLAYABLE_LAND7 = "UNPLAYABLE_LAND7",
  UNPLAYABLE_LAND8 = "UNPLAYABLE_LAND8",
  UNPLAYABLE_SEA1 = "UNPLAYABLE_SEA1",
  UNPLAYABLE_SEA2 = "UNPLAYABLE_SEA2",
  UNPLAYABLE_SEA3 = "UNPLAYABLE_SEA3",
  UNPLAYABLE_SEA4 = "UNPLAYABLE_SEA4",
  UNPLAYABLE_SEA5 = "UNPLAYABLE_SEA5",
  UNPLAYABLE_SEA6 = "UNPLAYABLE_SEA6",
  UNPLAYABLE_SEA7 = "UNPLAYABLE_SEA7",
  UNPLAYABLE_SEA8 = "UNPLAYABLE_SEA8",
  UNPLAYABLE_SEA9 = "UNPLAYABLE_SEA9",
  VENICE = "VENICE",
  VIENNA = "VIENNA",
  WALES = "WALES",
  WARSAW = "WARSAW",
  WESTERN_MEDITERRANEAN = "WESTERN_MEDITERRANEAN",
  YORK = "YORK",
}

export default Province;
