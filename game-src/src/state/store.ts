import { configureStore } from "@reduxjs/toolkit";
import gameApiSliceReducer from "./game/game-api-slice";

export const store = configureStore({
  reducer: {
    game: gameApiSliceReducer,
  },
  devTools: true,
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: false, // FIXME
    }),
});

export type AppDispatch = typeof store.dispatch;
export type RootState = ReturnType<typeof store.getState>;
