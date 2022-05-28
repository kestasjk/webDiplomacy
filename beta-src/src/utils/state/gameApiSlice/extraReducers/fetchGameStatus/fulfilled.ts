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
export default function fetchGameStatusFulfilled(
  state: GameState,
  action,
): void {
  console.log("fetchGameStatusFulfilled");
  state.outstandingGameRequests = Math.max(
    state.outstandingGameRequests - 1,
    0,
  );
  state.apiStatus = "succeeded";

  // If the user is scrolled to the current phase, make the viewed
  // phase track the current phase
  if (state.viewedPhaseState.viewedPhaseIdx >= state.status.phases.length - 1) {
    state.viewedPhaseState.viewedPhaseIdx = action.payload.phases.length - 1;
  }

  state.status = action.payload;
  const {
    data: { data },
    overview: { members },
  }: {
    data: { data: GameDataResponse["data"] };
    overview: {
      members: GameOverviewResponse["members"];
    };
  } = current(state);
}
