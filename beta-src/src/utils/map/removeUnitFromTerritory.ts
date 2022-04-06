import Territory from "../../enums/map/variants/classic/Territory";

export default function removeUnitFromTerritory(territory: Territory): void {
  const name = `data-unit-slot=${Territory[territory]}`;
  const unitSlots = document.querySelectorAll(`[${name}]`);
  const unitSlotCount = unitSlots.length;
  for (let i = 0; i < unitSlotCount; i += 1) {
    const slot = unitSlots[i];
    while (slot.firstChild) {
      if (slot.lastChild) {
        slot.removeChild(slot.lastChild);
      }
    }
  }
}
