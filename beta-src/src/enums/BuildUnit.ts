/* eslint-disable no-bitwise */

enum BuildUnit {
  Army = 1 << 1,
  Fleet = 1 << 2,
  All = Army | Fleet,
}

export default BuildUnit;
