import { useTheme } from "@mui/material";
import * as React from "react";
import { Label } from "../../../interfaces";

interface WDLabelProps extends Label {
  id?: string;
}

const WDLabel: React.FC<WDLabelProps> = function ({
  x,
  y,
  text,
  id,
  style,
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
