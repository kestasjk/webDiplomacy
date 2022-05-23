/* eslint-disable no-bitwise */
import * as React from "react";
import { Box, Button, Stack } from "@mui/material";
import Territories from "../../../data/Territories";
import {
  gameApiSliceActions,
  gameOrder,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import WDBuildUnitButtons from "./WDBuildUnitButtons";
import Territory from "../../../enums/map/variants/classic/Territory";

const WDOrderTypeButtons: React.FC = function (): React.ReactElement {
  const dispatch = useAppDispatch();
  const territoryName = Territory.KIEL;
  const unitSlotName = "main";
  const labelId = `${territoryName}-${unitSlotName}-unit-slot`;
  const label: SVGTextElement = document.getElementById(
    labelId,
  ) as unknown as SVGTextElement;
  const territory: SVGSVGElement = document.getElementById(
    `${territoryName}-territory`,
  ) as unknown as SVGSVGElement;
  if (!label) return <Box />; // throw Error(labelId);
  const x = Number(label.getAttribute("x")) + 20; // FIXME hardcoded 20
  const y = Number(label.getAttribute("y")) + 20;
  console.log({ territoryName, x, y });
  const svgX = Number(territory.getAttribute("x")) + x;
  const svgY = Number(territory.getAttribute("y")) + y;
  const r = 50;
  const rw = 10;
  const rh = 10;
  const rBorder = 20;
  const rFill = "rgba(0,0,0,.7)";
  const groupStyle: React.CSSProperties = {
    cursor: "pointer",
  };
  /*
   * r is the circle radius
  cx and cy are offsets
  rx and ry and rounded edges
   */
  return (
    <svg x={svgX} y={svgY}>
      <g>
        <rect fill="green" width={rw} height={rh} rx={rBorder} ry={rBorder} />

        <text
          style={{ zIndex: 10 }}
          fontFamily="Verdana"
          fontSize="20"
          fill="blue"
        >
          MOVE
        </text>
      </g>
    </svg>
  );
};

export default WDOrderTypeButtons;
