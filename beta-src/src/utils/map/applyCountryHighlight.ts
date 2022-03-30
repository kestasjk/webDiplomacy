export default function applyCountryHighlight(
  countryName: string,
  territoryName: string,
) {
  const territoryElement = document.getElementById(
    `${territoryName.toUpperCase()}-control-path`,
  );

  // Needs proper error handling
  if (!territoryElement) {
    return alert("Territory not found!"); // eslint-disable-line
  }

  territoryElement.setAttribute("fill", `url(#radialFill)`);
  territoryElement.setAttribute("filter", `url(#${countryName.toLowerCase()})`);
  territoryElement.setAttribute("fill-opacity", "0.5");
  territoryElement.setAttribute("stroke-opacity", "0.5");

  return territoryElement;
}
