import * as React from "react";
import { Box } from "@mui/material";
import WDTabGroup from "./WDTabGroup";
import ModalViews from "../../enums/ModalViews";

interface WDViewsContainerProps {
  children: React.ReactNode;
  currentView: ModalViews;
  onChange: (currentTab: ModalViews) => void;
  padding: string;
  tabGroup: ModalViews[];
}

const WDViewsContainer: React.FC<WDViewsContainerProps> = function ({
  children,
  currentView,
  onChange,
  padding,
  tabGroup,
}): React.ReactElement {
  return (
    <Box>
      <WDTabGroup
        currentView={currentView}
        onChange={onChange}
        padding={padding}
        tabGroup={tabGroup}
      />
      {children}
    </Box>
  );
};

export default WDViewsContainer;
