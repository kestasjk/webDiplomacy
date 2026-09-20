import React, { FC, ReactElement } from "react";

import { Lock, PriorityHigh } from "@mui/icons-material";
import { OrderStatus } from "../../interfaces";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";

interface WDOrderStatusIconProps {
  // eslint-disable-next-line react/require-default-props
  isHidden: boolean;
  orderStatus: OrderStatus;
}

const WDOrderStatusIcon: FC<WDOrderStatusIconProps> = function ({
  isHidden = false,
  orderStatus,
}): ReactElement {
  if (isHidden) {
    return <Lock sx={{ fontSize: "16px", color: "#666" }} />;
  }
  if (orderStatus.Saved || orderStatus.Ready) {
    return <WDCheckmarkIcon color={(orderStatus.Ready && "#0A0") || "#888"} />;
  }
  return <PriorityHigh sx={{ fontSize: "16px", color: "#C00" }} />;
};

export default WDOrderStatusIcon;
