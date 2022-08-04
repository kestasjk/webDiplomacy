import * as React from "react";

const WDVerticalScroll: React.FC = function ({ children }): React.ReactElement {
  return (
    <div className="flex flex-col w-full h-[350px] mb-4">
      <div className="overflow-auto">{children}</div>
    </div>
  );
};

export default WDVerticalScroll;
