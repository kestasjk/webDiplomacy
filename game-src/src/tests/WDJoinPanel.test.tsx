import * as React from "react";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import gameApiSliceReducer from "../state/game/game-api-slice";
import initialState from "../state/game/initial-state";
import GameOverviewResponse from "../state/interfaces/GameOverviewResponse";
import WDJoinPanel from "../components/ui/WDJoinPanel";
import ApiRoute from "../enums/ApiRoute";
import { postGameApiFormRequest } from "../utils/api";

jest.mock("../utils/api", () => ({
  postGameApiFormRequest: jest.fn(),
}));
const post = postGameApiFormRequest as jest.Mock;

const countries = [
  "England",
  "France",
  "Italy",
  "Germany",
  "Austria",
  "Turkey",
  "Russia",
];

const canJoin = {
  canJoin: true,
  cantJoinReason: null,
  bet: 5,
  needsInviteCode: false,
  minimumReliabilityRating: 0,
  civilDisorderCountryIDs: [],
};

function renderPanel(overview: Partial<GameOverviewResponse>) {
  const store = configureStore({
    reducer: { game: gameApiSliceReducer },
    preloadedState: {
      game: {
        ...initialState,
        overview: {
          ...initialState.overview,
          gameID: 10,
          phase: "Pre-game",
          phaseMinutes: 1440,
          playerTypes: "Members",
          variant: { ...initialState.overview.variant, countries },
          ...overview,
        },
      },
    },
    middleware: (getDefaultMiddleware) =>
      getDefaultMiddleware({ serializableCheck: false }),
  });
  return render(
    <Provider store={store}>
      <WDJoinPanel />
    </Provider>,
  );
}

const reload = jest.fn();
beforeEach(() => {
  post.mockReset();
  reload.mockReset();
  window.confirm = jest.fn(() => true);
  Object.defineProperty(window, "location", {
    writable: true,
    value: { href: "", reload },
  });
});

test("shows nothing when there is nothing to offer", () => {
  const { container } = renderPanel({ join: null, leave: null });
  expect(container).toBeEmptyDOMElement();
});

test("joins a pre-game game and reloads as a member", async () => {
  post.mockResolvedValue({ data: {} });
  renderPanel({ join: canJoin, leave: null });

  expect(screen.getByText("5 points")).toBeInTheDocument();
  fireEvent.click(screen.getByRole("button", { name: "Join" }));

  await waitFor(() => expect(reload).toHaveBeenCalled());
  expect(post).toHaveBeenCalledWith(ApiRoute.JOIN_GAME, { gameID: "10" });
  expect(window.confirm).toHaveBeenCalledWith(
    expect.stringContaining("start when all 7 players have joined"),
  );
});

test("does nothing if the join isn't confirmed", () => {
  window.confirm = jest.fn(() => false);
  renderPanel({ join: canJoin, leave: null });
  fireEvent.click(screen.getByRole("button", { name: "Join" }));
  expect(post).not.toHaveBeenCalled();
});

test("needs the invite code for a private game, and shows a refusal", async () => {
  post.mockRejectedValue({
    isAxiosError: true,
    response: {
      data: "The invite code you supplied is incorrect, please try again.",
    },
  });
  renderPanel({ join: { ...canJoin, needsInviteCode: true }, leave: null });

  const join = screen.getByRole("button", { name: "Join" });
  expect(join).toBeDisabled();
  fireEvent.change(screen.getByLabelText(/Invite code/), {
    target: { value: "wrong" },
  });
  fireEvent.click(join);

  expect(
    await screen.findByText(
      "The invite code you supplied is incorrect, please try again.",
    ),
  ).toBeInTheDocument();
  expect(post).toHaveBeenCalledWith(ApiRoute.JOIN_GAME, {
    gameID: "10",
    gamepass: "wrong",
  });
  expect(reload).not.toHaveBeenCalled();
});

test("takes over the chosen country in civil disorder", async () => {
  post.mockResolvedValue({ data: {} });
  renderPanel({
    phase: "Diplomacy",
    join: { ...canJoin, bet: 0, civilDisorderCountryIDs: [1, 3] },
    leave: null,
  });

  fireEvent.change(screen.getByLabelText(/Take over/), {
    target: { value: "3" },
  });
  fireEvent.click(screen.getByRole("button", { name: "Take over" }));

  await waitFor(() => expect(reload).toHaveBeenCalled());
  expect(window.confirm).toHaveBeenCalledWith(
    "Are you sure you want to take over Italy?",
  );
  expect(post).toHaveBeenCalledWith(ApiRoute.JOIN_GAME, {
    gameID: "10",
    countryID: "3",
  });
});

test("says why the user can't join", () => {
  renderPanel({
    join: {
      ...canJoin,
      canJoin: false,
      cantJoinReason: "You have too few points to join this game.",
    },
    leave: null,
  });
  expect(
    screen.getByText("(You have too few points to join this game.)"),
  ).toBeInTheDocument();
  expect(screen.queryByRole("button")).not.toBeInTheDocument();
});

test("leaves a pre-game game and goes home", async () => {
  post.mockResolvedValue({ data: {} });
  renderPanel({ join: null, leave: { canLeave: true, cantLeaveReason: null } });

  fireEvent.click(screen.getByRole("button", { name: "Leave game" }));

  await waitFor(() => expect(window.location.href).toBe("/"));
  expect(post).toHaveBeenCalledWith(ApiRoute.LEAVE_GAME, { gameID: "10" });
});

test("says why a member can't leave", () => {
  renderPanel({
    join: null,
    leave: { canLeave: false, cantLeaveReason: "game starting soon" },
  });
  expect(
    screen.getByText("(Can't leave game; game starting soon.)"),
  ).toBeInTheDocument();
  expect(screen.queryByRole("button")).not.toBeInTheDocument();
});
