import * as React from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TableContainer,
} from "@mui/material";
import IntegerRange from "../../types/IntegerRange";
import WDLineClamp from "./WDLineClamp";
import Device from "../../enums/Device";
import getDevice from "../../utils/getDevice";
import useViewport from "../../hooks/useViewport";

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
}) {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const isMobile =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE ||
    device === Device.MOBILE_LG;

  const width = isMobile ? 260 : 320;
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
              Pot: {potNumber} - {season} {year} - <b>{phase}</b>
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell sx={tableCellStyles}>{alternatives}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default WDInfoDisplay;
