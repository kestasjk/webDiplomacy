import { IOrderData } from "../../models/Interfaces";
import TerritoryClass from "../../models/TerritoryClass";

interface OrderMetaUpdate {
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
  convoyToChoices?: TerritoryClass[];
  originalOrder?: IOrderData;
  supportHoldChoices?: TerritoryClass[];
  supportMoveChoices?: SupportMoveChoice[];
  update?: OrderMetaUpdate;
}

interface OrderMeta extends SharedMeta {
  saved: boolean;
}

export interface EditOrder extends SharedMeta {
  saved?: boolean;
}

export interface EditOrderMeta {
  [key: string]: EditOrder;
}

interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
