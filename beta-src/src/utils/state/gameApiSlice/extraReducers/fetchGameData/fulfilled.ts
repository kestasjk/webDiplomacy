import { current } from "@reduxjs/toolkit";
import TerritoryMap from "../../../../../data/map/variants/classic/TerritoryMap";
import Territory from "../../../../../enums/map/variants/classic/Territory";
import GameDataResponse from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import UnitType from "../../../../../types/UnitType";
import getTerritoriesMeta from "../../../../getTerritoriesMeta";
import getOrdersMeta from "../../../../map/getOrdersMeta";
import { getUnitsLive } from "../../../../map/getUnits";
import generateMaps from "../../../generateMaps";
import getPhaseKey from "../../../getPhaseKey";
import updateOrdersMeta from "../../../updateOrdersMeta";
import { getLegalOrders } from "./precomputeLegalOrders";

/* eslint-disable no-param-reassign */
export default function fetchGameDataFulfilled(state: GameState, action): void {
  state.apiStatus = "succeeded";

  const oldPhaseKey = getPhaseKey(
    state.data.data.contextVars?.context,
    "<BAD OLD_DATA_KEY>",
  );
  const newPhaseKey = getPhaseKey(
    action.payload.data.contextVars?.context,
    "<BAD NEW_DATA_KEY>",
  );
  // console.log(`fetchGameDataFulfilled  ${oldPhaseKey} -> ${newPhaseKey}`);

  // Upon phase change, sweep away all orders from the previous turn
  if (oldPhaseKey !== newPhaseKey) {
    state.ordersMeta = {};
  }

  state.data = action.payload;
  const currentState = current(state);
  const {
    data: { data },
    overview: { phase, user },
  } = currentState;

  state.maps = generateMaps(data);
  state.ownUnits = [];
  Object.values(data.units).forEach((unit) => {
    if (unit.countryID === user.member.countryID.toString()) {
      state.ownUnits.push(unit.id);
    }
  });

  state.territoriesMeta = getTerritoriesMeta(data);

  state.legalOrders = getLegalOrders(state.overview, data, state.maps);

  const numUnsavedOrders = Object.values(state.ordersMeta).reduce(
    (acc, meta) => acc + 1 - +meta.saved,
    0,
  );
  // If all orders are saved, then update them from queries.
  // If not all orders are saved, then we want to keep the current UI state rather than
  // grabbing the new orders from the server.
  if (numUnsavedOrders === 0) {
    updateOrdersMeta(state, getOrdersMeta(data, phase));
  }
}
