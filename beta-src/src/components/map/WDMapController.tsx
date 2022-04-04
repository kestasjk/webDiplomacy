import * as React from "react";
import * as d3 from "d3";
import Device from "../../enums/Device";
import getInitialViewTranslation from "../../utils/map/getInitialViewTranslation";
import Scale from "../../types/Scale";
import WDMap from "./WDMap";
import { Viewport } from "../../interfaces";
import debounce from "../../utils/debounce";
import { useAppDispatch } from "../../state/hooks";
import { gameApiSliceActions } from "../../state/game/game-api-slice";

const Scales: Scale = {
  DESKTOP: [0.45, 3],
  MOBILE_LG: [0.32, 1.6],
  MOBILE_LG_LANDSCAPE: [0.3, 1.6],
  MOBILE: [0.32, 1.6],
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

const mapOriginalWidth = 6010;
const mapOriginalHeight = 3005;

const WDMapController: React.FC<WDMapControllerProps> = function ({
  device,
  viewport,
}): React.ReactElement {
  const svgElement = React.useRef<SVGSVGElement>(null);
  const [scaleMin, scaleMax] = getInitialScaleForDevice(device);
  const dispatch = useAppDispatch();

  const clickAction = function (e) {
    const unitId = e.path[2].id;
    if (unitId.includes("unit-slot")) {
      dispatch(gameApiSliceActions.startOrder());
    }
  };

  const handleClick = debounce((e) => {
    clickAction(e);
  }, 200);

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
        .call(d3Zoom.transform, d3.zoomIdentity.translate(x, y).scale(scale))
        .on("dblclick.zoom", null)
        .on("click", (e) => {
          handleClick[0](e);
        })
        .on("dblclick", (e) => {
          handleClick[1]();
          handleClick[0](e);
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
      <WDMap ref={svgElement} />
    </div>
  );
};

export default WDMapController;
