import { current } from "@reduxjs/toolkit";

export default function writeNotifications(state): void {
  const {
    ordersMeta,
    order,
    overview: { phase },
  } = current(state);
}
