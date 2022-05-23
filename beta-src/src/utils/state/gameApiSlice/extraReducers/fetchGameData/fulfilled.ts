import { current } from "@reduxjs/toolkit";
import { useEffect } from "react";
import TerritoryMap from "../../../../../data/map/variants/classic/TerritoryMap";
import Territory from "../../../../../enums/map/variants/classic/Territory";
import BoardClass from "../../../../../models/BoardClass";
import { GameCommand } from "../../../../../state/interfaces/GameCommands";
import GameDataResponse from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import UnitType from "../../../../../types/UnitType";
import getTerritoriesMeta from "../../../../getTerritoriesMeta";
import getOrdersMeta from "../../../../map/getOrdersMeta";
import getUnits from "../../../../map/getUnits";
import highlightMapTerritoriesBasedOnStatuses from "../../../../map/highlightMapTerritoriesBasedOnStatuses";
import generateMaps from "../../../generateMaps";
import setCommand from "../../../setCommand";
import updateOrdersMeta from "../../../updateOrdersMeta";

/* eslint-disable no-param-reassign */
export default function fetchGameDataFulfilled(state, action): void {
  // state.transition = false;
  state.apiStatus = "succeeded";
  state.data = action.payload;
  const {
    data: { data },
    overview: { members, phase, user },
  }: {
    data: { data: GameDataResponse["data"] };
    overview: {
      members: GameOverviewResponse["members"];
      phase: GameOverviewResponse["phase"];
      user: GameOverviewResponse["user"];
    };
  } = current(state);
  let board;
  if (data.contextVars) {
    board = new BoardClass(
      data.contextVars.context,
      Object.values(data.territories),
      data.territoryStatuses,
      Object.values(data.units),
    );
    state.board = board;
  }
  state.maps = generateMaps(data);
  state.ownUnits = [];
  Object.values(data.units).forEach((unit) => {
    if (unit.countryID === user.member.countryID.toString()) {
      state.ownUnits.push(unit.id);
    }
  });
  const unitsToDraw = getUnits(data, members, phase);
  Object.values(data.territories).forEach(({ name }) => {
    const mappedTerritory = TerritoryMap[name];
    const command: GameCommand = {
      command: "SET_UNIT",
      data: { setUnit: { unitSlotName: mappedTerritory.unitSlotName } },
    };
    setCommand(
      state,
      command,
      "territoryCommands",
      mappedTerritory.parent
        ? Territory[mappedTerritory.parent]
        : Territory[mappedTerritory.territory],
    );
  });
  unitsToDraw.forEach(({ country, mappedTerritory, unit }) => {
    const command: GameCommand = {
      command: "SET_UNIT",
      data: {
        setUnit: {
          componentType: "Game",
          country,
          mappedTerritory,
          unit,
          unitType: unit.type as UnitType,
          unitSlotName: mappedTerritory.unitSlotName,
        },
      },
    };
    setCommand(
      state,
      command,
      "territoryCommands",
      mappedTerritory.parent
        ? Territory[mappedTerritory.parent]
        : Territory[mappedTerritory.territory],
    );
  });

  state.territoriesMeta = getTerritoriesMeta(data);
  highlightMapTerritoriesBasedOnStatuses(state);
  updateOrdersMeta(state, getOrdersMeta(data, board, phase));
}
