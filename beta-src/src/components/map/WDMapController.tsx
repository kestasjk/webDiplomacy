import * as React from "react";
import * as d3 from "d3";
import { Devices } from "../../interfaces";
import Scale from "../../types/Scale";
import WDMap from "./WDMap";

const Scales: Scale = {
  desktop: [0.65, 3],
  mobileLg: [0.82, 1.6],
  mobileLgLandscape: [0.3, 1.6],
  mobile: [0.7, 1.6],
  mobileLandscape: [0.27, 1.6],
  tablet: [0.77, 3],
  tabletLandscape: [0.6, 3],
};

const getInitialScaleForDevice = (device: keyof Devices): number[] => {
  if (Object.keys(Scales).includes(device)) {
    return Scales[device];
  }
  return Scales.desktop;
};

interface WDMapControllerProps {
  device: keyof Devices;
  viewportHeight: number;
  viewportWidth: number;
}

const WDMapController: React.FC<WDMapControllerProps> = function ({
  device,
  viewportHeight,
  viewportWidth,
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
    const gameBoardAreaSelection = fullMap.select("#outlines");
    const gameBoardAreaNode = gameBoardAreaSelection.node();
    const gameBoardAreaRect = gameBoardAreaNode.getBoundingClientRect();

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

    let translateX: number;
    let translateY: number;

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

    const zoom = ({ transform }) => {
      contained.attr("transform", transform);
    };

    const d3Zoom = d3
      .zoom()
      .translateExtent([
        [0, 0],
        [mapOriginalWidth, mapOriginalHeight],
      ])
      .scaleExtent([scaleMin, scaleMax])
      .on("zoom", zoom);

    fullMap
      .call(d3Zoom)
      .call(
        d3Zoom.transform,
        d3.zoomIdentity.translate(translateX, translateY).scale(scaleMin),
      )
      .on("wheel", (e) => e.preventDefault());
  });

  const preserveAspectRationDefer = "";
  const preserveAspectRatioAlign = "xMidYMid";
  const preserveAspectRatioMeetOrSlice = "meet";
  const preserveAspectRatio =
    `${preserveAspectRationDefer} ${preserveAspectRatioAlign} ${preserveAspectRatioMeetOrSlice}`.trim();

  return (
    <WDMap
      height={viewportHeight}
      key={device}
      preserveAspectRatio={preserveAspectRatio}
      svgElement={svgElement}
      width={viewportWidth}
    />
  );
};

export default WDMapController;
