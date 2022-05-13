import * as React from "react";
import { Box, Stack } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDButton from "./WDButton";

interface WDMessageListProps {
  children: React.ReactNode;
}

const WDMessageList: React.FC<WDMessageListProps> = function ({
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
  const spacing = mobileLandscapeLayout ? 1 : 2;

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

export default WDMessageList;
