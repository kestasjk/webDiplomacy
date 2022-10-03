import * as React from "react";
import { useWindowSize } from "react-use";
import { CountryTableData } from "../../interfaces/CountryTableData";
import BetIcon from "./icons/country-table/WDBet";
import CentersIcon from "./icons/country-table/WDCenters";
import PowerIcon from "./icons/country-table/WDPower";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";

interface Column {
  align?: "right" | "left" | "center";
  icon?: React.FC;
  id: keyof CountryTableData;
  label: string;
}

const columns: readonly Column[] = [
  { id: "power", label: "Power", icon: PowerIcon, align: "left" },
  {
    id: "supplyCenterNo",
    label: "Centers",
    icon: CentersIcon,
    align: "center",
  },
  {
    id: "status",
    label: "Status",
    icon: WDCheckmarkIcon,
    align: "center",
  },
  {
    id: "bet",
    label: "Bet",
    icon: BetIcon,
    align: "center",
  },
  {
    id: "pointsWon",
    label: "Won",
    icon: BetIcon,
    align: "center",
  },
];

interface WDGameFinishedOverlayProps {
  allCountries: CountryTableData[];
}

const WDGameFinishedOverlay: React.FC<WDGameFinishedOverlayProps> = function ({
  allCountries,
}) {
  const { width } = useWindowSize();

  const innerElem = (
    <div className="flex flex-col text-center w-[300px]">
      <div className="m-3">Game is finished.</div>
      <table aria-label="game finished table">
        <thead className="h-[55px]">
          <tr className="align-top">
            {columns.map((column) => (
              <th key={column.id} align={column.align}>
                {column.icon && <column.icon />}
                <div className="text-xss font-medium block mt-1">
                  {column.label.toUpperCase()}
                </div>
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {allCountries.map((country) => (
            <React.Fragment key={country.power}>
              <tr className="border-t border-gray-200 mt-1">
                {columns.map((column) => {
                  let className = "text-black font-normal";
                  let value;
                  switch (column.id) {
                    case "power":
                      value =
                        width < 500
                          ? country.abbr.toUpperCase()
                          : country.power.toUpperCase();
                      className = `text-${country.power.toLowerCase()}-main font-bold`;
                      break;
                    case "pointsWon":
                      value = `${country[column.id] || "-"}`;
                      break;
                    default:
                      value = `${country[column.id]}`;
                      break;
                  }
                  return (
                    <td key={column.id} align={column.align} className="pt-2">
                      <span className={className}>{value}</span>
                    </td>
                  );
                })}
              </tr>
              <tr>
                <td className="text-left text-xs pb-2 font-roboto">
                  {country.username}
                </td>
              </tr>
            </React.Fragment>
          ))}
        </tbody>
      </table>
      {/* Forces the user to rotate the phone to portrait mode to see the results */}
      <style jsx global>{`
        @media screen and (min-width: 320px) and (max-width: 900px) and (orientation: landscape) {
          html {
            transform: rotate(-90deg);
            transform-origin: left top;
            width: 100vh;
            height: 150vh;
            overflow-x: hidden;
            position: absolute;
            top: 100%;
            left: 0px;
          }
          #map-container {
            height: 250vh !important;
          }
        }
      `}</style>
    </div>
  );
  return (
    <div className="absolute p-3 rounded-md bg-white top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
      {innerElem}
    </div>
  );
};

export default WDGameFinishedOverlay;
