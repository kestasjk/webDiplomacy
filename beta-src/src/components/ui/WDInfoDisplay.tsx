import * as React from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TableContainer,
} from "@mui/material";
import WDButton from "./WDButton";
import IntegerRange from "../../types/IntegerRange";
import WDLineClamp from "./WDLineClamp";
import Device from "../../enums/Device";
import getDevice from "../../utils/getDevice";
import useViewport from "../../hooks/useViewport";
import Season from "../../enums/Season";
import { formatPSYForDisplay } from "../../utils/formatPhaseForDisplay";
import {
  gameOverview,
  copySandboxFromGame,
  moveSandboxTurnBack,
  deleteSandbox,
} from "../../state/game/game-api-slice";
import { getFormattedTime } from "../../utils/formatTime";
import { useAppDispatch, useAppSelector } from "../../state/hooks";

/**
 * game setting datas which would be passed to the component by parent component/ context/redux store
 */

interface WDInfoDisplayProps {
  alternatives: string;
  phase: string;
  potNumber: IntegerRange<35, 666>;
  season: string;
  title: string;
  year: number;
  gameID: number;
}

const tableCellStyles = {
  border: "none",
  fontSize: "0.7rem",
  p: "0 5px 0 0",
};

const WDInfoDisplay: React.FC<WDInfoDisplayProps> = function ({
  alternatives,
  phase,
  potNumber,
  season,
  title,
  year,
  gameID,
}) {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const isMobile =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE ||
    device === Device.MOBILE_LG;

  const { phaseMinutes, phaseMinutesRB } = useAppSelector(gameOverview);
  let phaseLengthInfo = `${getFormattedTime(phaseMinutes * 60)} / phase`;
  if (phaseMinutesRB !== -1) {
    phaseLengthInfo = `${phaseLengthInfo} (M) | ${getFormattedTime(
      phaseMinutesRB * 60,
    )} / phase (R, B)`;
  }
  const width = isMobile ? 260 : 320;
  const dropDownBoardLink = `/board.php?gameID=${gameID}&view=dropDown`;
  const archiveOrdersLink = `/board.php?gameID=${gameID}&view=dropDown&viewArchive=Orders`;
  const archiveMapsLink = `/board.php?gameID=${gameID}&view=dropDown&viewArchive=Maps`;
  const archiveMessagesLink = `/board.php?gameID=${gameID}&view=dropDown&viewArchive=Messages`;
  // if alternatives contains Sandbox create an isSandbox variable set to true:
  const isSandbox = alternatives.includes("Sandbox");

  const dispatch = useAppDispatch();

  const clickedCopySandboxFromGame = () => {
    dispatch(copySandboxFromGame({ copyGameID: String(gameID) })).then(
      (res) => {
        window.location.href = `/board.php?gameID=${res.payload.gameID}`;
      },
    );
  };
  const clickedMoveSandboxTurnBack = () => {
    dispatch(moveSandboxTurnBack({ gameID: String(gameID) })).then(() => {
      window.location.reload();
    });
  };
  const clickedDeleteSandbox = () => {
    // After deleting the sandbox, refresh the page to update the game overview
    dispatch(deleteSandbox({ gameID: String(gameID) })).then(() => {
      window.location.href = "/";
    });
  };
  const buttonClass = "h-4 sm:w-fit sm:px-[10px]";

  return (
    <TableContainer sx={{ overflowX: "inherit" }}>
      <Table
        aria-label="A table of game information"
        size="small"
        sx={{
          width,
        }}
      >
        <TableHead>
          <TableRow>
            <TableCell
              sx={{
                border: "none",
                fontWeight: 600,
                p: "2px 5px 5px 0",
              }}
            >
              <WDLineClamp
                lineHeight="1.2rem"
                maxHeight="2.4rem"
                WebkitLineClamp={2}
              >
                {title}
              </WDLineClamp>
            </TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              Pot: {potNumber} -{" "}
              <b>
                {formatPSYForDisplay({
                  phase,
                  season: season as Season,
                  year,
                })}
              </b>
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>{alternatives}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>{phaseLengthInfo}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              <b>Archive:</b>&nbsp;
              <a href={archiveOrdersLink} className="text-blue-500">
                Orders
              </a>
              &nbsp;|&nbsp;
              <a href={archiveMapsLink} className="text-blue-500">
                Maps
              </a>
              &nbsp;|&nbsp;
              <a href={archiveMessagesLink} className="text-blue-500">
                Messages
              </a>
              &nbsp;-&nbsp;
              <a href={dropDownBoardLink} className="text-blue-500">
                Legacy Board
              </a>
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              <div className="flex flex-col sm:flex-row justify-end space-y-2 space-x-0 sm:space-x-3 sm:space-y-0 w-fit">
                <b>Sandbox:</b>&nbsp;
                <WDButton
                  onClick={clickedCopySandboxFromGame}
                  className={buttonClass}
                >
                  Create
                </WDButton>
                {isSandbox && (
                  <WDButton
                    onClick={clickedMoveSandboxTurnBack}
                    className={buttonClass}
                  >
                    Move back
                  </WDButton>
                )}
                {isSandbox && (
                  <WDButton
                    onClick={clickedDeleteSandbox}
                    className={buttonClass}
                  >
                    Delete
                  </WDButton>
                )}
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default WDInfoDisplay;
