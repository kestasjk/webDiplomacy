import React from "react";
import { AbsoluteCoordinates } from "./AbsoluteCoordinates";

export interface Label extends AbsoluteCoordinates {
  text?: string;
  style?: React.CSSProperties;
}
