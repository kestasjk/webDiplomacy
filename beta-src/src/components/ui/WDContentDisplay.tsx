import * as React from "react";
import { Box } from "@mui/material";

interface WDContentDisplayProps {
  content: string;
  WebkitLineClamp: number;
  lineHeight: string;
  maxHeight: string;
}

const WDContentDisplay: React.FC<WDContentDisplayProps> = function ({
  content,
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
        wordWrap: "break-word",
        WebkitLineClamp,
        WebkitBoxOrient: "vertical",
      }}
    >
      {content}
    </Box>
  );
};

export default WDContentDisplay;
