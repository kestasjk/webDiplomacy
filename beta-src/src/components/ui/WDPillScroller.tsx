import * as React from "react";
import Season from "../../enums/Season";

interface WDPillScrollerProps {
  country: string;
  viewedSeason: Season;
  viewedYear: number;
}

const WDPillScroller: React.FC<WDPillScrollerProps> = function ({
  country,
  viewedSeason,
  viewedYear,
}): React.ReactElement {
  return (
    <div className="flex items-center py-2 px-3 rounded-md text-white bg-opacity-60 bg-black font-bold select-none">
      {viewedSeason},{viewedYear}. {country}, Select a unit
    </div>
  );
};

export default WDPillScroller;
