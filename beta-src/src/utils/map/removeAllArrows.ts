import * as d3 from "d3";

export default function removeAllArrows(): void {
  d3.selectAll("line").remove();
}
