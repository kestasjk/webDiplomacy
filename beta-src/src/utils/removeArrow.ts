export default function removeArrow(
  /**
   * id is the id of the arrowline to be removed
   */
  id: string,
  /**
   * The resulting object after selecting the full map using D3 (NOT A SVG ELEMENT); must be able to target centers and defs
   */
  d3MapSelector: any,
) {
  const arrowIdTag = id.split("__")[1];
  d3MapSelector.select(`#${id}`).remove();
  d3MapSelector.select(`#arrowhead__${arrowIdTag}`).remove();
}
