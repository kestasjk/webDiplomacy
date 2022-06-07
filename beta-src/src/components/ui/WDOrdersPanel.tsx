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
} from "@mui/material";
import Device from "../../enums/Device";
import WDCountryTable from "./WDCountryTable";
import { CountryTableData } from "../../interfaces/CountryTableData";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { IOrderDataHistorical } from "../../models/Interfaces";

interface WDOrdersPanelProps {
  orders: IOrderDataHistorical[];
  allCountries: CountryTableData[];
}

const WDOrdersPanel: React.FC<WDOrdersPanelProps> = function ({
  orders,
  allCountries,
}): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);

  const orderStringsByPower: { [key: string]: string[] } = {};
  // orders.forEach((order) => {});

  return (
    <div>
      <Table aria-label="country info table" size="small" stickyHeader>
        <TableBody>
          {allCountries.map((country) => (
            <React.Fragment key={country.power}>
              <TableRow>
                <TableCell align="left">
                  <span style={{ color: country.color, fontWeight: 700 }}>
                    {country.power.toUpperCase()}
                  </span>
                </TableCell>
              </TableRow>
            </React.Fragment>
          ))}
        </TableBody>
      </Table>
    </div>
  );
};

export default WDOrdersPanel;
