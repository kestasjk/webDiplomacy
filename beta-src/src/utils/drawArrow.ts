export default function drawArrow(
  /**
   * color code passed from enum ArrowColors based on move type
   */
  arrowColor: string,
  /**
   * source is the id of the element the arrow begins at
   */
  source: string,
  /**
   * The resulting object after selecting the full map using D3 (NOT A SVG ELEMENT); must be able to target centers and defs
   */
  d3MapSelector: any,
  /**
   * target is the id of the element the arrow ends at
   */
  target: string,
) {
  const arrowIdNumber = `${source}-${target}`;

  const sourceNodeData = (
    d3MapSelector.select(`#${source}`).node() as SVGSVGElement
  ).getBBox();
  const targetNodeData = (
    d3MapSelector.select(`#${target}`).node() as SVGSVGElement
  ).getBBox();

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
