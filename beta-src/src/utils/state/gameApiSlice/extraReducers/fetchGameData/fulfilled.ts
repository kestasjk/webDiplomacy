import { current } from "@reduxjs/toolkit";
import Territory from "../../../../../enums/map/variants/classic/Territory";
import BoardClass from "../../../../../models/BoardClass";
import { GameCommand } from "../../../../../state/interfaces/GameCommands";
import GameDataResponse from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import UnitType from "../../../../../types/UnitType";
import getOrdersMeta from "../../../../map/getOrdersMeta";
import getUnits from "../../../../map/getUnits";
import generateMaps from "../../../generateMaps";
import setCommand from "../../../setCommand";
import updateOrdersMeta from "../../../updateOrdersMeta";

/* eslint-disable no-param-reassign */
export default function fetchGameDataFulfilled(state, action): void {
  state.apiStatus = "succeeded";
  state.data = action.payload;
  const {
    data: { data },
    overview: { members, phase },
  }: {
    data: { data: GameDataResponse["data"] };
    overview: {
      members: GameOverviewResponse["members"];
      phase: GameOverviewResponse["phase"];
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
    console.log({
      newBoard2: board,
    });
    state.board = board;
  }
  state.maps = generateMaps(data);
  const {
    maps,
    data: {
      data: { territories },
    },
  }: {
    maps: GameState["maps"];
    data: { data: GameDataResponse["data"] };
  } = current(state);
  data.currentOrders?.forEach(({ unitID, id }) => {
    const terr = Object.values(territories).find(
      (t) => t.id === maps.unitToTerritory[unitID],
    );
    console.log(
      `Unit ID: ${unitID} - Order ID: ${id} - Terr ID: ${terr?.name}`,
    );
    state.ownUnits.push(unitID);
  });
  const unitsToDraw = getUnits(data, members);
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

  updateOrdersMeta(state, getOrdersMeta(data, board, phase));
}
