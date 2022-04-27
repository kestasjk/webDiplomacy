import React from "react";
import TerritoryLabel from "../../types/UnitLabel";
import { Coordinates } from "./Coordinates";

export interface Label extends Coordinates {
  name: TerritoryLabel;
  style?: React.CSSProperties;
  text?: string;
}
