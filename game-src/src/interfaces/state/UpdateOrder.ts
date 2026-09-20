export default interface UpdateOrder {
  fromTerrID: string | null;
  id: string;
  toTerrID: string | null;
  type: string;
  unitID: string;
  viaConvoy: string | null;
}
