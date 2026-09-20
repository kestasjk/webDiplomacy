import { Button, Stack } from "@mui/material";
import * as React from "react";
import Season from "../../enums/Season";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import ViewedPhaseState from "../../state/interfaces/ViewedPhaseState";
import { formatPSYForDisplay } from "../../utils/formatPhaseForDisplay";
import {
  getGamePhaseSeasonYear,
  getHistoricalPhaseSeasonYear,
} from "../../utils/state/getPhaseSeasonYear";

// Various of the buttons draw with a Z_INDEX of 2
// So we set this overlay to a Z_INDEX of 4 to draw on top of them,
// so that the user isn't scrolling phases and playing with the save and ready buttons
// while this overlay sits on top.
const Z_INDEX = "4";

const centeredStyle = {
  top: "50%",
  left: "50%",
  // justifyContent: "center",
  // alignItems: "center",
  transform: "translate(-50%, -50%)",
  backgroundColor: "rgba(255,255,255,1)",
  padding: "10px",
  borderRadius: "5px",
  zIndex: Z_INDEX,
};

const overlayStyle = {
  left: 0,
  top: 0,
  height: "100%",
  width: "100%",
  backgroundColor: "rgba(52,52,52,0.6)",
  zIndex: Z_INDEX,
};

interface WDGameProgressOverlayProps {
  overview: GameOverviewResponse;
  status: GameStatusResponse;
  viewedPhaseState: ViewedPhaseState;
  clickHandler: () => void;
}

const WDGameProgressOverlay: React.FC<WDGameProgressOverlayProps> = function ({
  overview,
  status,
  viewedPhaseState,
  clickHandler,
}) {
  let innerElem;
  if (
    ["Diplomacy", "Retreats", "Builds", "Finished"].includes(overview.phase)
  ) {
    if (status.status === "Left") {
      innerElem = (
        <Stack direction="column" alignItems="center">
          <div className="m-2">
            You failed to enter orders and had no excused absences.
          </div>
          <div className="m-2">
            You are in Civil Disorder
            {overview.isTempBanned
              ? " and cannot rejoin due to a temp ban"
              : ""}
            .
          </div>

          <Button
            size="large"
            variant="contained"
            color="success"
            onClick={clickHandler}
            disabled={overview.isTempBanned}
          >
            Rejoin Game
          </Button>
        </Stack>
      );
    } else if (status.phases.length <= 1) {
      innerElem = (
        <Stack direction="column" alignItems="center">
          <div className="m-2">Game progressed to a new phase...</div>
          <Button
            size="large"
            variant="contained"
            color="success"
            onClick={clickHandler}
          >
            View{" "}
            {formatPSYForDisplay({
              phase: overview.phase,
              season: overview.season as Season,
              year: overview.year,
            })}
          </Button>
        </Stack>
      );
    }
  } else if (overview.phase === "Pre-game") {
    innerElem = (
      <div>
        <b>Pre-game:</b> Game is waiting to start
      </div>
    );
  } else if (overview.phase === "Error") {
    innerElem = <div>Could not load game. You may need to join this game.</div>;
  } else {
    innerElem = <div>Game phase is {overview.phase}!</div>;
  }
  return (
    <>
      <div className="absolute" style={overlayStyle} />
      <div className="absolute" style={centeredStyle}>
        {innerElem}
      </div>
    </>
  );
};

export default WDGameProgressOverlay;
