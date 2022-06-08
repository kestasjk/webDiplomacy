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
  Typography,
  useTheme,
  Link,
  Stack,
  Button,
  Badge,
} from "@mui/material";
import { Email } from "@mui/icons-material";

import { styled } from "@mui/material/styles";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { useAppSelector } from "../../state/hooks";
import {
  gameOverview,
  playerActiveGames,
} from "../../state/game/game-api-slice";
import WDVerticalScroll from "./WDVerticalScroll";
import {
  formatPSYForDisplay,
  formatPSYForDisplayShort,
} from "../../utils/formatPhaseForDisplay";
import { getPhaseSeasonYear } from "../../utils/state/getPhaseSeasonYear";
import WDOrderStatusIcon from "./WDOrderStatusIcon";
import getOrderStates from "../../utils/state/getOrderStates";
import { getFormattedTimeLeft } from "../../utils/formatTime";

const WDGamesList: React.FC = function (): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const activeGames = useAppSelector(playerActiveGames);
  const overview = useAppSelector(gameOverview); // just for country mapping
  const theme = useTheme();

  const countries = Object.fromEntries(
    overview.members.map((m) => [m.countryID, m.country]),
  );

  return (
    <WDVerticalScroll>
      <Stack sx={{ alignItems: "center" }}>
        {activeGames
          .filter((game) => game.gameID !== overview.gameID)
          .map((game) => (
            <Button
              key={game.gameID}
              variant="outlined"
              sx={{
                width: "90%",
                m: "6px",
              }}
              href={`?gameID=${game.gameID}`}
            >
              <Box
                sx={{
                  display: "grid",
                  gridTemplateRows: "auto",
                  gridTemplateColumns: "auto",
                  width: "100%",
                }}
              >
                <Stack sx={{ gridColumn: 1, gridRow: 1 }}>
                  <Box
                    className="game-button"
                    sx={{
                      fontSize: "16px",
                      fontWeight: 700,
                      lineHeight: "19px",
                    }}
                  >
                    {game.name}
                  </Box>
                  <Box
                    className="game-button"
                    sx={{
                      fontWeight: 400,
                      fontSize: "11px",
                      lineHeight: "13px",
                    }}
                  >
                    {formatPSYForDisplay(
                      getPhaseSeasonYear(game.turn, game.phase),
                    )}
                    <br />
                    <span
                      className="game-button"
                      style={{
                        color: theme.palette[countries[game.countryID]].main,
                        fontWeight: 700,
                        fontSize: "11px",
                        padding: 0,
                      }}
                    >
                      {countries[game.countryID]}
                    </span>{" "}
                    - {game.unitNo} units
                  </Box>
                </Stack>
                <Stack sx={{ gridColumn: 2, gridRow: 1 }}>
                  <Box
                    sx={{
                      fontWeight: 700,
                      fontSize: "14px",
                      lineHeight: "16px",
                      textAlign: "right",
                    }}
                    className="game-button"
                  >
                    {getFormattedTimeLeft(game.processTime).replace(
                      " remaining",
                      "",
                    )}
                  </Box>
                  <Box
                    component="span"
                    sx={{
                      fontWeight: 700,
                      fontSize: "10px",
                      lineHeight: "12px",
                      textAlign: "right",
                      verticalAlign: "middle",
                    }}
                    className="game-button"
                  >
                    {game.newMessagesFrom.length && (
                      <Email
                        fontSize="small"
                        sx={{
                          verticalAlign: "middle",
                          color: "#66C",
                          m: "3px",
                        }}
                      />
                    )}
                    <Box
                      component="span"
                      sx={{ verticalAlign: "middle", m: "3px" }}
                    >
                      <WDOrderStatusIcon
                        orderStatus={getOrderStates(game.orderStatus)}
                      />
                    </Box>
                  </Box>
                </Stack>
              </Box>
            </Button>
            // <TableRow key={game.name}>
            //   <WDTableCell>
            //     <Link href={`beta?gameID=${game.gameID}`}>{game.name}</Link>
            //   </WDTableCell>
            //   <WDTableCell>
            //     {getCountryNameFromID(game.countryID)
            //       .substring(0, 3)
            //       .toUpperCase()}
            //   </WDTableCell>
            //   <WDTableCell>
            //     {formatPSYForDisplayShort(
            //       getPhaseSeasonYear(game.turn, game.phase),
            //     )}
            //   </WDTableCell>
            //   <WDTableCell>
            //     <WDOrderStatusIcon
            //       orderStatus={getOrderStates(game.orderStatus)}
            //     />
            //   </WDTableCell>
            // </TableRow>
          ))}
      </Stack>
    </WDVerticalScroll>
  );
};

export default WDGamesList;
