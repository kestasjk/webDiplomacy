import * as React from "react";
import { useTheme } from "@mui/material";
import WDMapController from "../map/WDMapController";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import debounce from "../../utils/debounce";

const getDevice = ({ width, height }): Device => {
  const theme = useTheme();

  const desktop = width >= theme.breakpoints.values.desktop;
  if (desktop) {
    return Device.DESKTOP;
  }

  const tabletLandscape =
    width >= theme.breakpoints.values.tabletLandscape &&
    height <= theme.breakpoints.values.tablet;
  if (tabletLandscape) {
    return Device.TABLET_LANDSCAPE;
  }

  const mobileLgLandscape =
    width >= theme.breakpoints.values.mobileLgLandscape &&
    height <= theme.breakpoints.values.mobileLg;
  if (mobileLgLandscape) {
    return Device.MOBILE_LG_LANDSCAPE;
  }

  const tablet = width >= theme.breakpoints.values.tablet;
  if (tablet) {
    return Device.TABLET;
  }

  const mobileLg =
    width >= theme.breakpoints.values.mobileLg &&
    height >= theme.breakpoints.values.mobileLgLandscape;
  if (mobileLg) {
    return Device.MOBILE_LG;
  }

  const mobileLandscape = width >= theme.breakpoints.values.mobileLandscape;
  if (mobileLandscape) {
    return Device.MOBILE_LANDSCAPE;
  }

  return Device.MOBILE;
};

const WDMain: React.FC = function (): React.ReactElement {
  const [viewport, setViewport] = useViewport();

  React.useLayoutEffect(() => {
    const updateViewport = debounce(() => {
      setViewport();
    }, 500);
    window.addEventListener("resize", updateViewport[0]);
    return () => window.removeEventListener("resize", updateViewport[0]);
  }, []);

  const device = getDevice(viewport);
  const key = `${device}-${viewport.width}-${viewport.height}`;
  console.log(`DEVICE: ${device}`); // todo remove this once devices are verified correct
  return (
    <WDMapController
      device={device}
      key={key}
      viewportHeight={viewport.height}
      viewportWidth={viewport.width}
    />
  );
};

export default WDMain;
