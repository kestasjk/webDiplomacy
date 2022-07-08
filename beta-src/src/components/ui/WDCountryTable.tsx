import React, { Fragment } from "react";
import { useWindowSize } from "react-use";

import BetIcon from "./icons/country-table/WDBet";
import CentersIcon from "./icons/country-table/WDCenters";
import { CountryTableData } from "../../interfaces";
import DelaysIcon from "./icons/country-table/WDDelays";
import IntegerRange from "../../types/IntegerRange";
import PowerIcon from "./icons/country-table/WDPower";
import UnitsIcon from "./icons/country-table/WDUnits";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";
import WDOrderStatusIcon from "./WDOrderStatusIcon";
import LeftGameIcon from "./icons/country-table/WDLeftGame";

import Vote from "../../enums/Vote";

interface WDCountryTableProps {
  userCountry: CountryTableData | null;
  countries: CountryTableData[];
  maxDelays: IntegerRange<0, 5>;
}

interface Column {
  align?: "right" | "left" | "center";
  icon?: React.FC;
  id: keyof CountryTableData;
  label: string;
}

const columns: readonly Column[] = [
  { id: "power", label: "Power", icon: PowerIcon, align: "left" },
  {
    id: "orderStatus",
    label: "Status",
    icon: WDCheckmarkIcon,
    align: "center",
  },
  { id: "unitNo", label: "Units", icon: UnitsIcon, align: "center" },
  {
    id: "supplyCenterNo",
    label: "Centers",
    icon: CentersIcon,
    align: "center",
  },
  {
    id: "bet",
    label: "Bet",
    icon: BetIcon,
    align: "center",
  },
  {
    id: "excusedMissedTurns",
    label: "Delays",
    icon: DelaysIcon,
    align: "center",
  },
];

const WDCountryTable: React.FC<WDCountryTableProps> = function ({
  userCountry,
  countries,
  maxDelays,
}): React.ReactElement {
  const { width } = useWindowSize();

  return (
    <div className="mt-5 pl-4 pr-1">
      <table aria-label="country info table" className="w-full">
        <thead style={{ height: "55px" }}>
          <tr style={{ verticalAlign: "top" }}>
            {columns.map((column) => (
              <th key={column.id} align={column.align}>
                {column.icon && <column.icon />}
                <div className="text-xss mt-1 font-normal">
                  {column.label.toUpperCase()}
                </div>
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {countries.map((country) => (
            <Fragment key={country.power}>
              <tr>
                {columns.map((column) => {
                  const style: any = {
                    fontWeight: 400,
                  };
                  let value;
                  switch (column.id) {
                    case "power":
                      value =
                        width < 1200
                          ? country.abbr.toUpperCase()
                          : country.power.toUpperCase();
                      style.color = country.color;
                      style.fontWeight = 700;
                      break;
                    case "orderStatus":
                      return (
                        <td key={column.id} align={column.align}>
                          {country.status !== "Left" ? (
                            <WDOrderStatusIcon
                              orderStatus={country.orderStatus}
                              isHidden={
                                country.orderStatus.Hidden &&
                                country.countryID !== userCountry?.countryID
                              }
                            />
                          ) : (
                            <LeftGameIcon height={13} />
                          )}
                        </td>
                      );
                    case "excusedMissedTurns":
                      value = `${country[column.id]}/${maxDelays}`;
                      break;
                    default:
                      value = `${country[column.id]}`;
                      break;
                  }
                  return (
                    <td key={column.id} align={column.align}>
                      <span className="text-black" style={style}>
                        {value}
                      </span>
                    </td>
                  );
                })}
              </tr>
              <tr>
                <td className="pt-0 text-sm font-roboto pb-2">
                  {country.username}
                </td>
                <td
                  className="text-xss pt-0 font-medium pb-2"
                  colSpan={columns.length}
                >
                  <div
                    className={`${
                      country.votes.length ? "inline-block" : "hidden"
                    } mr-2 text-gray-400`}
                  >
                    VOTED
                  </div>

                  {Object.keys(Vote).map(
                    (vote) =>
                      country.votes.includes(vote) && (
                        <span
                          key={`${country.power}-vote-${vote}`}
                          className="mr-1 text-white font-bold px-2 py-0.5 rounded-full text-xsss"
                          style={{
                            background: country.color,
                          }}
                        >
                          {vote.toUpperCase()}
                        </span>
                      ),
                  )}
                </td>
              </tr>
            </Fragment>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default WDCountryTable;
