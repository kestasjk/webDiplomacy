import * as d3 from "d3";

export default function drawArrow(
  /**
   * color code passed from enum ArrowColors based on move type
   */
  arrowColor: string,
  /**
   * source is the id of the element the arrow begins at
   */
  sourceElementID: string,
  /**
   * Map SVG element
   */
  svgMap: SVGSVGElement,
  /**
   * target is the id of the element the arrow ends at
   */
  targetElementID: string,
): void {
  const arrowIdNumber = `${sourceElementID}-${targetElementID}`;
  const d3MapSelector = d3.select(svgMap);

  const sourceNodeData = d3MapSelector
    .select(`#${sourceElementID}`)
    .node()
    .getBBox();
  const targetNodeData = d3MapSelector
    .select(`#${targetElementID}`)
    .node()
    .getBBox();

  d3MapSelector
    .select("defs")
    .append("marker")
    .attr("id", `arrowhead__${arrowIdNumber}`)
    .attr("markerWidth", 8)
    .attr("markerHeight", 8)
    .attr("refX", 0)
    .attr("refY", 4)
    .attr("orient", "auto")
    .append("polygon")
    .attr("points", "0 0, 8 4, 0 8")
    .attr("fill", arrowColor);

  d3MapSelector
    .select("#container")
    .append("line")
    .attr("x1", sourceNodeData.x + sourceNodeData.width / 2)
    .attr("y1", sourceNodeData.y + sourceNodeData.height / 2)
    .attr("x2", targetNodeData.x + targetNodeData.width / 2)
    .attr("y2", targetNodeData.y + targetNodeData.height / 2)
    .attr("marker-end", `url(#arrowhead__${arrowIdNumber})`)
    .attr("stroke", arrowColor)
    .attr("stroke-width", "2")
    .attr("id", `arrowline__${arrowIdNumber}`);
}
