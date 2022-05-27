import * as d3 from "d3";
import Province from "../../enums/map/variants/classic/Province";

export default function invalidClick(
  evt: React.MouseEvent<SVGGElement, MouseEvent>,
  name: Province,
): void {
  console.log("INVALID CLICK");
  const provinceSelection = d3.select(`#${name}-province`);
  const province: SVGSVGElement = provinceSelection.node();
  if (province) {
    const screenCTM = province.getScreenCTM();
    if (screenCTM) {
      const pt = province.createSVGPoint();
      pt.x = evt.clientX;
      pt.y = evt.clientY;
      const { x, y } = pt.matrixTransform(screenCTM.inverse());
      provinceSelection
        .append("circle")
        .attr("cx", x)
        .attr("cy", y)
        .attr("r", 6.5)
        .attr("fill", "red")
        .attr("fill-opacity", 0.4)
        .attr("class", "invalid-click");
      provinceSelection
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
