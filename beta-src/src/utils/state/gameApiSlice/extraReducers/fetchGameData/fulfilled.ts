import { current } from "@reduxjs/toolkit";
import TerritoryMap from "../../../../../data/map/variants/classic/TerritoryMap";
import Territory from "../../../../../enums/map/variants/classic/Territory";
import BoardClass from "../../../../../models/BoardClass";
import GameDataResponse from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import UnitType from "../../../../../types/UnitType";
import getTerritoriesMeta from "../../../../getTerritoriesMeta";
import getOrdersMeta from "../../../../map/getOrdersMeta";
import { getUnitsLive } from "../../../../map/getUnits";
import generateMaps from "../../../generateMaps";
import updateOrdersMeta from "../../../updateOrdersMeta";

/* eslint-disable no-param-reassign */
export default function fetchGameDataFulfilled(state: GameState, action): void {
  console.log("fetchGameDataFulfilled");
  state.apiStatus = "succeeded";
  state.data = action.payload;
  const currentState = current(state);
  const {
    data: { data },
    overview: { members, phase, user },
  } = currentState;
  let board;
  console.log({ contextVars: data.contextVars });
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

  state.territoriesMeta = getTerritoriesMeta(data);

  const numUnsavedOrders = Object.values(state.ordersMeta).reduce(
    (acc, meta) => acc + 1 - +meta.saved,
    0,
  );
  if (!numUnsavedOrders) {
    updateOrdersMeta(state, getOrdersMeta(data, board, phase));
  }
}
