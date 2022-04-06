interface OrderMetaUpdate {
  type: string;
  toTerrID: string | null;
}

interface OrderMeta {
  saved: boolean;
  update?: OrderMetaUpdate;
}

interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
