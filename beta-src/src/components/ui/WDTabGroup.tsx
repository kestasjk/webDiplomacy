import * as React from "react";
import ModalViews from "../../enums/ModalViews";
import WDTabItem from "./WDTabItem";

interface WDTabGroupProps {
  currentView: ModalViews;
  onChange: (currentTab: ModalViews) => void;
  tabGroup: ModalViews[];
}

const WDTabGroup: React.FC<WDTabGroupProps> = function ({
  currentView,
  onChange,
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

  return <div className="flex space-x-3 px-3 sm:px-4">{tabList}</div>;
};

export default WDTabGroup;
