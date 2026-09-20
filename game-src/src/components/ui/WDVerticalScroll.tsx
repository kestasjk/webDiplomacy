import React, { FunctionComponent, ReactElement } from "react";

interface WDVerticalScrollProps {
  children: ReactElement | ReactElement[];
  height?: number;
}

const WDVerticalScroll: FunctionComponent<WDVerticalScrollProps> = function ({
  children,
  height,
}: WDVerticalScrollProps): ReactElement {
  return (
    <div className="flex flex-col w-full mb-4" style={{ height }}>
      <div className="overflow-auto">{children}</div>
    </div>
  );
};

WDVerticalScroll.defaultProps = {
  height: 480,
};

export default WDVerticalScroll;
