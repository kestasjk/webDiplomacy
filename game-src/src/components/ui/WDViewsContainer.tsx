import * as React from "react";
import WDTabGroup from "./WDTabGroup";
import ModalViews from "../../enums/ModalViews";

interface WDViewsContainerProps {
  children: React.ReactNode;
  currentView: ModalViews;
  onChange: (currentTab: ModalViews) => void;
  tabGroup: ModalViews[];
}

const WDViewsContainer: React.FC<WDViewsContainerProps> = function ({
  children,
  currentView,
  onChange,
  tabGroup,
}): React.ReactElement {
  return (
    <div>
      <WDTabGroup
        currentView={currentView}
        onChange={onChange}
        tabGroup={tabGroup}
      />
      {children}
    </div>
  );
};

export default WDViewsContainer;
