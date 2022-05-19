export default function memberActivityFrequencyMultiplier(
  active: number,
  membersPlayingAmount: number,
  currentFrequency: number,
): number {
  let newFrequency = currentFrequency;
  if (active) {
    let percentage = active / membersPlayingAmount;
    if (percentage === 1) {
      percentage = 0.9;
    }
    newFrequency -= percentage * currentFrequency;
  }
  return newFrequency;
}
