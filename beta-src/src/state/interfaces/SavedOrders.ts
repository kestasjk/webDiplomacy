import TerritoryClass from "../../models/TerritoryClass";

interface OrderMetaUpdate {
  type: string;
  toTerrID: string | null;
  viaConvoy?: string | null;
}

interface OrderMeta {
  saved: boolean;
  update?: OrderMetaUpdate;
  allowedBorderCrossings?: TerritoryClass[];
}

export interface EditOrderMeta {
  [key: string]: {
    saved?: boolean;
    update?: OrderMetaUpdate;
    allowedBorderCrossings?: TerritoryClass[];
  };
}

interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
