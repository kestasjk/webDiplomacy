import * as React from "react";
import {
  Box,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TableContainer,
} from "@mui/material";
import GameMode from "../../enums/GameMode";
import Season from "../../enums/Season";
import Ranking from "../../enums/Ranking";
import IntegerRange from "../../types/IntegerRange";

/**
 * game setting datas which would be passed to the component by parent component/ context/redux store
 */

interface WDInfoDisplayProps {
  gameMode: GameMode;
  potNumber: IntegerRange<35, 665>;
  rank: Ranking;
  season: Season;
  title: string;
  year: number;
}

const tableCellStyles = {
  border: "none",
  fontSize: "0.7rem",
  p: "0 5px 0 0",
};

const WDInfoDisplay: React.FC<WDInfoDisplayProps> = function ({
  gameMode,
  potNumber,
  rank,
  season,
  title,
  year,
}) {
  return (
    <TableContainer>
      <Table
        aria-label="a dense table"
        size="small"
        sx={{
          maxWidth: 250,
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
              <Box
                /**
                 * by applying CSS properties, the title of the game would be able to wrap until the second line
                 * for the second line display '...' to show it being cut-off
                 */
                sx={{
                  display: "-webkit-box",
                  lineHeight: "1.2rem",
                  maxHeight: "2.4rem",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                  wordWrap: "break-word",
                  WebkitLineClamp: 2,
                  WebkitBoxOrient: "vertical",
                }}
              >
                {title}
              </Box>
            </TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              Pot: {potNumber} - {season} {year}
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              Classic, {gameMode}, {rank}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default WDInfoDisplay;
