import * as React from "react";
import { Box } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";

interface WDPressProps {
  children: React.ReactNode;
}

const WDPress: React.FC<WDPressProps> = function ({
  children,
}): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;
  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";
  const width = mobileLandscapeLayout ? 272 : 358;

  return (
    <Box
      sx={{
        m: "20px 0 10px 0",
        p: padding,
        width,
      }}
    >
      {children}
    </Box>
  );
};

export default WDPress;
