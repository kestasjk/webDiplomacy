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
import updateOrdersMeta from "../../../updateOrdersMeta";
import { getLegalOrders } from "./precomputeLegalOrders";

/* eslint-disable no-param-reassign */
export default function fetchGameDataFulfilled(state: GameState, action): void {
  state.apiStatus = "succeeded";
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
  if (!numUnsavedOrders) {
    console.log("Updating ordersMeta");
    updateOrdersMeta(state, getOrdersMeta(data, phase));
  }
}
