import { current } from "@reduxjs/toolkit";
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
import generateMaps from "../../../generateMaps";
import setCommand from "../../../setCommand";
import updateOrdersMeta from "../../../updateOrdersMeta";

/* eslint-disable no-param-reassign */
export default function fetchGameDataFulfilled(state, action): void {
  console.log("fetchGameDataFulfilled");
  console.log(action);
  if (state.commands.unitCommands.length) {
    // FIXME
    console.log("fetchGameDataFulfilled BAIL");

    return;
  }
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
    // FIXME: can't put non-serializable object in store
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
  const unitsToDraw = getUnits(data, members);
  console.log("unitsToDraw");
  console.log(unitsToDraw);
  console.log(data.territories);

  state.territoriesMeta = getTerritoriesMeta(data);

  // FIXME: figure out what's here
  updateOrdersMeta(state, getOrdersMeta(data, board, phase));
}
