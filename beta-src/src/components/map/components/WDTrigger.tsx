import * as React from "react";

const WDTrigger: React.FC = function (): React.ReactElement {
  return (
    <rect
      className="trigger"
      height="100%"
      width="100%"
      fill="black"
      style={{ opacity: 0 }}
    />
  );
};

export default WDTrigger;
