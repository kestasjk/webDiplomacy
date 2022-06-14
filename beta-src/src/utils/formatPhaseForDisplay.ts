import { PhaseSeasonYear } from "../interfaces/PhaseSeasonYear";

export function formatPhaseForDisplay(phase: string): string {
  if (phase === "Diplomacy") {
    return "Movement";
  }
  return phase;
}

export function formatPSYForDisplay(psy: PhaseSeasonYear): string {
  return `${psy.season} ${psy.year} ${formatPhaseForDisplay(psy.phase)}`;
}

export function formatPSYForDisplayShort(psy: PhaseSeasonYear): string {
  return `${psy.season.charAt(0)}${psy.year}${formatPhaseForDisplay(
    psy.phase,
  ).charAt(0)}`;
}
