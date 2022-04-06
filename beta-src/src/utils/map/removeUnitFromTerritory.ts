import Territory from "../../enums/map/variants/classic/Territory";

export default function removeUnitFromTerritory(
  territory: Territory,
  //   unitSlotName,
): void {
  //   const unitSlotId = `${Territory[territory]}-${unitSlotName}-unit-slot`;
  const name = `data-unit-slot=${Territory[territory]}`;
  console.log({
    name,
  });
  const unitSlots = document.querySelectorAll(`[${name}]`);
  console.log({
    unitSlots,
  });
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
