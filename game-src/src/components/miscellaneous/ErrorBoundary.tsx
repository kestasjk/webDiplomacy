import React, { ErrorInfo, ReactElement, ReactNode } from "react";
import { reportClientError } from "../../utils/clientLog";

interface ErrorBoundaryProps {
  children: ReactNode;
}

interface ErrorBoundaryState {
  failed: boolean;
}

/**
 * The last resort around the board: React unmounts a tree whose render threw, so without this a single bad
 * value leaves the player looking at a blank page with no idea what happened and nothing in any log. The
 * error goes to the site's error log through client/error, and the player is told to reload.
 */
export default class ErrorBoundary extends React.Component<
  ErrorBoundaryProps,
  ErrorBoundaryState
> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = { failed: false };
  }

  static getDerivedStateFromError(): ErrorBoundaryState {
    return { failed: true };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
    reportClientError("react", {
      message: error.message,
      stack: error.stack,
      componentStack: errorInfo.componentStack,
    });
  }

  render(): ReactElement | ReactNode {
    const { failed } = this.state;
    const { children } = this.props;

    if (!failed) return children;

    return (
      <div
        style={{
          padding: "2em",
          fontFamily: "sans-serif",
          textAlign: "center",
        }}
      >
        <h2>Something went wrong showing this game</h2>
        <p>
          The error has been reported. Reloading usually fixes it; the game
          itself is not affected.
        </p>
        <p>
          <button type="button" onClick={() => window.location.reload()}>
            Reload
          </button>
        </p>
      </div>
    );
  }
}
