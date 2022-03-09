import * as React from "react";
import { useState } from "react";
import { Stack } from "@mui/material";
import WDButton from "./WDButton";

interface saveReadyProps {
  saveStatus: boolean;
  readyStatus: boolean;
}
const WDSaveReadyButtons: React.FC<saveReadyProps> = function ({
  saveStatus,
  readyStatus,
}) {
  const [saveSelect, setSaveSelect] = useState(saveStatus);
  const [readySelect, setReadySelect] = useState(readyStatus);

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color="primary"
        disabled={readySelect}
        onClick={() => {
          setSaveSelect(!saveSelect);
        }}
      >
        Save
      </WDButton>
      <WDButton
        color="primary"
        onClick={() => {
          setReadySelect(!readySelect);
        }}
      >
        {readySelect ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDSaveReadyButtons;
