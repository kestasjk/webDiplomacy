import * as React from "react";
import { Table, TableBody, TableCell, TableRow } from "@mui/material";
import { CountryTableData } from "../../interfaces/CountryTableData";
import { IOrderDataHistorical } from "../../models/Interfaces";
import GameStateMaps from "../../state/interfaces/GameStateMaps";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import WDVerticalScroll from "./WDVerticalScroll";

interface WDOrdersPanelProps {
  orders: IOrderDataHistorical[];
  allCountries: CountryTableData[];
  maps: GameStateMaps;
}

function shortUnitType(unitType: string): string | undefined {
  if (unitType === "Army") return "A";
  if (unitType === "Fleet") return "F";
  return undefined;
}

const WDOrdersPanel: React.FC<WDOrdersPanelProps> = function ({
  orders,
  allCountries,
  maps,
}): React.ReactElement {
  const orderStringsByCountryID: { [key: number]: string[] } = {};
  const supporteeUnitTypeByProvID: { [key: string]: string | undefined } = {};
  orders.forEach((order) => {
    if (order.terrID) {
      supporteeUnitTypeByProvID[maps.terrIDToProvinceID[order.terrID]] =
        shortUnitType(order.unitType);
    }
  });

  orders.forEach((order) => {
    if (!orderStringsByCountryID[order.countryID]) {
      orderStringsByCountryID[order.countryID] = [];
    }
    const orderStrings = orderStringsByCountryID[order.countryID];
    const uType = shortUnitType(order.unitType);

    const getProvStr = function (terrID) {
      if (!terrID) return undefined;
      return TerritoryMap[maps.terrIDToTerritory[terrID]].provinceMapData.abbr;
    };
    const getTerrStr = function (terrID) {
      if (!terrID) return undefined;
      const mappedTerritory = TerritoryMap[maps.terrIDToTerritory[terrID]];
      if (mappedTerritory.unitSlotName === "main")
        return mappedTerritory.provinceMapData.abbr;
      return `${mappedTerritory.provinceMapData.abbr} (${mappedTerritory.unitSlotName})`;
    };
    const terrStr = getTerrStr(order.terrID);
    const provStr = getProvStr(order.terrID);
    const toProvStr = getProvStr(order.toTerrID);
    const toTerrStr = getTerrStr(order.toTerrID);
    const fromProvStr = getProvStr(order.fromTerrID);
    const supporteeTerrID = order.fromTerrID || order.toTerrID;
    const supporteeProvID = supporteeTerrID
      ? maps.terrIDToProvinceID[supporteeTerrID]
      : undefined;
    const supporteeUType = supporteeProvID
      ? supporteeUnitTypeByProvID[supporteeProvID]
      : undefined;

    if (order.type === "Move" && uType && provStr && toTerrStr) {
      if (order.viaConvoy === "Yes") {
        orderStrings.push(`${uType} ${provStr} -> ${toTerrStr} via convoy`);
      } else {
        orderStrings.push(`${uType} ${provStr} -> ${toTerrStr}`);
      }
    } else if (order.type === "Retreat" && uType && provStr && toTerrStr) {
      orderStrings.push(`${uType} ${provStr} retreats to ${toTerrStr}`);
    } else if (order.type === "Disband" && uType && provStr) {
      orderStrings.push(`${uType} ${provStr} disbands`);
    } else if (order.type === "Hold" && uType && provStr) {
      orderStrings.push(`${uType} ${provStr} H`);
    } else if (
      order.type === "Support hold" &&
      uType &&
      supporteeUType &&
      provStr &&
      toProvStr
    ) {
      orderStrings.push(
        `${uType} ${provStr} S ${supporteeUType} ${toProvStr} H`,
      );
    } else if (
      order.type === "Support move" &&
      uType &&
      supporteeUType &&
      provStr &&
      fromProvStr &&
      toProvStr
    ) {
      orderStrings.push(
        `${uType} ${provStr} S ${supporteeUType} ${fromProvStr} -> ${toProvStr}`,
      );
    } else if (
      order.type === "Convoy" &&
      uType &&
      supporteeUType &&
      provStr &&
      fromProvStr &&
      toProvStr
    ) {
      orderStrings.push(
        `${uType} ${provStr} C ${supporteeUType} ${fromProvStr} -> ${toProvStr}`,
      );
    } else if (order.type === "Destroy" && uType && provStr) {
      orderStrings.push(`Destroy ${uType} ${provStr}`);
    } else if (order.type === "Build Army" && terrStr) {
      orderStrings.push(`Build A ${terrStr}`);
    } else if (order.type === "Build Fleet" && terrStr) {
      orderStrings.push(`Build F ${terrStr}`);
    } else if (uType && provStr) {
      orderStrings.push(`${uType} ${provStr} order unassigned`);
    }
  });
  console.log({ orders, orderStringsByCountryID });

  return (
    <WDVerticalScroll>
      <Table aria-label="country info table" size="small" stickyHeader>
        <TableBody>
          {allCountries.map(
            (country) =>
              orderStringsByCountryID[country.countryID] && (
                <React.Fragment key={country.power}>
                  <TableRow key="orderlabel">
                    <TableCell
                      align="left"
                      sx={{
                        borderBottom: "none",
                        paddingTop: "14px",
                        paddingBottom: "2px",
                      }}
                    >
                      <span style={{ color: country.color, fontWeight: 700 }}>
                        {country.power.toUpperCase()}
                      </span>
                    </TableCell>
                  </TableRow>
                  {orderStringsByCountryID[country.countryID]?.map(
                    (orderString) => (
                      <TableRow key={`order-${orderString}`}>
                        <TableCell
                          sx={{
                            paddingTop: "0px !important",
                            fontSize: "10pt",
                            fontFamily: "Roboto",
                            borderBottom: "none",
                            paddingBottom: "2px",
                          }}
                        >
                          {orderString}
                        </TableCell>
                      </TableRow>
                    ),
                  )}
                </React.Fragment>
              ),
          )}
        </TableBody>
      </Table>
    </WDVerticalScroll>
  );
};

export default WDOrdersPanel;
