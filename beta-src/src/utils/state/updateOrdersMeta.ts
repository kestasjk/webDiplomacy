import { createAsyncThunk } from "@reduxjs/toolkit";
import { EditOrderMeta } from "../../state/interfaces/SavedOrders";
import drawOrders from "../map/drawOrders";
import UpdateOrder from "../../interfaces/state/UpdateOrder";
import OrderSubmission from "../../interfaces/state/OrderSubmission";
import { submitOrders } from "../api";
import SavedOrdersConfirmation from "../../interfaces/state/SavedOrdersConfirmation";

/* eslint-disable no-param-reassign */
export default function updateOrdersMeta(state, updates: EditOrderMeta): void {
  const saveOrders = createAsyncThunk(
    "game/submitOrders",
    async (data: OrderSubmission) => {
      const formData = new FormData();
      formData.set("orderUpdates", JSON.stringify(data.orderUpdates));
      formData.set("context", data.context);
      formData.set("contextKey", data.contextKey);
      const response = await submitOrders(formData, data.queryParams);
      const confirmation: string = response.headers["x-json"] || "";
      const parsed: SavedOrdersConfirmation = JSON.parse(
        confirmation.substring(1, confirmation.length - 1),
      );
      console.log(parsed);
      return parsed;
    },
  );
  Object.entries(updates).forEach(([orderID, update]) => {
    state.ordersMeta[orderID] = {
      ...state.ordersMeta[orderID],
      ...update,
    };
  });

  if (
    Object.values(updates).length === 1 &&
    ((Object.values(updates)[0].update?.type === "Disband" &&
      !Object.values(updates)[0].allowedBorderCrossings?.length) ||
      (Object.values(updates)[0].update?.type === "Retreat" &&
        !Object.values(updates)[0].allowedBorderCrossings?.length))
  ) {
    const [{ fromTerrID, id, toTerrID, type: moveType, unitID, viaConvoy }] =
      state.data.data.currentOrders;

    const orderUpdates: UpdateOrder[] = [];
    const updateReference = state.ordersMeta[id].update;
    let orderUpdate: UpdateOrder = {
      fromTerrID,
      id,
      toTerrID,
      type: moveType,
      unitID,
      viaConvoy,
    };
    if (updateReference) {
      orderUpdate = {
        ...orderUpdate,
        ...updateReference,
      };
    }
    console.log({ orderUpdate });
    orderUpdates.push(orderUpdate);
    const orderSubmission = {
      orderUpdates,
      context: JSON.stringify(state.data.data.contextVars.context),
      contextKey: state.data.data.contextVars.contextKey,
      queryParams: { notready: "on" },
    };

    console.log("s", saveOrders(orderSubmission));

    saveOrders(orderSubmission);
  }

  drawOrders(state);
}
