import { useState, useEffect, useRef } from "react";

export default function useComponentVisible(
  initialIsVisible: boolean,
  closeExternal: boolean,
) {
  const [isComponentVisible, setIsComponentVisible] =
    useState(initialIsVisible);
  const ref = useRef<HTMLDivElement>(null);

  const handleClickOutside = (event) => {
    if (ref.current && !ref.current.contains(event.target) && closeExternal) {
      setIsComponentVisible(false);
    }
  };

  useEffect(() => {
    document.addEventListener("click", handleClickOutside, true);
    return () => {
      document.removeEventListener("click", handleClickOutside, true);
    };
  });

  return { ref, isComponentVisible, setIsComponentVisible };
}
