import * as React from "react";
import Country from "../../enums/Country";
import webDiplomacyTheme from "../../webDiplomacyTheme";

interface CountryHighlightFilter {
  country: Country;
  floodOpacity?: number;
}

const countryHighlightFilterConfig: CountryHighlightFilter[] = [
  {
    country: Country.AUSTRIA,
  },
  {
    country: Country.ENGLAND,
  },
  {
    country: Country.FRANCE,
  },
  {
    country: Country.GERMANY,
  },
  {
    country: Country.ITALY,
  },
  {
    country: Country.RUSSIA,
    floodOpacity: 0.85,
  },
  {
    country: Country.TURKEY,
  },
];

const WDCountryHighlightFilterDefs = function (): React.ReactElement[] {
  return countryHighlightFilterConfig.map((filter: CountryHighlightFilter) => (
    <filter id={filter.country} key={filter.country}>
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
        floodColor={webDiplomacyTheme.palette[`${filter.country}`].main}
        floodOpacity={filter.floodOpacity || 1}
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
