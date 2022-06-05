export default interface SavedOrder {
  [key: string]: {
    changed: string;
    notice: string | null;
    status: string;
  };
}
