import { store } from "../state/store";

// Rendering the full <App /> is not feasible under jsdom (the map needs a real
// SVG layout engine for getBBox/d3-zoom), so smoke-test the store bootstrap,
// which pulls in the whole game-api-slice reducer tree.
test("redux store initializes with the game slice", () => {
  const state = store.getState();
  expect(state).toHaveProperty("game");
  expect(state.game).toHaveProperty("apiStatus");
});
