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
import { styled } from "@mui/material/styles";
import BetIcon from "./icons/country-table/WDBet";
import CentersIcon from "./icons/country-table/WDCenters";
import { CountryTableData } from "../../interfaces";
import DelaysIcon from "./icons/country-table/WDDelays";
import Device from "../../enums/Device";
import IntegerRange from "../../types/IntegerRange";
import PowerIcon from "./icons/country-table/WDPower";
import UnitsIcon from "./icons/country-table/WDUnits";

interface WDCountryTableProps {
  countries: CountryTableData[];
  device: Device;
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
  { id: "unitQty", label: "Units", icon: UnitsIcon, align: "center" },
  {
    id: "centerQty",
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
    id: "delaysLeft",
    label: "Delays",
    icon: DelaysIcon,
    align: "center",
  },
];

const WDCountryTable: React.FC<WDCountryTableProps> = function ({
  countries,
  device,
  maxDelays,
}): React.ReactElement {
  const theme = useTheme();
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE || device === Device.MOBILE_LG_LANDSCAPE;
  const WDTableCell = styled(TableCell)(() => {
    const padding = mobileLandscapeLayout ? 6 : "6px 16px";
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
                  let value: string;
                  switch (column.id) {
                    case "power":
                      value = mobileLandscapeLayout
                        ? country.abbr.toUpperCase()
                        : country.power.toUpperCase();
                      style.color = country.color;
                      style.fontWeight = 700;
                      break;
                    case "delaysLeft":
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
              {country.votes && (
                <TableRow>
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
                        display: "inline-block",
                        marginRight: 1.5,
                      }}
                    >
                      VOTED
                    </Box>
                    {Object.entries(country.votes).map(
                      (data) =>
                        data[1] && (
                          <Chip
                            key={`${country.power}-vote-${data[0]}`}
                            size="small"
                            label={data[0].toUpperCase()}
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
                </TableRow>
              )}
            </React.Fragment>
          ))}
        </TableBody>
      </Table>
    </div>
  );
};

export default WDCountryTable;
