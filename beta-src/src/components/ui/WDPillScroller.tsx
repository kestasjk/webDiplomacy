import * as React from "react";
import { Box, ButtonGroup } from "@mui/material";
import WDScrollButton from "./WDScrollButton";
import ScrollButtonState from "../../enums/ScrollButton";
import Season from "../../enums/Season";

interface gameStateProps {
  onChangeSeason: React.MouseEventHandler<HTMLButtonElement> | undefined;
  season: [Season, number];
  disabled?: ScrollButtonState | undefined;
}

const WDPillScroller: React.FC<gameStateProps> = function ({
  disabled,
  season,
  onChangeSeason,
}): React.ReactElement {
  return (
    <Box
      sx={{
        alignItems: "center",
        display: "flex",
        filter: "drop-shadow(0px 8px 9px black)",
      }}
    >
      <ButtonGroup>
        <WDScrollButton
          className="WDScroll--Backward"
          direction={ScrollButtonState.BACKWARD}
          disabled={disabled === ScrollButtonState.BACKWARD}
          onClick={onChangeSeason}
        />
        <Box
          sx={{
            alignItems: "center",
            bgcolor: "secondary.main",
            display: "flex",
            fontWeight: "bold",
            padding: "5",
            textTransform: "uppercase",
          }}
        >
          {`${season[0]} ${season[1]}`}
        </Box>
        <WDScrollButton
          className="WDScroll--Forward"
          direction={ScrollButtonState.FORWARD}
          disabled={disabled === ScrollButtonState.FORWARD}
          onClick={onChangeSeason}
        />
      </ButtonGroup>
    </Box>
  );
};

WDPillScroller.defaultProps = {
  disabled: undefined,
};

export default WDPillScroller;
