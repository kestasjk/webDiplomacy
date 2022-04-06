interface OrderMeta {
  saved: boolean;
}

interface OrdersMeta {
  [key: string]: OrderMeta;
}

export default OrdersMeta;
