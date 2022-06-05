import * as React from "react";
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
  if (orderStatus.Hidden) {
    return <Lock sx={{ fontSize: "16px", color: "#666" }} />;
  }
  if (orderStatus.Saved || orderStatus.Ready) {
    return <WDCheckmarkIcon color={(orderStatus.Ready && "#0A0") || "#888"} />;
  }
  return <PriorityHigh sx={{ fontSize: "16px", color: "#A00" }} />;
};

export default WDOrderStatusIcon;
