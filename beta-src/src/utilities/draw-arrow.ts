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

  svgElement
    .append("line")
    .attr("x1", sourceNodeData.x)
    .attr("y1", sourceNodeData.y)
    .attr("x2", targetNodeData.x)
    .attr("y2", targetNodeData.y)
    .attr("marker-end", "url(#arrowhead)")
    .attr("stroke", actionTypeColors[actionType])
    .attr("strokeWidth", "1")
    .attr("id", "arrowline");

  //   svgElement
  //     .append("line")
  //     .attr("x1", coordinates.source.x)
  //     .attr("y1", coordinates.source.y - differenceOfY)
  //     .attr("x2", coordinates.target.x)
  //     .attr("y2", coordinates.target.y + differenceOfY)
  //     .attr("stroke", color)
  //     .attr("strokeWidth", "1")
  //     .attr("transform", `rotate(${degrees}degrees)`);
}
