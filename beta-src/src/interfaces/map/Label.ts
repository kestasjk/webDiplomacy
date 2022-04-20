import React from "react";
import TerritoryLabel from "../../types/UnitLabel";
import { AbsoluteCoordinates } from "./AbsoluteCoordinates";

export interface Label extends AbsoluteCoordinates {
  name: TerritoryLabel;
  style?: React.CSSProperties;
  text?: string;
}
