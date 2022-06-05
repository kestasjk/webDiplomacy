import * as React from "react";
import { Stack } from "@mui/material";
import ModalViews from "../../enums/ModalViews";
import WDTabItem from "./WDTabItem";

interface WDTabGroupProps {
  currentView: ModalViews;
  onChange: (currentTab: ModalViews) => void;
  padding: string;
  tabGroup: ModalViews[];
}

const WDTabGroup: React.FC<WDTabGroupProps> = function ({
  currentView,
  onChange,
  padding,
  tabGroup,
}): React.ReactElement {
  const tabList = tabGroup.map((currentTab) => {
    return (
      <WDTabItem
        currentTab={currentTab}
        currentView={currentView}
        key={currentTab}
        onChange={onChange}
      />
    );
  });

  return (
    <Stack alignItems="center" direction="row" spacing={2} sx={{ p: padding }}>
      {tabList}
    </Stack>
  );
};

export default WDTabGroup;
