/**
 * This function shows which attributes need to be udpated/applied to set the highlighting properly.  They should be set on the WDTerritory element when it is time to wire this up with the global state.
 * @param countryName
 * @param territoryName
 * @returns
 */

export default function applyCountryHighlight(
  countryName: string,
  territoryName: string,
): void {
  const territoryElement: HTMLElement | null = document.getElementById(
    `${territoryName.toUpperCase()}-control-path`,
  );

  if (territoryElement == null) {
    alert("Territory not found!"); // eslint-disable-line
    return;
  }

  territoryElement.setAttribute("filter", `url(#${countryName.toLowerCase()})`);
  territoryElement.setAttribute("fill-opacity", "0.4");
  territoryElement.setAttribute("stroke-opacity", "0.5");
}
