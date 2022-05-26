import { IOrderData } from "../../models/Interfaces";
import TerritoryClass from "../../models/TerritoryClass";

export interface OrderMetaUpdate {
  convoyPath?: string[];
  type: string;
  toTerrID: string | null;
  fromTerrID?: string | null;
  viaConvoy?: string | null;
}

export interface SupportMoveChoice {
  supportMoveFrom: TerritoryClass[];
  supportMoveTo: TerritoryClass;
}

interface SharedMeta {
  allowedBorderCrossings?: TerritoryClass[];
  convoyToChoices?: string[]; //TerritoryClass[];
  originalOrder?: IOrderData;
  supportHoldChoices?: TerritoryClass[];
  supportMoveChoices?: SupportMoveChoice[];
  update?: OrderMetaUpdate;
}

export interface OrderMeta extends SharedMeta {
  saved: boolean;
}

export interface EditOrder extends SharedMeta {
  saved?: boolean;
}

export interface EditOrderMeta {
  [key: string]: EditOrder;
}

// The key on this interface is Order ID.
interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
