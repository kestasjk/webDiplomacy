import * as React from "react";
import { useState } from "react";
import { SvgIcon, Box } from "@mui/material";
import { ActionIcon, ActionIconSelected } from "./svgr-components";

const WDActionIcon: React.FC = function () {
  const [active, setActive] = useState(false);

  const highlightActionHandler = () => {
    setActive(!active);
  };

  return (
    <Box
      onClick={highlightActionHandler}
      // sx={{
      //   position: "absolute",
      // }}
    >
      {active && (
        <SvgIcon
          component={ActionIconSelected}
          inheritViewBox
          style={{
            filter: "drop-shadow(1px 10px 7px #737373)",
          }}
          sx={{
            height: 52,
            width: 53,
          }}
        />
      )}
      {!active && (
        <SvgIcon
          component={ActionIcon}
          style={{ filter: "drop-shadow(0 0 7px #323232)" }}
          inheritViewBox
          sx={{ margin: 1.5 }}
        />
      )}
    </Box>
  );
};

export default WDActionIcon;
