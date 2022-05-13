import * as React from "react";
import { Box } from "@mui/material";
import { GameMessage } from "../../state/interfaces/GameMessages";

interface WDMessageProps {
  message: GameMessage;
}

const WDMessage: React.FC<WDMessageProps> = function ({
  message,
}): React.ReactElement {
  return (
    <Box id={`message-${String(message.timeSent)}`} bgcolor="0xaaaaaa">
      {message.message}
    </Box>
  );
};

export default WDMessage;
