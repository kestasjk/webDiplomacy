/**
 * What the board sees, sent back to the server: errors to api.php?route=client/error, which writes them to
 * the site's error log beside the server's own, and timings to client/metrics, which status.php lists.
 *
 * The classic pages do the same from javascript/clientlog.js. Nothing in here may throw or reject: a board
 * is never worth breaking to report on it, and these are called from error handlers.
 */

// The site root, which the board is served from a folder within, as in gameSource.ts. Kept here rather than
// imported so that the data layer can report without this module and that one depending on each other.
const siteRoot = "../";

const MAX_ERRORS_PER_LOAD = 5;

const reported: { [signature: string]: boolean } = {};
let errorsReported = 0;

interface ClientError {
  message?: string;
  source?: string;
  line?: number;
  column?: number;
  stack?: string;
  componentStack?: string;
}

function post(route: string, payload: unknown): void {
  try {
    const url = `${siteRoot}api.php?route=${route}`;
    const body = JSON.stringify(payload);

    // A beacon still goes when the page is being closed, which is when a board that has broken tends to be
    if (navigator.sendBeacon) {
      navigator.sendBeacon(url, new Blob([body], { type: "application/json" }));
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(body);
  } catch (e) {
    // Reporting is never worth an error of its own
  }
}

/**
 * Report one error. The same error is sent once per page load, and a board failing in a loop stops after
 * MAX_ERRORS_PER_LOAD; the server counts and de-duplicates too, but a broken board shouldn't spend the
 * user's bandwidth saying so.
 */
export function reportClientError(
  kind: "script" | "promise" | "react",
  error: ClientError,
): void {
  try {
    if (errorsReported >= MAX_ERRORS_PER_LOAD) return;

    const signature = `${kind}|${error.message || ""}|${error.source || ""}|${
      error.line || 0
    }`;
    if (reported[signature]) return;
    reported[signature] = true;
    errorsReported += 1;

    post("client/error", {
      kind,
      message: String(error.message || "Unknown error").slice(0, 1000),
      source: String(error.source || "").slice(0, 500),
      line: error.line || 0,
      column: error.column || 0,
      // Whether the browser had translated the page, which rewrites text nodes behind React's back and is
      // the usual cause of a removeChild error; in the trace rather than the message so it isn't signed on
      stack: `${String(error.stack || "").slice(0, 4000)}${
        /\btranslated-/.test(document.documentElement.className)
          ? "\n(the browser had translated this page)"
          : ""
      }`,
      componentStack: String(error.componentStack || "").slice(0, 4000),
      url: String(window.location.href).slice(0, 500),
    });
  } catch (e) {
    // As above
  }
}

/**
 * Add to one of the counters status.php lists. The name has to be one the server knows
 * (libMetrics::clientParts()); anything else is ignored there.
 */
export function reportClientMetric(name: string, ms?: number): void {
  post("client/metrics", {
    metrics: [
      {
        name,
        ms:
          typeof ms === "number" && Number.isFinite(ms) ? Math.round(ms) : null,
      },
    ],
  });
}

/**
 * Report a name the first time it is given, for a board which loads once but re-renders constantly.
 */
const reportedOnce: { [name: string]: boolean } = {};
export function reportClientMetricOnce(name: string, ms?: number): void {
  if (reportedOnce[name]) return;
  reportedOnce[name] = true;
  reportClientMetric(name, ms);
}

/**
 * Catch what React doesn't: errors outside a render, and rejected promises nothing handled. Called once, as
 * early as the board starts.
 */
export function installClientErrorReporting(): void {
  window.addEventListener("error", (event) => {
    // An element's error (a failed image or script tag) has no message and isn't the board's to fix
    if (!event || !event.message) return;

    reportClientError("script", {
      message: event.message,
      source: event.filename,
      line: event.lineno,
      column: event.colno,
      stack: event.error && event.error.stack,
    });
  });

  window.addEventListener("unhandledrejection", (event) => {
    const reason = event ? event.reason : null;

    reportClientError("promise", {
      message:
        (reason && (reason.message || String(reason))) ||
        "Unhandled promise rejection",
      stack: reason && reason.stack,
    });
  });
}
