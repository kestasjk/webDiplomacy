import * as React from "react";
import { useTheme } from "@mui/material";
import WDMapController from "../map/WDMapController";
import { Devices, Viewport } from "../../interfaces";
import debounce from "../../utils/debounce";

const getDevice = ({ width, height }): keyof Devices => {
  const theme = useTheme();

  const desktop = width >= theme.breakpoints.values.desktop;
  if (desktop) {
    return "desktop";
  }

  const tabletLandscape =
    width >= theme.breakpoints.values.tabletLandscape &&
    height <= theme.breakpoints.values.tablet;
  if (tabletLandscape) {
    return "tabletLandscape";
  }

  const mobileLgLandscape =
    width >= theme.breakpoints.values.mobileLgLandscape &&
    height <= theme.breakpoints.values.mobileLg;
  if (mobileLgLandscape) {
    return "mobileLgLandscape";
  }

  const tablet = width >= theme.breakpoints.values.tablet;
  if (tablet) {
    return "tablet";
  }

  const mobileLg =
    width >= theme.breakpoints.values.mobileLg &&
    height >= theme.breakpoints.values.mobileLgLandscape;
  if (mobileLg) {
    return "mobileLg";
  }

  const mobileLandscape = width >= theme.breakpoints.values.mobileLandscape;
  if (mobileLandscape) {
    return "mobileLandscape";
  }

  return "mobile";
};

const getViewport = (): Viewport => {
  let { height, width } = window.visualViewport;
  const { scale } = window.visualViewport;
  height = Math.round(height * scale);
  width = Math.round(width * scale);
  return {
    height,
    scale,
    width,
  };
};

const WDMain: React.FC = function (): React.ReactElement {
  const [viewport, setViewport] = React.useState<Viewport>(getViewport());

  React.useLayoutEffect(() => {
    const updateViewport = debounce(() => {
      setViewport(getViewport());
    }, 1000);
    window.addEventListener("resize", updateViewport[0]);
    return () => window.removeEventListener("resize", updateViewport[0]);
  }, []);

  const device = getDevice(viewport);
  const key = `${device}-${viewport.width}-${viewport.height}`;
  console.log(`DEVICE: ${device}`);
  return (
    <WDMapController
      key={key}
      viewportWidth={viewport.width}
      viewportHeight={viewport.height}
      device={device}
    />
  );
};

export default WDMain;
