/* eslint-disable no-bitwise */
import { Box, Button, useTheme } from "@mui/material";
import * as React from "react";
import BuildUnit from "../../../enums/BuildUnit";
import Country from "../../../enums/Country";
import UIState from "../../../enums/UIState";
import WDArmyIcon from "../../ui/units/WDArmyIcon";
import WDFleetIcon from "../../ui/units/WDFleetIcon";

export interface BuildData {
  availableOrder: string;
  country: Country;
  canBuild: BuildUnit;
  toTerrID: string;
}

interface BuildUnitButtonsProps extends BuildData {
  clickCallback: (
    availableOrder: BuildData["availableOrder"],
    canBuild: BuildData["canBuild"],
    toTerrID: BuildData["toTerrID"],
  ) => void;
}

interface BuildButtonProps {
  clickCallback: () => void;
}

const BuildButton: React.FC<BuildButtonProps> = function ({
  children,
  clickCallback,
}): React.ReactElement {
  const theme = useTheme();
  const side = 40;
  const viewBox = `0 0 50 50`;
  return (
    <Box sx={{ margin: "10px 6px" }}>
      <Button
        onClick={clickCallback}
        sx={{
          background: theme.palette.secondary.main,
          borderRadius: "50%",
          minWidth: 0,
          padding: 0,
          "&:hover": { background: theme.palette.secondary.main },
        }}
      >
        <svg viewBox={viewBox} width={side} height={side}>
          {children}
        </svg>
      </Button>
    </Box>
  );
};

const WDBuildUnitButtons: React.FC<BuildUnitButtonsProps> = function ({
  availableOrder,
  canBuild,
  country,
  toTerrID,
  clickCallback,
}): React.ReactElement {
  const buildOptions = {
    [BuildUnit.Army]: (
      <BuildButton
        clickCallback={() => {
          clickCallback(availableOrder, BuildUnit.Army, toTerrID);
        }}
      >
        <WDArmyIcon country={country} iconState={UIState.BUILD} />
      </BuildButton>
    ),
    [BuildUnit.Fleet]: (
      <BuildButton
        clickCallback={() => {
          clickCallback(availableOrder, BuildUnit.Fleet, toTerrID);
        }}
      >
        <WDFleetIcon country={country} iconState={UIState.BUILD} />
      </BuildButton>
    ),
  };
  return (
    <>
      {buildOptions[canBuild & BuildUnit.Army]}
      {buildOptions[canBuild & BuildUnit.Fleet]}
    </>
  );
};

export default WDBuildUnitButtons;
