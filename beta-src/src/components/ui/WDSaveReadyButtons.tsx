import * as React from "react";
import { useState } from "react";
import { Stack } from "@mui/material";
import WDButton from "./WDButton";

const WDSaveReadyButtons: React.FC = function () {
  const [saveSelect, setSaveSelect] = useState(false);
  const [readySelect, setReadySelect] = useState(false);

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color="primary"
        disabled={readySelect}
        onClick={() => {
          setSaveSelect(!saveSelect);
        }}
        variant="contained"
      >
        <span>Save</span>
      </WDButton>
      <WDButton
        color="primary"
        onClick={() => {
          setReadySelect(!readySelect);
        }}
        variant="contained"
      >
        <span>{readySelect ? "Unready" : "Ready"}</span>
      </WDButton>
    </Stack>
  );
};

export default WDSaveReadyButtons;
