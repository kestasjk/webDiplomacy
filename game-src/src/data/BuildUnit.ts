import BuildUnit from "../enums/BuildUnit";

export const BuildUnitTypeMap = {
  Army: BuildUnit.Army,
  Fleet: BuildUnit.Fleet,
  [BuildUnit.Army]: "Army",
  [BuildUnit.Fleet]: "Fleet",
} as const;

const BuildUnitMap = {
  [BuildUnit.Army]: "Build Army",
  [BuildUnit.Fleet]: "Build Fleet",
  "Build Army": BuildUnit.Army,
  "Build Fleet": BuildUnit.Fleet,
} as const;

export default BuildUnitMap;
