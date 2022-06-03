export default function arrowDispatchReceiveCoordinates(
  unitH: number, // dispatch unit height
  unitW: number, // dispatch unit width
  rh: number, // receiver height
  rw: number, // receiver width
  x1: number, // line x1
  x2: number, // line x2
  y1: number, // line y1
  y2: number, // line y2
): {
  x1: number;
  x2: number;
  y1: number;
  y2: number;
} {
  // Compute the angle that we need the arrow to go at
  // If there isn't an angle because the diff is too small, quit out immediately
  const xDiff = x2 - x1;
  const yDiff = y2 - y1;
  if (Math.abs(xDiff) <= 1e-10 && Math.abs(yDiff) <= 1e-10) {
    return {
      x1,
      x2,
      y1,
      y2,
    };
  }
  const theta = Math.atan2(yDiff, xDiff);

  // Make the arrow start at the border of the ellipse with the specified
  // width and height.
  const x1New = x1 + (unitW / 2) * Math.cos(theta);
  const y1New = y1 + (unitH / 2) * Math.sin(theta);

  // Make the arrow end at the border of the ellipse with the specified
  // width and height.
  const x2New = x2 - (rw / 2) * Math.cos(theta);
  const y2New = y2 - (rh / 2) * Math.sin(theta);

  // If the result would give an arrow that points backwards, due to overlap
  // between the source and destination then give up and don't do adjustment
  // for the source and receiver width and height.
  // Determine if it points backwards by dot product with the original vector
  const xDiffNew = x2New - x1New;
  const yDiffNew = y2New - y1New;
  if (xDiffNew * xDiff + yDiffNew * yDiff <= 1e-10) {
    return {
      x1,
      x2,
      y1,
      y2,
    };
  }

  return {
    x1: x1New,
    x2: x2New,
    y1: y1New,
    y2: y2New,
  };
}
