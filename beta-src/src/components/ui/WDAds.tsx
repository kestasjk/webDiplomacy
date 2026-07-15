import React, { useEffect, useRef } from "react";
import { AdPlacement, RAIL_WIDTH } from "../../utils/adPlacement";

/**
 * The dedicated ad areas around the game UI: a banner across the top on
 * phones/tablets, or a vertical rail down each side on wide desktops.
 *
 * WDAds only renders the AdSense units; the space they occupy is reserved
 * by useViewport and the game-area wrapper in App, so the map and the UI
 * overlays never sit underneath an ad. Which area applies is decided by
 * useAdPlacement, and the AdSense config (publisher/slot IDs, whether this
 * user gets ads at all) comes from javascript/adsense.js via window.wDAds.
 */

interface WDAdUnitProps {
  slot: string;
  format: "horizontal" | "vertical";
}

const WDAdUnit: React.FC<WDAdUnitProps> = function ({
  slot,
  format,
}): React.ReactElement {
  const insRef = useRef<HTMLModElement>(null);

  useEffect(() => {
    // Each <ins> must be handed to AdSense exactly once, after it is in the
    // DOM with a real size.
    const ins = insRef.current;
    if (!ins || ins.getAttribute("data-adsbygoogle-status")) return;
    try {
      // adsense.js only auto-loads the AdSense script when the login
      // cookie qualifies; if the shouldShowAds decision came from the
      // game API's user object instead, the script still needs loading.
      // Pushes made before it loads are queued, so the order is safe.
      if (typeof window.wDAds?.loadScript === "function") {
        window.wDAds.loadScript();
      }
      (window.adsbygoogle = window.adsbygoogle || []).push({});
    } catch {
      // The AdSense script may be blocked or unavailable; the ad area
      // simply stays empty.
    }
  }, []);

  return (
    <ins
      ref={insRef}
      className="adsbygoogle"
      style={{ display: "block", width: "100%", height: "100%" }}
      data-ad-client={window.wDAds?.client}
      data-ad-slot={slot}
      data-ad-format={format}
      data-full-width-responsive={format === "horizontal" ? "true" : undefined}
    />
  );
};

interface WDAdsProps {
  placement: AdPlacement;
}

const WDAds: React.FC<WDAdsProps> = function ({
  placement,
}): React.ReactElement {
  const slots = window.wDAds?.slots;
  const { mode, insets } = placement;
  if (mode === "NONE" || !slots) {
    return <div />;
  }

  const containerStyle: React.CSSProperties = {
    position: "fixed",
    overflow: "hidden",
    zIndex: 30,
  };

  if (mode === "TOP_BANNER") {
    return (
      <div
        style={{
          ...containerStyle,
          top: 0,
          left: 0,
          right: 0,
          height: insets.top,
          textAlign: "center",
        }}
      >
        {/* Keyed on the banner height so rotating a device remounts the
        unit and requests an ad that fits the new size. */}
        <WDAdUnit
          key={`top-${insets.top}`}
          slot={slots.top}
          format="horizontal"
        />
      </div>
    );
  }

  return (
    <>
      <div
        style={{
          ...containerStyle,
          top: 0,
          bottom: 0,
          left: 0,
          width: RAIL_WIDTH,
        }}
      >
        <WDAdUnit slot={slots.left} format="vertical" />
      </div>
      <div
        style={{
          ...containerStyle,
          top: 0,
          bottom: 0,
          right: 0,
          width: RAIL_WIDTH,
        }}
      >
        <WDAdUnit slot={slots.right} format="vertical" />
      </div>
    </>
  );
};

export default WDAds;
