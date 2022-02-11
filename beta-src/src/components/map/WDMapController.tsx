import * as React from "react";
import * as d3 from "d3";
import Device from "../../enums/Device";
import Scale from "../../types/Scale";
import WDMap from "./WDMap";

const Scales: Scale = {
  DESKTOP: [0.65, 3],
  MOBILE_LG: [0.82, 1.6],
  MOBILE_LG_LANDSCAPE: [0.3, 1.6],
  MOBILE: [0.7, 1.6],
  MOBILE_LANDSCAPE: [0.27, 1.6],
  TABLET: [0.77, 3],
  TABLET_LANDSCAPE: [0.6, 3],
};

const getInitialScaleForDevice = (device: Device): number[] => {
  return Scales[device];
};

interface WDMapControllerProps {
  device: Device;
  viewportHeight: number;
  viewportWidth: number;
}

const mapOriginalWidth = 4018;
const mapOriginalHeight = 2002;

const WDMapController: React.FC<WDMapControllerProps> = function ({
  device,
  viewportHeight,
  viewportWidth,
}): React.ReactElement {
  const svgElement = React.useRef(null);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  React.useLayoutEffect(() => {
    if (svgElement.current) {
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

      const nonPlayableVerticalArea = Math.abs(
        viewportHeight - translatedGameBoardAreaHeight,
      );
      const verticalPadding = Math.abs(nonPlayableVerticalArea / 2);

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
    }
  }, [svgElement]);

  return (
    <WDMap
      height={viewportHeight}
      key={device}
      svgElement={svgElement}
      width={viewportWidth}
    />
  );
};

export default WDMapController;
