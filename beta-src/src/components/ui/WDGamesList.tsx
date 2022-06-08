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
    <WDVerticalScroll>
      <Stack sx={{ align: "center" }}>
        {activeGames.map((game) => (
          <Button
            key={game.gameID}
            variant="outlined"
            sx={{ align: "center", width: "90%", m: "6px" }}
            href={`beta?gameID=${game.gameID}`}
          >
            <Stack direction="column" sx={{ m: "10px" }}>
              <Stack direction="row">
                <Stack sx={{ float: "left", left: "0px" }}>
                  <Box className="game-button" sx={{ fontSize: "20px" }}>
                    {game.name}
                  </Box>
                  <Box className="game-button" sx={{ fontSize: "12px" }}>
                    {formatPSYForDisplay(
                      getPhaseSeasonYear(game.turn, game.phase),
                    )}
                  </Box>
                </Stack>
                <Stack sx={{ float: "right" }}>
                  <Box className="game-button">Top right 1</Box>
                  <Box className="game-button">Top right 2</Box>
                </Stack>
              </Stack>
              <Stack direction="row">
                <Box className="game-button">FRA 2</Box>
                <Box className="game-button">AUS 3</Box>
              </Stack>
            </Stack>
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
