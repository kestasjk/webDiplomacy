import * as React from "react";
import * as d3 from "d3";
import Device from "../../enums/Device";
import getInitialViewTranslation from "../../utils/map/getInitialViewTranslation";
import Scale from "../../types/Scale";
import WDMap from "./WDMap";
import { Viewport } from "../../interfaces";
import drawArrow from "../../utilities/draw-arrow";

const Scales: Scale = {
  DESKTOP: [0.65, 3],
  MOBILE_LG: [0.37, 1.6],
  MOBILE_LG_LANDSCAPE: [0.3, 1.6],
  MOBILE: [0.37, 1.6],
  MOBILE_LANDSCAPE: [0.27, 1.6],
  TABLET: [0.6275, 3],
  TABLET_LANDSCAPE: [0.6, 3],
};

const getInitialScaleForDevice = (device: Device): number[] => {
  return Scales[device];
};

interface WDMapControllerProps {
  device: Device;
  viewport: Viewport;
}

const mapOriginalWidth = 4018;
const mapOriginalHeight = 2002;

const WDMapController: React.FC<WDMapControllerProps> = function ({
  device,
  viewport,
}): React.ReactElement {
  const [arrowConfigurations, setArrowConfigurations] = React.useState<any>({
    source: {
      element: undefined,
      actionType: "",
    },
    target: {
      element: undefined,
    },
  });
  const svgElement = React.useRef(null);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  React.useLayoutEffect(() => {
    if (svgElement.current) {
      const fullMap = d3.select(svgElement.current);
      const contained = fullMap.select("#container");
      const containedRect = contained.node().getBBox();
      const gameBoardAreaRect = fullMap.select("#outlines").node().getBBox();

      const { scale, x, y } = getInitialViewTranslation(
        containedRect,
        gameBoardAreaRect,
        scaleMin,
        viewport,
      );

      const zoom = ({ transform }) => {
        contained.attr("transform", transform);
        if (arrowConfigurations.target.element) {
          drawArrow(
            arrowConfigurations.source.actionType,
            arrowConfigurations.source.element,
            fullMap,
            arrowConfigurations.target.element,
          );
        }
      };

      const d3Zoom = d3
        .zoom()
        .translateExtent([
          [0, 0],
          [mapOriginalWidth, mapOriginalHeight],
        ])
        .scaleExtent([scale, scaleMax])
        .on("zoom", zoom);

      fullMap
        .on("wheel", (e) => {
          e.preventDefault();
        })
        .call(d3Zoom)
        .call(d3Zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale));

      d3.selectAll("path").on("click", (e) => {
        e.preventDefault();

        if (!arrowConfigurations.source.element) {
          const updatedState = { ...arrowConfigurations };
          updatedState.source.element = e.target.id;
          updatedState.source.actionType = "moveConvoy";
          setArrowConfigurations(updatedState);
        } else if (
          arrowConfigurations.source.element &&
          !arrowConfigurations.target.element
        ) {
          const updatedState = { ...arrowConfigurations };
          updatedState.target.element = e.target.id;
          setArrowConfigurations(updatedState);
          drawArrow(
            arrowConfigurations.source.actionType,
            arrowConfigurations.source.element,
            fullMap,
            arrowConfigurations.target.element,
          );
        }
      });
    }
  }, [svgElement, viewport]);

  return (
    <div
      style={{
        width: viewport.width,
        height: viewport.height,
      }}
    >
      <WDMap svgElement={svgElement} />
    </div>
  );
};

export default WDMapController;
