import * as React from "react";
import { Box } from "@mui/material";

interface WDLineClampProps {
  children: React.ReactNode;
  lineHeight: string;
  maxHeight: string;
  WebkitLineClamp: number;
}

const WDLineClamp: React.FC<WDLineClampProps> = function ({
  children,
  lineHeight,
  maxHeight,
  WebkitLineClamp,
}): React.ReactElement {
  return (
    <Box
      sx={{
        display: "-webkit-box",
        lineHeight,
        maxHeight,
        overflow: "hidden",
        textOverflow: "ellipsis",
        WebkitBoxOrient: "vertical",
        WebkitLineClamp,
        wordWrap: "break-word",
      }}
    >
      {children}
    </Box>
  );
};

export default WDLineClamp;
