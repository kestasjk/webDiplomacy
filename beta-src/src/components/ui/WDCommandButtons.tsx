import * as React from "react";
import { useState } from "react";
import { Stack, Button } from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";

const defaultStyle = {
  backgroundColor: "black",
  border: "2px solid black",
  color: "white",
  transition: ".5s",
  p: 1.5,
  height: 30,
  borderRadius: 15,
  "&:hover": {
    backgroundColor: "black",
  },
};

const selectStyle = {
  backgroundColor: "white",
  border: "2px solid black",
  color: "black",
  transition: ".5s",
  p: 1.5,
  height: 30,
  borderRadius: 15,
};

const WDCommandButtons: React.FC = function () {
  const [draw, setDraw] = useState(false);
  const [pause, setPause] = useState(false);
  const [cancel, setCancel] = useState(false);

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <Button
        sx={draw ? selectStyle : defaultStyle}
        onClick={() => setDraw(!draw)}
        startIcon={draw ? <CheckIcon style={{ fontSize: "1rem" }} /> : ""}
      >
        Draw
      </Button>
      <Button
        sx={pause ? selectStyle : defaultStyle}
        onClick={() => setPause(!pause)}
        startIcon={pause ? <CheckIcon style={{ fontSize: "1rem" }} /> : ""}
      >
        Pause
      </Button>
      <Button
        sx={cancel ? selectStyle : defaultStyle}
        onClick={() => setCancel(!cancel)}
        startIcon={cancel ? <CheckIcon style={{ fontSize: "1rem" }} /> : ""}
      >
        Cancel
      </Button>
    </Stack>
  );
};

export default WDCommandButtons;
