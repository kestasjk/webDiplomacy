import * as React from "react";
import getDeviceUtil from "../utils/getDevice";
import Device from "../enums/Device";
import useViewport from "./useViewport";

export default function useDevice(): [Device, () => void] {
  const [viewport] = useViewport();
  const getDevice = () => {
    return getDeviceUtil(viewport);
  };
  const [device, setValue] = React.useState<Device>(getDevice());

  const setDevice = () => {
    setValue(getDevice());
  };

  return [device, setDevice];
}
