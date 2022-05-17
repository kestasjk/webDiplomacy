import { configureStore } from "@reduxjs/toolkit";
import gameApiSliceReducer from "./game/game-api-slice";

export const store = configureStore({
  reducer: {
    game: gameApiSliceReducer,
  },
  devTools:
    process.env.REACT_APP_WD_BASE_URL !== "https://www.webdiplomacy.net",
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: false,
    }),
});

export type AppDispatch = typeof store.dispatch;
export type RootState = ReturnType<typeof store.getState>;
