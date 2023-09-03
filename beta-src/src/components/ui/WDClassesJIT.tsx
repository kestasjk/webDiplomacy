import React, { FunctionComponent, ReactElement } from "react";

const WDClassesJIT: FunctionComponent = function (): ReactElement {
  return (
    <>
      {/* This are the classes that wouldn't show because they were used  */}
      <div className="hidden bottom-14 bottom-4 bottom-3 bottom-6 bottom-8 bottom-10 bottom-40 text-france-main text-france-light text-austria-main text-austria-light text-england-main text-england-light text-germany-main text-germany-light text-russia-main text-russia-light text-italy-main text-italy-light text-turkey-main text-turkey-light bg-france-main bg-austria-main bg-austria-light bg-england-main bg-england-light bg-germany-main bg-germany-light bg-russia-main bg-russia-light bg-italy-main bg-italy-light bg-turkey-main bg-turkey-light" />
    </>
  );
};

export default WDClassesJIT;
