import * as React from "react";
import { SvgIcon, Box } from "@mui/material";
import HomeIcon from "./svgr-components/HomeIcon";

const WDHomeIcon: React.FC = function () {
  return (
    <Box component="a" href="https://webdiplomacy.net/">
      <SvgIcon
        component={HomeIcon}
        style={{ filter: "drop-shadow(0 0 7px #323232)" }}
        inheritViewBox
        sx={{ margin: 1.5 }}
      />
    </Box>
  );
};

export default WDHomeIcon;
