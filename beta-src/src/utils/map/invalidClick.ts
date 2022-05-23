import * as d3 from "d3";
import Territory from "../../enums/map/variants/classic/Territory";

export default function invalidClick(
  evt: any, // FIXME: what type is this?
  name: Territory,
): void {
  console.log("INVALID CLICK");
  const territorySelection = d3.select(`#${name}-territory`);
  const territory: SVGSVGElement = territorySelection.node();
  if (territory) {
    const screenCTM = territory.getScreenCTM();
    if (screenCTM) {
      const pt = territory.createSVGPoint();
      pt.x = evt.clientX;
      pt.y = evt.clientY;
      const { x, y } = pt.matrixTransform(screenCTM.inverse());
      territorySelection
        .append("circle")
        .attr("cx", x)
        .attr("cy", y)
        .attr("r", 6.5)
        .attr("fill", "red")
        .attr("fill-opacity", 0.4)
        .attr("class", "invalid-click");
      territorySelection
        .append("circle")
        .attr("cx", x)
        .attr("cy", y)
        .attr("r", 14)
        .attr("fill", "red")
        .attr("fill-opacity", 0.2)
        .attr("class", "invalid-click");
      setTimeout(() => {
        d3.selectAll(".invalid-click").remove();
      }, 100);
    }
  }
}
