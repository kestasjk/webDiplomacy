import * as React from "react";
import { Box } from "@mui/material";
import Device from "../../enums/Device";

interface WDPressProps {
  children: React.ReactNode;
  device: Device;
}

const WDPress: React.FC<WDPressProps> = function ({
  children,
  device,
}): React.ReactElement {
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE || device === Device.MOBILE_LG_LANDSCAPE;
  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";
  const minimumWidth = mobileLandscapeLayout ? 265 : 375;

  return (
    <Box
      sx={{
        m: "20px 0 10px 0",
        p: padding,
        minWidth: minimumWidth,
      }}
    >
      {children}
    </Box>
  );
};

export default WDPress;
