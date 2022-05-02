export default function arrowDispatchReceiveCoordinates(
  positionChangeBuffer: number, // distance before changing receiver/dispatch position
  unitH: number, // dispatch unit height
  unitW: number, // dispatch unit width
  rh: number, // receiver height
  rw: number, // receiver width
  xDiff: number, // horizontal diff between receiver and dispatch
  yDiff: number, // vertical diff between receiver and dispatch
  X1: number, // line x1
  X2: number, // line x2
  Y1: number, // line y1
  Y2: number, // line y2
): {
  x1: number;
  x2: number;
  y1: number;
  y2: number;
} {
  let x1 = X1;
  let x2 = X2;
  let y1 = Y1;
  let y2 = Y2;
  if (Math.abs(xDiff) < positionChangeBuffer && yDiff < 0) {
    // dispatch: top center
    x1 += unitW / 2;
    // receiver: bottom center
    if (rw && rh) {
      x2 += rw / 2;
      y2 += rh;
    }
  } else if (Math.abs(xDiff) < positionChangeBuffer && yDiff > 0) {
    // dispatch: bottom center
    y1 += unitH;
    x1 += unitW / 2;
    // receiver: top center
    if (rw) {
      x2 += rw / 2;
    }
  } else if (
    xDiff > positionChangeBuffer &&
    yDiff < 0 &&
    Math.abs(yDiff) > positionChangeBuffer
  ) {
    // dispatch: top right
    x1 += unitW;
    // receive: bottom left
    if (rh) {
      y2 += rh;
    }
  } else if (
    xDiff > positionChangeBuffer &&
    Math.abs(yDiff) < positionChangeBuffer
  ) {
    // dispatch: center right
    y1 += unitH / 2;
    x1 += unitW;
    // receive: center left
    if (rh) {
      y2 += rh / 2;
    }
  } else if (xDiff > positionChangeBuffer && yDiff > positionChangeBuffer) {
    // dispatch: bottom right
    y1 += unitH;
    x1 += unitW;
    // receive: top left
    // nothing needs to be done for top left
  } else if (xDiff < 0 && Math.abs(yDiff) < positionChangeBuffer) {
    // dispatch: center left
    y1 += unitH / 2;
    // receive: center right
    if (rw && rh) {
      x2 += rw;
      y2 += rh / 2;
    }
  } else if (xDiff < 0 && yDiff > positionChangeBuffer) {
    // dispatch: bottom left
    y1 += unitH;
    // receive: top right
    if (rw) {
      x2 += rw;
    }
  }
  return {
    x1,
    x2,
    y1,
    y2,
  };
}
