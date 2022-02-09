import * as React from "react";
import * as d3 from "d3";

import WDMap from "./WDMap";
import { Devices } from "../../interfaces/Devices";

interface WDMapController {
  device: keyof Devices;
  viewportHeight: number;
  viewportWidth: number;
}

type Scale = {
  [key in keyof Devices]: number[];
};

const Scales: Scale = {
  desktop: [0.65, 3],
  tablet: [0.77, 3],
  tabletLandscape: [0.77, 3],
  mobileLg: [0.82, 1.6],
  mobileLgLandscape: [0.3, 1.6],
  mobile: [0.7, 1.6],
  mobileLandscape: [0.27, 1.6],
};

const getInitialScaleForDevice = (device: keyof Devices): number[] => {
  if (Object.keys(Scales).includes(device)) {
    return Scales[device];
  }
  return Scales.desktop;
};

const WDMapController: React.FC<WDMapController> = function ({
  device,
  viewportWidth,
  viewportHeight,
}): React.ReactElement {
  const svgElement = React.useRef<HTMLElement & SVGElement>(null);
  const mapOriginalWidth = 4018;
  const mapOriginalHeight = 2002;

  const initialScaleForDevice = getInitialScaleForDevice(device);
  const scaleMin = initialScaleForDevice[0];
  const scaleMax = initialScaleForDevice[1];

  React.useEffect(() => {
    const fullMap = d3.select(svgElement.current);
    const contained = fullMap.select("#container");
    const width = Number(fullMap.attr("width"));
    const height = Number(fullMap.attr("height"));
    const zoom = ({ transform }) => {
      console.log({
        transform,
      });
      contained.attr("transform", transform);
    };

    const gameBoardAreaSelection = fullMap.select("#outlines");
    const gameBoardAreaNode = gameBoardAreaSelection.node();
    const gameBoardAreaRect = gameBoardAreaNode.getBoundingClientRect();

    const d3Zoom = d3
      .zoom()
      .translateExtent([
        [0, 0],
        [mapOriginalWidth, mapOriginalHeight],
      ])
      .scaleExtent([scaleMin, scaleMax])
      .on("zoom", zoom);

    const translatedGameBoardAreaHeight = gameBoardAreaRect.height * scaleMin;
    const translatedGameBoardAreaY = gameBoardAreaRect.top * scaleMin;

    const translatedGameBoardAreaWidth = gameBoardAreaRect.width * scaleMin;
    const translatedGameBoardAreaX = gameBoardAreaRect.left * scaleMin;

    const nonPlayableHorizontalArea = Math.abs(
      viewportWidth - translatedGameBoardAreaWidth,
    );
    const horizontalPadding = Math.abs(nonPlayableHorizontalArea / 2);

    const nonPlayabableVerticalArea = Math.abs(
      viewportHeight - translatedGameBoardAreaHeight,
    );
    const verticalPadding = Math.abs(nonPlayabableVerticalArea / 2);

    let translateX;
    let translateY;

    if (viewportHeight >= translatedGameBoardAreaHeight) {
      translateY = -translatedGameBoardAreaY + verticalPadding;
    } else {
      translateY = -translatedGameBoardAreaY - verticalPadding;
    }

    if (viewportWidth >= translatedGameBoardAreaWidth) {
      translateX = -translatedGameBoardAreaX + horizontalPadding;
    } else {
      translateX = -translatedGameBoardAreaX - horizontalPadding;
    }

    console.log({
      translateX,
      translateY,
      translatedGameBoardAreaHeight,
      translatedGameBoardAreaWidth,
      translatedGameBoardAreaY,
      translatedGameBoardAreaX,
      nonPlayabableVerticalArea,
      nonPlayableHorizontalArea,
      horizontalPadding,
      verticalPadding,
    });

    fullMap
      .call(d3Zoom)
      .call(
        d3Zoom.transform,
        d3.zoomIdentity.translate(translateX, translateY).scale(scaleMin),
      )
      .on("wheel", (e) => e.preventDefault());

    console.log({
      viewportWidth,
      viewportHeight,
      width,
      height,
      device,
      scaleMin,
    });
  });

  const preserveAspectRationDefer = "";
  const preserveAspectRatioAlign = "xMidYMid";
  const preserveAspectRatioMeetOrSlice = "meet";
  const preserveAspectRatio =
    `${preserveAspectRationDefer} ${preserveAspectRatioAlign} ${preserveAspectRatioMeetOrSlice}`.trim();

  return (
    <WDMap
      key={device}
      width={viewportWidth}
      height={viewportHeight}
      preserveAspectRatio={preserveAspectRatio}
      style={{}}
      svgElement={svgElement}
    />
  );
};

export default WDMapController;
