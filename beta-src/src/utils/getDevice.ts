import { useTheme } from "@mui/material";
import Device from "../enums/Device";

export default function getDevice({
  height,
  width,
}: {
  width: number;
  height: number;
}): Device {
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

  const tablet =
    width >= theme.breakpoints.values.tablet &&
    height >= theme.breakpoints.values.tabletLandscape;
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
}
