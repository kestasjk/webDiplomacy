import ApiRoute from "../enums/ApiRoute";
import { getGameApiRequest, postGameApiRequest } from "./api";

/**
 * Keeps this browser's Web Push subscription in sync while the user is in the beta app.
 *
 * The opt-in UX (permission prompt banner) lives in the classic UI (javascript/push.js); here we
 * only quietly refresh an existing subscription, so the server always has current keys for users
 * who already granted notification permission. Does nothing unless the server says push is
 * enabled for this user (feature-flagged during the trial).
 */

// The applicationServerKey must be the raw P-256 public key bytes
function urlBase64ToUint8Array(base64String: string): Uint8Array {
  const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; i += 1) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export default async function syncPushSubscription(): Promise<void> {
  if (
    !("serviceWorker" in navigator) ||
    !("PushManager" in window) ||
    !("Notification" in window) ||
    Notification.permission !== "granted"
  ) {
    return;
  }

  const response = await getGameApiRequest(ApiRoute.PUSH_CONFIG, {});
  const config = response.data;
  if (!config.success || !config.data.enabled) {
    return;
  }

  // The root-scoped worker covers both the classic pages and this app
  const registration = await navigator.serviceWorker.register(
    "/service-worker.js",
    { scope: "/" },
  );
  const subscription = await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(config.data.vapidPublicKey),
  });
  const json = subscription.toJSON();
  if (!json.endpoint || !json.keys) {
    return;
  }
  await postGameApiRequest(ApiRoute.PUSH_SUBSCRIBE, {
    endpoint: json.endpoint,
    p256dh: json.keys.p256dh,
    auth: json.keys.auth,
  });
}
