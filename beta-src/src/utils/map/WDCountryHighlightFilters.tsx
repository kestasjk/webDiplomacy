import * as React from "react";
import webDiplomacyTheme from "../../webDiplomacyTheme";

interface CountryHighlightFilter {
  countryName: string;
  floodColor: string;
  floodOpacity?: number;
}

const countryHighlightFilterConfig: CountryHighlightFilter[] = [
  {
    countryName: "austria",
    floodColor: webDiplomacyTheme.palette.Austria.main,
  },
  {
    countryName: "england",
    floodColor: webDiplomacyTheme.palette.England.main,
  },
  {
    countryName: "france",
    floodColor: webDiplomacyTheme.palette.France.main,
  },
  {
    countryName: "germany",
    floodColor: webDiplomacyTheme.palette.Germany.main,
  },
  {
    countryName: "italy",
    floodColor: webDiplomacyTheme.palette.Italy.main,
  },
  {
    countryName: "russia",
    floodColor: webDiplomacyTheme.palette.Russia.main,
    floodOpacity: 0.85,
  },
  {
    countryName: "turkey",
    floodColor: webDiplomacyTheme.palette.Turkey.main,
  },
];

const WDCountryHighlightFilterDefs = function (): React.ReactElement[] {
  return countryHighlightFilterConfig.map((country: CountryHighlightFilter) => (
    <filter id={country.countryName} key={country.countryName}>
      <feOffset dx="5" dy="5" in="SourceGraphic" result="shadow1" />
      <feOffset dx="-5" dy="-5" in="SourceGraphic" result="shadow2" />
      <feMerge result="offset-blur">
        <feMergeNode in="shadow1" />
        <feMergeNode in="shadow2" />
        <feMergeNode in="SourceGraphic" />
      </feMerge>
      <feGaussianBlur stdDeviation="7.5" result="offset-blur" />
      <feComposite
        operator="out"
        in="SourceGraphic"
        in2="offset-blur"
        result="inverse"
      />
      <feFlood
        floodColor={country.floodColor}
        floodOpacity={country.floodOpacity || 1}
        result="color"
      />
      <feComposite operator="in" in="color" in2="inverse" result="shadow" />
      <feComponentTransfer in="shadow" result="shadow">
        <feFuncA type="linear" slope="2.5" />
      </feComponentTransfer>
    </filter>
  ));
};

export default WDCountryHighlightFilterDefs;
