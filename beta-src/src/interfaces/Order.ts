interface Order {
  error: null | string | undefined;
  status: string;
  id: number;
  type: string;
  unitID: number;
  toTerrID: number;
  fromTerrID: null | number | undefined;
  viaConvoy: string;
  fixed: string[];
}

export default Order;
