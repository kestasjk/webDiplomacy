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
import { formatPSYForDisplay } from "../../utils/formatPhaseForDisplay";
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

  const SBox = styled(Box)(() => ({ padding: "2px", fontWeight: 700 }));

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
              <SBox
                sx={{
                  display: "grid",
                  gridTemplateRows: "auto",
                  gridTemplateColumns: "auto",
                  width: "100%",
                }}
              >
                <Stack sx={{ gridColumn: 1, gridRow: 1 }}>
                  <SBox
                    sx={{
                      fontSize: "16px",
                    }}
                  >
                    {game.name}
                  </SBox>
                  <SBox
                    sx={{
                      fontWeight: 400,
                      fontSize: "11px",
                    }}
                  >
                    {formatPSYForDisplay(
                      getPhaseSeasonYear(game.turn, game.phase),
                    )}
                    <br />
                    <span
                      style={{
                        color: theme.palette[countries[game.countryID]].main,
                        fontSize: "11px",
                        fontWeight: 700,
                        padding: 0,
                      }}
                    >
                      {countries[game.countryID]}
                    </span>{" "}
                    - {game.unitNo} units
                  </SBox>
                </Stack>
                <Stack sx={{ gridColumn: 2, gridRow: 1 }}>
                  <SBox
                    sx={{
                      fontSize: "14px",
                      textAlign: "right",
                    }}
                  >
                    {getFormattedTimeLeft(game.processTime).replace(
                      " remaining",
                      "",
                    )}
                  </SBox>
                  <SBox
                    component="span"
                    sx={{
                      fontSize: "10px",
                      textAlign: "right",
                      verticalAlign: "middle",
                    }}
                  >
                    {!!game.newMessagesFrom.length && (
                      <Email
                        fontSize="small"
                        sx={{
                          verticalAlign: "middle",
                          color: "#88A",
                          m: "3px",
                        }}
                      />
                    )}
                    <SBox
                      component="span"
                      sx={{ verticalAlign: "middle", m: "3px" }}
                    >
                      <WDOrderStatusIcon
                        orderStatus={getOrderStates(game.orderStatus)}
                      />
                    </SBox>
                  </SBox>
                </Stack>
              </SBox>
            </Button>
          ))}
      </Stack>
    </WDVerticalScroll>
  );
};

export default WDGamesList;
