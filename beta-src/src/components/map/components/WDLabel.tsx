import { useTheme } from "@mui/material";
import * as React from "react";
import { Label } from "../../../interfaces";

interface WDLabelProps extends Label {
  id?: string;
}

const WDLabel: React.FC<WDLabelProps> = function ({
  id,
  style,
  text,
  x,
  y,
}): React.ReactElement {
  const theme = useTheme();
  return (
    <text
      key={id}
      className="label"
      style={{
        fill: theme.palette.primary.main,
        fontWeight: 900,
        fontSize: "150%",
        userSelect: "none",
        ...style,
      }}
      x={x}
      y={y}
      id={id}
    >
      {text}
    </text>
  );
};

WDLabel.defaultProps = {
  id: undefined,
};

export default WDLabel;
