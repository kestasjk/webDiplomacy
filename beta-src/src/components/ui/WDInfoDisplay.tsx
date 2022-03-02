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

/**
 * game setting datas which would be passed to the component by parent component/ context/redux store
 */

interface WDInfoDisplayProps {
  gameTime: number;
  gameType: string;
  phase: string;
  playType: string;
  rank: string;
  season: string;
  title: string;
  year: string;
}

const tableCellStyles = {
  border: "none",
  fontSize: "0.7rem",
  p: "0px 5px 0px 0px",
};

const WDInfoDisplay: React.FC<WDInfoDisplayProps> = function ({
  gameTime,
  gameType,
  phase,
  playType,
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
                p: "2px 5px 5px 0px",
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
              Next phase in: {gameTime}, {phase},
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              Pot: 35 - {season} {year}
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>
              {gameType}, {playType}, {rank}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default WDInfoDisplay;
