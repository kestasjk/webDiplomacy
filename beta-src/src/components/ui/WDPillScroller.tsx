import * as React from "react";
import { Box, ButtonGroup, useTheme } from "@mui/material";
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
  const theme = useTheme();
  return (
    <Box
      sx={{
        alignItems: "flex-start",
        display: "flex",
        backgroundColor: "white",
        borderRadius: "22px",
        filter: theme.palette.svg.filters.dropShadows[0],
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
