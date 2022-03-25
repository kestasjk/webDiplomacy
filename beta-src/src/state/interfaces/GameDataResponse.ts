import ContextVar from "../../interfaces/ContextVar";
import Order from "../../interfaces/Order";

interface GameDataResponse {
  msg: string;
  referenceCode: string;
  success: boolean;
  data: {
    contextVars: ContextVar;
    currentOrders: Order[];
  };
}

export default GameDataResponse;
