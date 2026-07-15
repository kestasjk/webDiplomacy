import webDiplomacyTheme from "../webDiplomacyTheme";
import GameOverviewResponse from "../state/interfaces/GameOverviewResponse";

/**
 * Layout logic for the dedicated ad areas that surround the game UI.
 *
 * The ad configuration itself (publisher ID, slot IDs, and the
 * shouldShowAds gating function) is owned by javascript/adsense.js — a
 * plain, hand-editable script that runs before the React bundle and
 * exposes itself on window.wDAds. The React app passes the game API's
 * user object into shouldShowAds so the gating rules can be changed
 * without recompiling this bundle.
 *
 * On phones and tablets a fixed-height banner area is reserved across the
 * top of the screen; on wide desktop screens a vertical rail is reserved
 * on the left and right instead. The reserved space is subtracted from the
 * viewport in useViewport, so the map and the UI overlays lay out in the
 * remaining game area and ads never sit on top of them.
 */

export type AdUser = GameOverviewResponse["user"];

declare global {
  interface Window {
    wDAds?: {
      client: string;
      slots: {
        top: string;
        left: string;
        right: string;
      };
      shouldShowAds: (user?: AdUser) => boolean;
      loadScript: () => void;
    };
    adsbygoogle?: unknown[];
  }
}

export const RAIL_WIDTH = 160;
export const TOP_BANNER_HEIGHT_SMALL = 50;
export const TOP_BANNER_HEIGHT_LARGE = 90;

// Side rails are only used when the game area left over after reserving
// them still counts as a desktop-sized viewport; otherwise reserving the
// rails would knock the whole UI down into a tablet/mobile layout
// (see getDevice). Narrower desktops fall back to the top banner.
const SIDE_RAILS_MIN_WIDTH =
  webDiplomacyTheme.breakpoints.values.desktop + 2 * RAIL_WIDTH;

export type AdMode = "NONE" | "TOP_BANNER" | "SIDE_RAILS";

export interface AdInsets {
  top: number;
  left: number;
  right: number;
}

export interface AdPlacement {
  mode: AdMode;
  insets: AdInsets;
}

export function adsEnabled(user?: AdUser): boolean {
  if (typeof window.wDAds?.shouldShowAds !== "function") {
    return false;
  }
  try {
    return window.wDAds.shouldShowAds(user) === true;
  } catch {
    // shouldShowAds is hand-edited on the server; a broken edit should
    // just mean no ads, not a broken game UI.
    return false;
  }
}

export function getRawViewportSize(): { width: number; height: number } {
  const { height, width, scale } = window.visualViewport;
  return {
    width: Math.round(width * scale),
    height: Math.round(height * scale),
  };
}

export function getAdPlacement(width: number, showAds: boolean): AdPlacement {
  if (!showAds) {
    return { mode: "NONE", insets: { top: 0, left: 0, right: 0 } };
  }
  if (width >= SIDE_RAILS_MIN_WIDTH) {
    return {
      mode: "SIDE_RAILS",
      insets: { top: 0, left: RAIL_WIDTH, right: RAIL_WIDTH },
    };
  }
  const top =
    width >= webDiplomacyTheme.breakpoints.values.tablet
      ? TOP_BANNER_HEIGHT_LARGE
      : TOP_BANNER_HEIGHT_SMALL;
  return { mode: "TOP_BANNER", insets: { top, left: 0, right: 0 } };
}
