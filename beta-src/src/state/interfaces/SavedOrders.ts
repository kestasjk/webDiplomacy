import { IOrderData } from "../../models/Interfaces";
import TerritoryClass from "../../models/TerritoryClass";

interface OrderMetaUpdate {
  type: string;
  toTerrID: string | null;
  fromTerrID?: string | null;
  viaConvoy?: string | null;
}

export interface SupportMoveChoice {
  supportMoveFrom: TerritoryClass[];
  supportMoveTo: TerritoryClass;
}

interface OrderMeta {
  allowedBorderCrossings?: TerritoryClass[];
  saved: boolean;
  supportMoveChoices?: SupportMoveChoice[];
  update?: OrderMetaUpdate;
  originalOrder: IOrderData;
}

export interface EditOrderMeta {
  [key: string]: {
    saved?: boolean;
    update?: OrderMetaUpdate;
    allowedBorderCrossings?: TerritoryClass[];
    originalOrder?: IOrderData;
    supportMoveChoices?: SupportMoveChoice[];
  };
}

interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
