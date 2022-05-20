import * as React from "react";

const { useEffect, useRef } = React;

type TimeoutFunction = () => unknown | void;

export default function useTimeout(callback: TimeoutFunction, delay: number) {
  const savedCallback = useRef<TimeoutFunction | null>(null);

  // Remember the latest callback.
  useEffect(() => {
    savedCallback.current = callback;
  });

  // Set up the interval.
  useEffect(() => {
    function tick() {
      if (savedCallback.current !== null) {
        savedCallback.current();
      }
    }
    const id = setTimeout(tick, delay);
    return () => clearTimeout(id);
  }, [delay]);
}
