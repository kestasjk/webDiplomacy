import * as React from "react";
import { Box, ButtonGroup } from "@mui/material";
import WDScrollButton from "./WDScrollButton";
import ScrollButtonState from "../../enums/ScrollButton";
import Season from "../../enums/Season";

interface WDPillScrollerProps {
  disabled?: ScrollButtonState | undefined;
  onChangeSeason?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  season: Season;
  year: number;
}

const WDPillScroller: React.FC<WDPillScrollerProps> = function ({
  disabled,
  onChangeSeason,
  season,
  year,
}): React.ReactElement {
  return (
    <Box
      sx={{
        alignItems: "flex-start",
        display: "flex",
        filter: "drop-shadow(0px 8px 9px black)",
        paddingTop: "5px",
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
            fontSize: 12,
            textTransform: "uppercase",
          }}
        >
          {`${season} ${year}`}
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
  onChangeSeason: undefined,
};

export default WDPillScroller;
