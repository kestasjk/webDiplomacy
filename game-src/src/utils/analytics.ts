/*
 * Google Analytics events. gtag() is defined by public/index.html, which also sets the site parameter and the
 * logged_in / user_type user properties that go with every event.
 */

declare global {
  interface Window {
    gtag?: (...args: unknown[]) => void;
  }
}

/**
 * Send an event for an action in a game. Games with bots in them get "_bot" appended to the name (e.g.
 * submit_orders_bot), matching the PHP pages' events (libHTML::analyticsGameEventName).
 */
export default function sendGameAnalyticsEvent(
  name: string,
  playerTypes: string,
): void {
  window.gtag?.("event", playerTypes === "Members" ? name : `${name}_bot`);
}
