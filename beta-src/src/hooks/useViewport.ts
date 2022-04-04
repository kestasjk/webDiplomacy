import * as React from "react";
import { Viewport } from "../interfaces";

export default function useViewport(): [Viewport, () => void] {
  const getViewport = (): Viewport => {
    let { height, width } = window.visualViewport;
    const { scale } = window.visualViewport;
    height = Math.round(height * scale);
    width = Math.round(width * scale);
    return {
      height,
      width,
      scale,
    };
  };

  const [viewport, setValue] = React.useState<Viewport>(getViewport());

  const setViewport = () => {
    setValue(getViewport());
  };

  React.useEffect(() => {
    window.addEventListener("resize", setViewport);
    return () => window.removeEventListener("resize", setViewport);
  }, []);

  return [viewport, setViewport];
}
