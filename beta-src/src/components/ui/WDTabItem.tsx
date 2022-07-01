import * as React from "react";
import { Button } from "@mui/material";
import ModalViews from "../../enums/ModalViews";

const textButtonStyle = {
  borderRadius: 0,
  fontWeight: 400,
  minWidth: 0,
  p: "0 0 5px 0",
  "&:hover": {
    background: "transparent",
  },
};

const textButtonSelected = {
  borderRadius: 0,
  borderBottom: "solid black 2px",
  fontWeight: 600,
  minWidth: 0,
  p: "0 0 5px 0",
  "&:hover": {
    background: "transparent",
  },
};

interface WDTabItemProps {
  currentTab: ModalViews;
  currentView: ModalViews;
  onChange: (currentTab: ModalViews) => void;
}

const WDTabItem: React.FC<WDTabItemProps> = function ({
  currentTab,
  currentView,
  onChange,
}): React.ReactElement {
  return (
    <Button
      onClick={() => onChange(currentTab)}
      sx={currentView === currentTab ? textButtonSelected : textButtonStyle}
    >
      {currentTab}
    </Button>
  );
};

export default WDTabItem;
