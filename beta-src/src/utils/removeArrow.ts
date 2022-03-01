export default function removeArrow(
  /**
   * id is the id of the arrowline. This ends with a -somenumber
   */
  id: string,
  /**
   * svgElement is the parent svg element. Must be able to target defs as well as svg#container
   */
  svgElement: any,
) {
  const arrowIdTag = id.split("-")[1];
  svgElement.select(`#${id}`).remove();
  svgElement.select(`#arrowhead${arrowIdTag}`).remove();
}
