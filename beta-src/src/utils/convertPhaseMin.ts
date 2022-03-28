export default function convertPhaseMin(phaseTime: number) {
  const endTime = Math.floor(Date.now() / 1000) + phaseTime * 60;
  return endTime;
}
