import * as React from "react";
import { Box } from "@mui/material";
import ModalViews from "../../enums/ModalViews";

interface WDTabPanelProps {
  currentTab: ModalViews;
  currentView: ModalViews;
  children: React.ReactNode;
}

const WDTabPanel: React.FC<WDTabPanelProps> = function ({
  currentTab,
  currentView,
  children,
}): React.ReactElement {
  return (
    <Box
      hidden={currentView !== currentTab}
      id={currentTab}
      aria-labelledby={currentTab}
    >
      {currentView === currentTab && <Box>{children}</Box>}
    </Box>
  );
};

export default WDTabPanel;
