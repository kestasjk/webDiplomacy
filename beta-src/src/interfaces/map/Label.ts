import React from "react";
import { AbsoluteCoordinates } from "./AbsoluteCoordinates";

export interface Label extends AbsoluteCoordinates {
  style?: React.CSSProperties;
  text?: string;
}
