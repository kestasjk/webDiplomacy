import * as React from "react";
import {
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
  title: string;
  gameTime: string;
  phase: string;
  season: string;
  year: string;
  gameType: string;
  playType: string;
  rank: string;
}

const WDInfoDisplay: React.FC<WDInfoDisplayProps> = function ({
  title,
  gameTime,
  phase,
  season,
  year,
  gameType,
  playType,
  rank,
}) {
  const tableCellStyles = {
    p: "0px 5px 0px 0px",
    fontSize: "0.7rem",
    border: "none",
  };

  return (
    <TableContainer>
      <Table
        sx={{
          maxWidth: "250px",
        }}
        size="small"
        aria-label="a dense table"
      >
        <TableHead>
          <TableRow>
            <TableCell
              sx={{
                fontWeight: 600,
                p: "2px 5px 5px 0px",
                border: "none",
              }}
            >
              <div
                /**
                 * by applying CSS properties, the title of the game would be able to wrap until the second line
                 * for the second line display '...' to show it being cut-off
                 */
                style={{
                  lineHeight: "1.2rem",
                  maxHeight: "2.4rem",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                  wordWrap: "break-word",
                  display: "-webkit-box",
                  WebkitLineClamp: 2,
                  WebkitBoxOrient: "vertical",
                }}
              >
                {title}
              </div>
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
