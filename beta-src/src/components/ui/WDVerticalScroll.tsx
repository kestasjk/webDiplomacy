import * as React from "react";
import { Box } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";

const WDVerticalScroll: React.FC = function ({ children }): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const height = "350px";

  return (
    <Box
      sx={{
        m: "0px 0 10px 0",
        width: "100%",
        height,
        display: "flex",
        flexDirection: "column",
      }}
    >
      <Box sx={{ overflow: "auto" }}>{children}</Box>
    </Box>
  );
};

export default WDVerticalScroll;
