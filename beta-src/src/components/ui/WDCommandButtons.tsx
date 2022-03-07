import * as React from "react";
import { useState } from "react";
import { Stack, Button } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";

/**
 * Define the default/select color theme
 */
const defaultStyle = {
  backgroundColor: "black",
  border: "2px solid black",
  color: "white",
  p: "10px",
  "&:hover": {
    backgroundColor: "black",
  },
};

const selectStyle = {
  backgroundColor: "white",
  border: "2px solid black",
  color: "black",
  p: "10px 8px 10px 8px",
};

const WDCommandButtons: React.FC = function () {
  const [draw, setDraw] = useState(false);
  const [pause, setPause] = useState(false);
  const [cancel, setCancel] = useState(false);

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <Button
        /**
         * the attribute color with value "primary" or "secondary" can also be applied as a different approach.
         * HOWEVER, when using the attribute color approach, the attribute variant with value "contained" has to be applied, which causes a clicking theme switching issue
         */
        onClick={() => {
          setDraw(!draw);
          /**
           * draw votting logic can be applied below
           */
          console.log("draw clicked");
        }}
        startIcon={draw ? <WDCheckmarkIcon /> : ""}
        sx={draw ? selectStyle : defaultStyle}
      >
        Draw
      </Button>
      <Button
        onClick={() => {
          setPause(!pause);
          /**
           * pause votting logic can be applied below
           */
          console.log("pause clicked");
        }}
        startIcon={pause ? <WDCheckmarkIcon /> : ""}
        sx={pause ? selectStyle : defaultStyle}
      >
        Pause
      </Button>
      <Button
        onClick={() => {
          setCancel(!cancel);
          /**
           * cancel votting logic can be applied below
           */
          console.log("cancel clicked");
        }}
        startIcon={cancel ? <WDCheckmarkIcon /> : ""}
        sx={cancel ? selectStyle : defaultStyle}
      >
        Cancel
      </Button>
    </Stack>
  );
};

export default WDCommandButtons;
