import * as React from "react";
import { Viewport } from "../interfaces";
import {
  adsEnabled,
  getAdPlacement,
  getRawViewportSize,
} from "../utils/adPlacement";
import { useAppSelector } from "../state/hooks";

export default function useViewport(): [Viewport, () => void] {
  // Select just the boolean, not the overview object: the overview is
  // replaced wholesale on every game/overview refresh, and this hook is
  // used all over the UI — subscribing to the object would re-render
  // every consumer on every refresh.
  const showAds = useAppSelector(({ game }) => adsEnabled(game.overview.user));

  const getViewport = (): Viewport => {
    const { height, width } = getRawViewportSize();
    const { scale } = window.visualViewport;
    // The dedicated ad areas (top banner on mobile, side rails on desktop)
    // are reserved out of the window, so the viewport every layout
    // consumer sees is the game area that remains.
    const { insets } = getAdPlacement(width, height, showAds);
    return {
      height: height - insets.top,
      width: width - insets.left - insets.right,
      scale,
    };
  };

  const [viewport, setValue] = React.useState<Viewport>(getViewport());

  const setViewport = () => {
    setValue(getViewport());
  };

  // Re-registered whenever the ad gating decision changes (e.g. once the
  // game overview's user info loads), so the listeners don't keep using a
  // stale decision and the viewport updates immediately.
  React.useEffect(() => {
    setViewport();
    window.addEventListener("resize", setViewport);
    return () => window.removeEventListener("resize", setViewport);
  }, [showAds]);

  // need to update the viewport when the mobile device
  // orientation changes as well.
  React.useEffect(() => {
    window.addEventListener("orientationchange", setViewport);
    return () => window.removeEventListener("orientationchange", setViewport);
  }, [showAds]);

  return [viewport, setViewport];
}
