import * as React from "react";
import {
  Box,
  Chip,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  tableCellClasses,
  useTheme,
} from "@mui/material";
import { Lock } from "@mui/icons-material";
import { styled } from "@mui/material/styles";
import BetIcon from "./icons/country-table/WDBet";
import CentersIcon from "./icons/country-table/WDCenters";
import { CountryTableData } from "../../interfaces";
import DelaysIcon from "./icons/country-table/WDDelays";
import Device from "../../enums/Device";
import IntegerRange from "../../types/IntegerRange";
import PowerIcon from "./icons/country-table/WDPower";
import UnitsIcon from "./icons/country-table/WDUnits";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";
import Vote from "../../enums/Vote";

interface WDCountryTableProps {
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
  countries,
  maxDelays,
}): React.ReactElement {
  const theme = useTheme();
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const isMobile =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE ||
    device === Device.MOBILE_LG;
  const WDTableCell = styled(TableCell)(() => {
    const padding = "8px 5px 0px 5px";
    return {
      [`&.${tableCellClasses.head}`]: {
        borderBottom: 0,
        lineHeight: "normal",
        padding,
      },
      [`&.${tableCellClasses.body}`]: {
        borderBottom: 0,
        padding,
      },
    };
  });
  return (
    <div>
      <Table aria-label="country info table" size="small" stickyHeader>
        <TableHead sx={{ height: "55px" }}>
          <TableRow sx={{ verticalAlign: "top" }}>
            {columns.map((column) => (
              <WDTableCell key={column.id} align={column.align}>
                {column.icon && <column.icon />}
                <Box sx={{ fontSize: 10, fontWeight: 400 }}>
                  {column.label.toUpperCase()}
                </Box>
              </WDTableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {countries.map((country) => (
            <React.Fragment key={country.power}>
              <TableRow>
                {columns.map((column) => {
                  const style = {
                    color: theme.palette.primary.main,
                    fontWeight: 400,
                  };
                  let value;
                  switch (column.id) {
                    case "power":
                      value = isMobile
                        ? country.abbr.toUpperCase()
                        : country.power.toUpperCase();
                      style.color = country.color;
                      style.fontWeight = 700;
                      break;
                    case "orderStatus":
                      return (
                        <WDTableCell key={column.id} align={column.align}>
                          {country.orderStatus.Hidden && (
                            <Lock sx={{ fontSize: "16px", color: "#666" }} />
                          )}
                          {(country.orderStatus.Saved ||
                            country.orderStatus.Ready) && (
                            <WDCheckmarkIcon
                              color={
                                (country.orderStatus.Ready && "#0A0") || "#888"
                              }
                            />
                          )}
                        </WDTableCell>
                      );
                    case "excusedMissedTurns":
                      value = `${country[column.id]}/${maxDelays}`;
                      break;
                    default:
                      value = `${country[column.id]}`;
                      break;
                  }
                  return (
                    <WDTableCell key={column.id} align={column.align}>
                      <span style={style}>{value}</span>
                    </WDTableCell>
                  );
                })}
              </TableRow>
              <TableRow
              //   sx={{
              //     display:
              //       country.votes.length || country.username
              //         ? "contents"
              //         : "none",
              //   }}
              >
                <WDTableCell
                  sx={{
                    display: country.username ? "inline-block" : "none",
                    paddingTop: "0px !important",
                    fontSize: "10pt",
                    fontFamily: "Roboto",
                  }}
                >
                  {country.username}
                </WDTableCell>
                <WDTableCell
                  sx={{
                    fontSize: "70%",
                    paddingTop: "0px !important",
                    fontWeight: 700,
                  }}
                  colSpan={columns.length}
                >
                  <Box
                    sx={{
                      color: theme.palette.action.disabledBackground,
                      display: country.votes.length ? "inline-block" : "none",
                      marginRight: 1.5,
                    }}
                  >
                    VOTED
                  </Box>

                  {Object.keys(Vote).map(
                    (vote) =>
                      country.votes.includes(vote) && (
                        <Chip
                          key={`${country.power}-vote-${vote}`}
                          size="small"
                          label={vote.toUpperCase()}
                          sx={{
                            color: theme.palette.secondary.main,
                            background: country.color,
                            fontWeight: 900,
                            fontSize: "90%",
                            height: 14,
                            marginRight: 1,
                          }}
                        />
                      ),
                  )}
                </WDTableCell>
                <WDTableCell />
              </TableRow>
            </React.Fragment>
          ))}
        </TableBody>
      </Table>
    </div>
  );
};

export default WDCountryTable;
