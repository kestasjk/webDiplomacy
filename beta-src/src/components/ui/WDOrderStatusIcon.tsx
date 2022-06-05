import * as React from "react";
import { Box } from "@mui/material";

import { Lock, PriorityHigh } from "@mui/icons-material";
import { OrderStatus } from "../../interfaces";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";

interface WDOrderStatusIconProps {
  orderStatus: OrderStatus;
}

const WDOrderStatusIcon: React.FC<WDOrderStatusIconProps> = function ({
  orderStatus,
}): React.ReactElement {
  console.log({ orderStatus });
  let inner = <Box />;
  if (orderStatus.Hidden) {
    inner = <Lock sx={{ fontSize: "16px", color: "#666" }} />;
  } else if (orderStatus.Saved || orderStatus.Ready) {
    inner = <WDCheckmarkIcon color={(orderStatus.Ready && "#0A0") || "#888"} />;
  } else {
    inner = <PriorityHigh sx={{ fontSize: "16px", color: "#A00" }} />;
  }
  return <Box sx={{ alignIterms: "center" }}>{inner}</Box>;
};

export default WDOrderStatusIcon;
