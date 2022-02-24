export default function drawArrow(actionType, source, svgElement, target) {
  const actionTypeColors = {
    moveOrder: "#FFFFFF",
    move: "#000000",
    moveConvoy: "#2042B8",
    moveFailed: "#BB0000",
    moveSupport: "#F8F83D",
    holdSupport: "#3FC621",
    retreat: "#BD2894",
  };
  const sourceNodeData = svgElement
    .select(`#${source}`)
    .node()
    .getBoundingClientRect();
  const targetNodeData = svgElement
    .select(`#${target}`)
    .node()
    .getBoundingClientRect();

  svgElement.select("#arrowline").remove();
  svgElement.select("#arrowhead").remove();

  svgElement
    .select("defs")
    .append("marker")
    .attr("id", "arrowhead")
    .attr("markerWidth", 8)
    .attr("markerHeight", 8)
    .attr("refX", 0)
    .attr("refY", 4)
    .attr("orient", "auto")
    .append("polygon")
    .attr("points", "0 0, 8 4, 0 8")
    .attr("fill", actionTypeColors[actionType]);

  svgElement
    .append("line")
    .attr("x1", sourceNodeData.x + sourceNodeData.width / 2)
    .attr("y1", sourceNodeData.y + sourceNodeData.height / 2)
    .attr("x2", targetNodeData.x + targetNodeData.width / 2)
    .attr("y2", targetNodeData.y + targetNodeData.height / 2)
    .attr("marker-end", "url(#arrowhead)")
    .attr("stroke", actionTypeColors[actionType])
    .attr("stroke-width", "2")
    .attr("id", "arrowline");
}
