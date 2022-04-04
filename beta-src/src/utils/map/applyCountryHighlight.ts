import Country from "../../enums/Country";
import Territory from "../../enums/Territory";

export default function applyCountryHighlight(
  country: Country,
  territory: Territory,
): void {
  const territoryElement: HTMLElement | null = document.getElementById(
    `${territory}-control-path`,
  );

  if (territoryElement !== null) {
    territoryElement.setAttribute("filter", `url(#${country})`);
    territoryElement.setAttribute("fill-opacity", "0.4");
    territoryElement.setAttribute("stroke-opacity", "0.5");
  }
}
