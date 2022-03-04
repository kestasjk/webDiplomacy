import * as d3 from "d3";

export default function removeArrow(
  /**
   * id is the id of the arrowline to be removed
   */
  id: string,
  /**
   * Map SVG element
   */
  svgMap: SVGSVGElement,
): void {
  const d3MapSelector = d3.select(svgMap);
  const arrowIdTag = id.split("__")[1];
  d3MapSelector.select(`#${id}`).remove();
  d3MapSelector.select(`#arrowhead__${arrowIdTag}`).remove();
}
