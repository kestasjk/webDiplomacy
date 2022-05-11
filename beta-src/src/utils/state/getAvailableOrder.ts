export default function getAvailableOrder(currentOrders, ordersMeta) {
  let availableOrder;
  for (let i = 0; i < currentOrders?.length; i += 1) {
    const { id } = currentOrders[i];
    const orderMeta = ordersMeta[id];
    if (!orderMeta.update || !orderMeta.update?.toTerrID) {
      availableOrder = id;
      break;
    }
  }
  return availableOrder;
}
