import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import ApiRoute from "../../enums/ApiRoute";
import {
  getGameApiRequest,
  postGameApiRequest,
  submitOrders,
} from "../../utils/api";
import GameDataResponse from "../interfaces/GameDataResponse";
import GameErrorResponse from "../interfaces/GameErrorResponse";
import GameOverviewResponse from "../interfaces/GameOverviewResponse";
import { ApiStatus, GameState } from "../interfaces/GameState";
import GameStatusResponse from "../interfaces/GameStatusResponse";
import GameMessages from "../interfaces/GameMessages";
import ViewedPhaseState from "../interfaces/ViewedPhaseState";
import { RootState } from "../store";
import initialState from "./initial-state";
import OrdersMeta from "../interfaces/SavedOrders";
import OrderState from "../interfaces/OrderState";
import mergeMessageArrays from "../../utils/state/mergeMessageArrays";
import updateOrder from "../../utils/state/updateOrder";
import updateOrdersMeta from "../../utils/state/updateOrdersMeta";
import UpdateOrdersMetaAction from "../../interfaces/state/UpdateOrdersMetaAction";
import SavedOrdersConfirmation from "../../interfaces/state/SavedOrdersConfirmation";
import OrderSubmission from "../../interfaces/state/OrderSubmission";
import resetOrder from "../../utils/state/resetOrder";
import processMapClick from "../../utils/state/gameApiSlice/reducers/processMapClick";
import fetchGameDataFulfilled from "../../utils/state/gameApiSlice/extraReducers/fetchGameData/fulfilled";
import updateUserActivity from "../../utils/state/gameApiSlice/reducers/updateUserActivity";
import TerritoriesMeta from "../interfaces/TerritoriesState";
import fetchGameOverviewFulfilled from "../../utils/state/gameApiSlice/extraReducers/fetchGameOverview/fulfilled";
import fetchGameStatusFulfilled from "../../utils/state/gameApiSlice/extraReducers/fetchGameStatus/fulfilled";
import saveOrdersFulfilled from "../../utils/state/gameApiSlice/extraReducers/saveOrders/fulfilled";
import shallowArraysEqual from "../../utils/shallowArraysEqual";

export const fetchGameData = createAsyncThunk(
  ApiRoute.GAME_DATA,
  async (queryParams: { countryID?: string; gameID: string }) => {
    const { data } = await getGameApiRequest(ApiRoute.GAME_DATA, queryParams);
    console.log("fetchGameData");
    console.log(data);
    return data as GameDataResponse;
  },
);

export const fetchGameOverview = createAsyncThunk(
  ApiRoute.GAME_OVERVIEW,
  async (queryParams: { gameID: string }) => {
    const {
      data: { data },
    } = await getGameApiRequest(ApiRoute.GAME_OVERVIEW, queryParams);
    console.log("fetchGameOverview");
    console.log(data);
    return data as GameOverviewResponse;
  },
);

export const fetchGameStatus = createAsyncThunk(
  ApiRoute.GAME_STATUS,
  async (queryParams: { countryID: string; gameID: string }) => {
    const { data } = await getGameApiRequest(ApiRoute.GAME_STATUS, queryParams);
    console.log("fetchGameStatus");
    console.log(data);
    return data as GameStatusResponse;
  },
);

export const fetchGameMessages = createAsyncThunk(
  ApiRoute.GAME_MESSAGES,
  async (queryParams: {
    gameID: string;
    countryID: string;
    toCountryID?: string;
    offset?: string;
    limit?: string;
    allMessages?: string;
    sinceTime?: string;
  }) => {
    const {
      data: { data },
    } = await getGameApiRequest(
      ApiRoute.GAME_MESSAGES,
      queryParams,
      // set a 60 second timeout.
      // Timeout is important because we rate-limit to
      // one outstanding request at a time.
      60000,
    );
    return data as GameMessages;
  },
);

export const sendMessage = createAsyncThunk(
  ApiRoute.SEND_MESSAGE,
  async (queryParams: {
    gameID: string;
    countryID: string;
    toCountryID: string;
    message: string;
  }) => {
    const response = await postGameApiRequest(
      ApiRoute.SEND_MESSAGE,
      queryParams,
    );
    return response.data as unknown as GameMessages;
  },
);

export const toggleVoteStatus = createAsyncThunk(
  ApiRoute.GAME_TOGGLEVOTE,
  async (queryParams: { countryID: string; gameID: string; vote: string }) => {
    const { data } = await getGameApiRequest(
      ApiRoute.GAME_TOGGLEVOTE,
      queryParams,
    );
    return data as string;
  },
);

export const markMessagesSeen = createAsyncThunk(
  ApiRoute.MESSAGES_SEEN,
  async (queryParams: {
    countryID: string;
    gameID: string;
    seenCountryID: string;
  }) => {
    const { data } = await getGameApiRequest(
      ApiRoute.MESSAGES_SEEN,
      queryParams,
    );
    return data as string;
  },
);

export const saveOrders = createAsyncThunk(
  "game/submitOrders",
  async (data: OrderSubmission) => {
    const formData = new FormData();
    formData.set("orderUpdates", JSON.stringify(data.orderUpdates));
    formData.set("context", data.context);
    formData.set("contextKey", data.contextKey);
    const response = await submitOrders(formData, data.queryParams);
    console.log({ response });
    const confirmation: string = response.headers["x-json"] || "";
    const parsed: SavedOrdersConfirmation = JSON.parse(
      confirmation.substring(1, confirmation.length - 1),
    );
    return parsed;
  },
);

export const loadGameData =
  (gameID: string, countryID: string) => async (dispatch) => {
    console.log("loadGameData");
    await Promise.all([
      dispatch(fetchGameData({ gameID, countryID })),
      // dispatch(fetchGameMessages({ gameID, countryID, allMessages: "true" })),
      dispatch(fetchGameStatus({ gameID, countryID })),
    ]);
  };

export const loadGame = (gameID: string) => async (dispatch) => {
  console.log("loadGame");
  const {
    payload: {
      user: {
        member: { countryID },
      },
    },
  } = await dispatch(
    fetchGameOverview({
      gameID,
    }),
  );
  await Promise.all([
    dispatch(fetchGameData({ gameID, countryID })),
    dispatch(fetchGameMessages({ gameID, countryID, allMessages: "true" })),
    dispatch(fetchGameStatus({ gameID, countryID })),
  ]);
};

/**
 * createSlice handles state changes properly without reassiging state, but
 * eslint does not know this. therefore, no-param-reassign is disabled for
 * the createSlice block of code below or functions therein.
 */

/* eslint-disable no-param-reassign */

const gameApiSlice = createSlice({
  name: "game",
  initialState,
  reducers: {
    resetOrder,
    updateUserActivity,
    updateOrder(state, action) {
      updateOrder(state, action.payload);
    },
    updateOrdersMeta(state, action: UpdateOrdersMetaAction) {
      updateOrdersMeta(state, action.payload);
    },
    updateTerritoriesMeta(state, action) {
      state.territoriesMeta = action.payload;
    },
    processMapClick,
    processMessagesSeen(state, action) {
      state.messages.newMessagesFrom = state.messages.newMessagesFrom.filter(
        (e) => e !== action.payload,
      );
    },
    updateOutstandingMessageRequests(state, action) {
      state.outstandingMessageRequests += action.payload;
    },
    setNeedsGameData(state, action) {
      state.activity.needsGameData = action.payload;
    },
    changeViewedPhaseIdxBy(state, action) {
      let newIdx = state.viewedPhaseState.viewedPhaseIdx + action.payload;
      newIdx = Math.min(newIdx, state.status.phases.length - 1);
      newIdx = Math.max(newIdx, 0);
      state.viewedPhaseState.viewedPhaseIdx = newIdx;
    },
  },
  extraReducers(builder) {
    builder
      // fetchGameData
      .addCase(fetchGameData.pending, (state) => {
        console.log("fetchGameData pending!");

        state.apiStatus = "loading";
      })
      .addCase(fetchGameData.fulfilled, fetchGameDataFulfilled)
      .addCase(fetchGameData.rejected, (state, action) => {
        console.log("fetchGameData rejected!");
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // fetchGameOverview
      .addCase(fetchGameOverview.pending, (state) => {
        state.apiStatus = "loading";
        state.activity.makeNewCall = false;
      })
      .addCase(fetchGameOverview.fulfilled, fetchGameOverviewFulfilled)
      .addCase(fetchGameOverview.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // fetchGameStatus
      .addCase(fetchGameStatus.pending, (state) => {
        state.apiStatus = "loading";
      })
      .addCase(fetchGameStatus.fulfilled, fetchGameStatusFulfilled)
      .addCase(fetchGameStatus.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // saveOrders
      .addCase(saveOrders.fulfilled, saveOrdersFulfilled)
      // Send message
      .addCase(sendMessage.fulfilled, (state, action) => {
        if (action.payload) {
          const { messages } = action.payload;
          const allMessages = mergeMessageArrays(
            state.messages.messages,
            messages,
          );
          state.messages.messages = allMessages;
        }
      })
      .addCase(sendMessage.rejected, (state, action) => {
        state.apiStatus = "failed";
        console.log(`sendMessages failed: ${action.error.message}`);
        state.error = action.error.message;
      })
      // Fetch Game Messages
      .addCase(fetchGameMessages.rejected, (state, action) => {
        state.apiStatus = "failed";
        console.log(`fetchGameMessages failed: ${action.error.message}`);
        state.outstandingMessageRequests = Math.max(
          state.outstandingMessageRequests - 1,
          0,
        );
        state.error = action.error.message;
      })
      .addCase(fetchGameMessages.fulfilled, (state, action) => {
        state.outstandingMessageRequests = Math.max(
          state.outstandingMessageRequests - 1,
          0,
        );
        if (action.payload) {
          const { messages, newMessagesFrom, time } = action.payload;
          if (messages) {
            const allMessages = mergeMessageArrays(
              state.messages.messages,
              messages,
            );
            if (state.messages.messages.length !== allMessages.length) {
              state.messages.messages = allMessages;
            }
          }
          if (newMessagesFrom) {
            // Only use the new newMessagesFrom if it has distinct values
            // Otherwise, use the old value to preserve reference equality
            // so that selectors recognize nothing changed and less of the UI
            // needs to redraw
            if (
              !shallowArraysEqual(
                state.messages.newMessagesFrom,
                newMessagesFrom,
              )
            ) {
              state.messages.newMessagesFrom = newMessagesFrom;
            }
          }
          console.log(
            `time=${time}, outstandingMessageRequests=${state.outstandingMessageRequests}`,
          );
          if (time) {
            state.messages.time = time;
          }
        }
      });
  },
});
/* eslint-enable no-param-reassign */

export const gameApiSliceActions = gameApiSlice.actions;

export const gameApiStatus = ({ game: { apiStatus } }: RootState): ApiStatus =>
  apiStatus;
export const gameData = ({ game: { data } }: RootState): GameDataResponse =>
  data;
export const gameError = ({ game: { error } }: RootState): GameErrorResponse =>
  error;
export const gameOverview = ({
  game: { overview },
}: RootState): GameOverviewResponse => overview;
export const gameStatus = ({
  game: { status },
}: RootState): GameStatusResponse => status;
export const gameOrdersMeta = ({
  game: { ordersMeta },
}: RootState): OrdersMeta => ordersMeta;
export const gameOrder = ({ game: { order } }: RootState): OrderState => order;
export const gameUserActivity = ({
  game: { activity },
}: RootState): GameState["activity"] => activity;
// gameMessages considered harmful, because part of the GameMessages object is a
// counter that tracks the last query timestamp, which means that if you use this
// selector rather than a more specific one, your component will update basically
// every time the server is queries for messages, regardless of whether
// the messages changed or not.
// export const gameMessages = ({ game: { messages } }: RootState): GameMessages =>
//  messages;
export const gameTerritoriesMeta = ({
  game: { territoriesMeta },
}: RootState): TerritoriesMeta => territoriesMeta;
export const gameMaps = ({ game: { maps } }: RootState) => maps;
export const gameBoard = ({ game: { board } }: RootState) => board;
export const gameViewedPhase = ({
  game: { viewedPhaseState },
}: RootState): ViewedPhaseState => viewedPhaseState;

export default gameApiSlice.reducer;
