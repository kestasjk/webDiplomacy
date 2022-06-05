export default function formatPhaseForDisplay(phase: string): string {
  if (phase === "Diplomacy") {
    return "Movement";
  }
  return phase;
}
