import {
  Box,
  Button,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  useTheme,
} from "@mui/material";
import * as React from "react";
import Device from "../../enums/Device";
import Season from "../../enums/Season";
import useViewport from "../../hooks/useViewport";
import { CountryTableData } from "../../interfaces/CountryTableData";
import { MemberData } from "../../interfaces/state/MemberData";
import { gameOverview } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import { formatPSYForDisplay } from "../../utils/formatPhaseForDisplay";
import getDevice from "../../utils/getDevice";
import BetIcon from "./icons/country-table/WDBet";
import CentersIcon from "./icons/country-table/WDCenters";
import PowerIcon from "./icons/country-table/WDPower";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";

const centeredStyle = {
  position: "absolute",
  top: "50%",
  left: "50%",
  // justifyContent: "center",
  // alignItems: "center",
  transform: "translate(-50%, -50%)",
  backgroundColor: "rgba(255,255,255,1)",
  p: "10px",
  borderRadius: "5px",
};

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
  const theme = useTheme();
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const isMobile =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE ||
    device === Device.MOBILE_LG;
  const overview = useAppSelector(gameOverview);

  const innerElem = (
    <Stack direction="column" alignItems="center">
      <Box sx={{ m: "4px" }}>Game is finished:</Box>
      <Table aria-label="country info table" size="small" stickyHeader>
        <TableHead sx={{ height: "55px" }}>
          <TableRow sx={{ verticalAlign: "top" }}>
            {columns.map((column) => (
              <TableCell key={column.id} align={column.align}>
                {column.icon && <column.icon />}
                <Box sx={{ fontSize: 10, fontWeight: 400 }}>
                  {column.label.toUpperCase()}
                </Box>
              </TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {allCountries.map((country) => (
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
                    case "pointsWon":
                      value = `${country[column.id] || "-"}`;
                      break;
                    default:
                      value = `${country[column.id]}`;
                      break;
                  }
                  return (
                    <TableCell key={column.id} align={column.align}>
                      <span style={style}>{value}</span>
                    </TableCell>
                  );
                })}
              </TableRow>
              <TableRow>
                <TableCell
                  sx={{
                    paddingTop: "0px !important",
                    fontSize: "10pt",
                    fontFamily: "Roboto",
                  }}
                >
                  {country.username}
                </TableCell>
              </TableRow>
            </React.Fragment>
          ))}
        </TableBody>
      </Table>
    </Stack>
  );
  return <Box sx={centeredStyle}>{innerElem}</Box>;
};

export default WDGameFinishedOverlay;
