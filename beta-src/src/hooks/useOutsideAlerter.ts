// from https://stackoverflow.com/questions/32553158/detect-click-outside-react-component
import React, { useRef, useEffect } from "react";

/**
 * Hook that alerts clicks outside of the passed ref
 */
function useOutsideAlerter(refs, handler) {
  useEffect(() => {
    /**
     * Alert if clicked on outside of element
     */
    function handleClickOutside(event) {
      console.log("SAW A CLICK");
      // eslint-disable-next-line no-restricted-syntax
      for (const ref of refs) {
        if (ref.current && ref.current.contains(event.target)) {
          return;
        }
      }
      console.log("ITS OUTSIDE");
      handler();
    }
    // Bind the event listener
    document.addEventListener("click", handleClickOutside);
    return () => {
      // Unbind the event listener on clean up
      document.removeEventListener("click", handleClickOutside);
    };
  }, [...refs]);
}

export default useOutsideAlerter;
