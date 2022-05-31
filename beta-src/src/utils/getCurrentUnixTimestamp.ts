export default function getCurrentUnixTimestamp(): number {
  // eslint-disable-next-line no-bitwise
  return (Date.now() / 1000) | 0;
}
