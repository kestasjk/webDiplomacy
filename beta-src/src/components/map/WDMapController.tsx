import * as React from "react";
import * as d3 from "d3";
import { useTheme } from "@mui/material/styles";
import Device from "../../enums/Device";
import getInitialViewTranslation from "../../utils/map/getInitialViewTranslation";
import Scale from "../../types/Scale";
import WDMap from "./WDMap";
import { Viewport } from "../../interfaces";
import drawArrow from "../../utils/drawArrow";
import ArrowType from "../../enums/ArrowType";

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
  const svgElement = React.useRef<SVGSVGElement>(null);
  const [arrowConfigurations, setArrowConfigurations] = React.useState<any>([]);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);

  const theme = useTheme();
  const color = theme.palette.retreat;

  React.useLayoutEffect(() => {
    if (svgElement.current) {
      const fullMap = d3.select(svgElement.current);
      const contained = fullMap.select("#container");
      const containedRect = (contained.node() as SVGSVGElement).getBBox();
      const gameBoardAreaRect = (
        fullMap.select("#outlines").node() as SVGSVGElement
      ).getBBox();

      const { scale, x, y } = getInitialViewTranslation(
        containedRect,
        gameBoardAreaRect,
        scaleMin,
        viewport,
      );

      const zoom = ({ transform }) => {
        contained.attr("transform", transform);
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
        .on("wheel", (e) => e.preventDefault())
        .call(d3Zoom)
        .call(d3Zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale));
    }
  }, [svgElement, viewport]);

  React.useEffect(() => {
    const fullMap = d3.select(svgElement.current);
    const contained = fullMap.select("#container");
    contained
      .select("#centers")
      .selectAll("path")
      .on("click", (e) => {
        e.preventDefault();

        if (
          arrowConfigurations.length === 0 ||
          arrowConfigurations[arrowConfigurations.length - 1].target.element
        ) {
          setArrowConfigurations([
            ...arrowConfigurations,
            {
              source: {
                element: e.target.id,
                actionType: color,
              },
              target: {
                element: undefined,
              },
            },
          ]);
        } else if (
          (arrowConfigurations[arrowConfigurations.length - 1].source.element,
          !arrowConfigurations[arrowConfigurations.length - 1].target.element)
        ) {
          const updatedState = [...arrowConfigurations];
          updatedState[arrowConfigurations.length - 1].target.element =
            e.target.id;
          setArrowConfigurations(updatedState);
          drawArrow(
            arrowConfigurations[arrowConfigurations.length - 1].source
              .actionType,
            arrowConfigurations[arrowConfigurations.length - 1].source.element,
            fullMap.node(),
            arrowConfigurations[arrowConfigurations.length - 1].target.element,
          );
        }
      });
  }, [arrowConfigurations]);

  return (
    <div
      style={{
        width: viewport.width,
        height: viewport.height,
      }}
    >
      <WDMap ref={svgElement} />
    </div>
  );
};

export default WDMapController;
