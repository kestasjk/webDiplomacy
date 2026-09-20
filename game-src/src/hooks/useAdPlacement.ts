import * as React from "react";
import {
  AdPlacement,
  adsEnabled,
  getAdPlacement,
  getRawViewportSize,
} from "../utils/adPlacement";
import { useAppSelector } from "../state/hooks";

const samePlacement = (a: AdPlacement, b: AdPlacement): boolean =>
  a.mode === b.mode &&
  a.insets.top === b.insets.top &&
  a.insets.left === b.insets.left &&
  a.insets.right === b.insets.right;

/**
 * Tracks which dedicated ad area (top banner / side rails / none) applies
 * to the current window size and user. Whether this user gets ads at all
 * is decided by shouldShowAds in javascript/adsense.js, which is passed
 * the user object once the game overview has loaded (and falls back to
 * the login cookie before then). The area is chosen from the raw window
 * size, not the ad-adjusted viewport returned by useViewport, to avoid a
 * feedback loop between the reserved ad space and the layout decision.
 */
export default function useAdPlacement(): AdPlacement {
  // Select just the boolean, not the overview object: the overview is
  // replaced wholesale on every game/overview refresh, and subscribing to
  // it would re-render App (and remount the game UI) each time.
  const showAds = useAppSelector(({ game }) => adsEnabled(game.overview.user));

  const [placement, setPlacement] = React.useState<AdPlacement>(() => {
    const { width, height } = getRawViewportSize();
    return getAdPlacement(width, height, showAds);
  });

  React.useEffect(() => {
    const update = () => {
      const { width, height } = getRawViewportSize();
      const next = getAdPlacement(width, height, showAds);
      setPlacement((prev) => (samePlacement(prev, next) ? prev : next));
    };
    update();
    window.addEventListener("resize", update);
    window.addEventListener("orientationchange", update);
    return () => {
      window.removeEventListener("resize", update);
      window.removeEventListener("orientationchange", update);
    };
  }, [showAds]);

  return placement;
}
