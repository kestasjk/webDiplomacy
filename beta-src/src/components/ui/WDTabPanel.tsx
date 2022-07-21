import * as React from "react";
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
    <div
      className={currentView !== currentTab ? "hidden" : ""}
      id={currentTab}
      aria-labelledby={currentTab}
    >
      {currentView === currentTab && <div>{children}</div>}
    </div>
  );
};

export default WDTabPanel;
