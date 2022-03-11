import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import ApiRoute from "../../enums/ApiRoutes";
import { getGameApiRequest } from "../../utils/api";
import { RootState } from "../store";
import initialState from "./initial-state";

export const fetchGameOverview = createAsyncThunk(
  ApiRoute.GAME_OVERVIEW,
  async (queryParams: { gameID: string }) => {
    const response = await getGameApiRequest(
      ApiRoute.GAME_OVERVIEW,
      queryParams,
    );
    return response.data;
  },
);

export const fetchGameStatus = createAsyncThunk(
  ApiRoute.GAME_STATUS,
  async (queryParams: { countryID: string; gameID: string }) => {
    const response = await getGameApiRequest(ApiRoute.GAME_STATUS, queryParams);
    return response.data;
  },
);

/**
 * createSlice handles state changes properly without reassiging state, but
 * eslint does not know this. therefore, no-param-reassign is disabled for
 * the createSlice block of code below.
 */

/* eslint-disable no-param-reassign */
const gameApiSlice = createSlice({
  name: "game",
  initialState,
  reducers: {
    getGameOverview(state, action) {
      state.overview = action.payload;
    },
    getGameStatus(state, action) {
      state.status = action.payload;
    },
  },
  extraReducers(builder) {
    builder
      // fetchGameOverview
      .addCase(fetchGameOverview.pending, (state) => {
        state.apiStatus = "loading";
      })
      .addCase(fetchGameOverview.fulfilled, (state, action) => {
        state.apiStatus = "succeeded";
        state.overview = action.payload;
      })
      .addCase(fetchGameOverview.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // fetchGameStatus
      .addCase(fetchGameStatus.pending, (state) => {
        state.apiStatus = "loading";
      })
      .addCase(fetchGameStatus.fulfilled, (state, action) => {
        state.apiStatus = "succeeded";
        state.status = action.payload;
      })
      .addCase(fetchGameStatus.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      });
  },
});
/* eslint-enable no-param-reassign */

export const { getGameOverview, getGameStatus } = gameApiSlice.actions;
export const gameOverview = ({ game: { overview } }: RootState) => overview;
export const gameStatus = ({ game: { status } }: RootState) => status;
export default gameApiSlice.reducer;
