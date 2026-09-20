type Enumerate<
  N extends number,
  Acc extends number[] = [],
> = Acc["length"] extends N
  ? Acc[number]
  : Enumerate<N, [...Acc, Acc["length"]]>;

type IntegerRange<F extends number, T extends number> = Exclude<
  Enumerate<T>,
  Enumerate<F>
>;

/**
 * IntegerRange type excludes the second number.
 * So if specifying a range of 0-4 you need to set the range to <0,5>
 */
export default IntegerRange;
