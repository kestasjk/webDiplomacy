import ArrowColors from "../enums/ArrowColors";

export default function drawArrow(
  /**
   * color code passed from enum ArrowColors based on move type
   */
  arrowColor: ArrowColors,
  /**
   * source is the id of the element the arrow begins at
   */
  source: string,
  /**
   * svg parent element; must be able to target centers and defs
   */
  svgElement: d3.Selection<d3.BaseType, unknown, HTMLElement, any>,
  /**
   * target is the id of the element the arrow ends at
   */
  target: string,
) {
  const arrowIdNumber = Math.floor(Date.now() + Math.random());
  const sourceNodeData = svgElement.select(`#${source}`).node().getBBox();
  const targetNodeData = svgElement.select(`#${target}`).node().getBBox();

  svgElement
    .select("defs")
    .append("marker")
    .attr("id", `arrowhead-${arrowIdNumber}`)
    .attr("markerWidth", 8)
    .attr("markerHeight", 8)
    .attr("refX", 0)
    .attr("refY", 4)
    .attr("orient", "auto")
    .append("polygon")
    .attr("points", "0 0, 8 4, 0 8")
    .attr("fill", arrowColor);

  svgElement
    .select("#container")
    .append("line")
    .attr("x1", sourceNodeData.x + sourceNodeData.width / 2)
    .attr("y1", sourceNodeData.y + sourceNodeData.height / 2)
    .attr("x2", targetNodeData.x + targetNodeData.width / 2)
    .attr("y2", targetNodeData.y + targetNodeData.height / 2)
    .attr("marker-end", `url(#arrowhead-${arrowIdNumber})`)
    .attr("stroke", arrowColor)
    .attr("stroke-width", "2")
    .attr("id", `arrowline-${arrowIdNumber}`);
}
