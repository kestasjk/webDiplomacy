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
  Link,
  Stack,
  Button,
  Badge,
} from "@mui/material";
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

const WDGamesList: React.FC = function (): React.ReactElement {
  const theme = useTheme();
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const activeGames = useAppSelector(playerActiveGames);
  const overview = useAppSelector(gameOverview); // just for country mapping

  // this is sad and busted, but no other way
  const getCountryNameFromID = function (countryID: number): string {
    console.log({ countryID, m: overview.members });
    return (
      overview.members.find((m) => m.countryID === countryID)?.country || "???"
    );
  };

  return (
    <WDVerticalScroll>
      <Stack sx={{ alignItems: "center" }}>
        {activeGames.map((game) => (
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
                  sx={{ fontSize: "16px", fontWeight: 700, lineHeight: "19px" }}
                >
                  {game.name}
                </Box>
                <Box
                  className="game-button"
                  sx={{ fontWeight: 400, fontSize: "10px", lineHeight: "12px" }}
                >
                  {formatPSYForDisplay(
                    getPhaseSeasonYear(game.turn, game.phase),
                  )}
                  <br />
                  Classic, Full-Press, Unranked
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
                  11:42 hrs
                </Box>
                <Box
                  sx={{
                    fontWeight: 700,
                    fontSize: "10px",
                    lineHeight: "12px",
                    textAlign: "right",
                  }}
                  className="game-button"
                >
                  Orders
                  <br />6 of 7
                </Box>
              </Stack>
              <Stack
                direction="row"
                sx={{
                  gridRow: 2,
                  gridColumn: "1/3",
                  marginTop: "10px",
                }}
              >
                <Badge variant="dot" color="error">
                  <Box
                    sx={{
                      p: "0px",
                      m: "6px",
                      fontSize: "12px",
                      fontWeight: 500,
                    }}
                    className="game-button"
                  >
                    FRA 2
                  </Box>
                </Badge>
                <Box
                  sx={{ p: "0px", m: "6px", fontSize: "12px", fontWeight: 500 }}
                  className="game-button"
                >
                  AUS 3
                </Box>
                <Box
                  sx={{ p: "0px", m: "6px", fontSize: "12px", fontWeight: 500 }}
                  className="game-button"
                >
                  ENG 2
                </Box>
                <Box
                  sx={{ p: "0px", m: "6px", fontSize: "12px", fontWeight: 500 }}
                  className="game-button"
                >
                  GER 3
                </Box>
                <Box
                  sx={{ p: "0px", m: "6px", fontSize: "12px", fontWeight: 500 }}
                  className="game-button"
                >
                  ITA 2
                </Box>
                <Box
                  sx={{ p: "0px", m: "6px", fontSize: "12px", fontWeight: 500 }}
                  className="game-button"
                >
                  TUR 3
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
