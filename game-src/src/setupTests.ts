// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import "@testing-library/jest-dom";

// jsdom implements neither visualViewport nor matchMedia; the app reads both
// at render time (adPlacement.ts, responsive components)
Object.defineProperty(window, "visualViewport", {
  writable: true,
  value: {
    width: 1024,
    height: 768,
    scale: 1,
    addEventListener: () => {},
    removeEventListener: () => {},
  },
});
Object.defineProperty(window, "matchMedia", {
  writable: true,
  value: (query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: () => {},
    removeListener: () => {},
    addEventListener: () => {},
    removeEventListener: () => {},
    dispatchEvent: () => false,
  }),
});

// jsdom has no SVG layout engine; the map component measures itself with getBBox
// eslint-disable-next-line @typescript-eslint/no-explicit-any
(SVGElement.prototype as any).getBBox = () => ({
  x: 0,
  y: 0,
  width: 1024,
  height: 768,
});
