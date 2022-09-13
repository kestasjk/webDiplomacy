/* eslint-disable no-bitwise */
import React, { FC, ReactElement } from "react";
import { useKeyPressEvent } from "react-use";
import {
  gameApiSliceActions,
  gameLegalOrders,
  gameMaps,
  gameOverview,
  gameOrder,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import { Unit } from "../../../utils/map/getUnits";

import WDFlyoutButton from "./WDFlyoutButton";
import TerritoryMap from "../../../data/map/variants/classic/TerritoryMap";

interface WDFlyoutContainerProps {
  units: Unit[];
}

const WDFlyoutContainer: FC<WDFlyoutContainerProps> = function ({
  units,
}): ReactElement {
  const dispatch = useAppDispatch();
  const overview = useAppSelector(gameOverview);
  const order = useAppSelector(gameOrder);
  const maps = useAppSelector(gameMaps);
  const legalOrders = useAppSelector(gameLegalOrders);

  const unit = units.find((u) => u.unit.id === order.unitID);
  const mTerr = TerritoryMap[maps.unitToTerritory[order.unitID]];

  const clickHandler = (
    orderType,
    viaConvoy: string | undefined = undefined,
  ) => {
    if (!order.inProgress) return;
    if (overview.phase !== "Diplomacy") return;
    console.log(`Dispatched ${orderType}`);
    dispatch(
      gameApiSliceActions.updateOrder({
        type: orderType,
        viaConvoy,
      }),
    );
  };

  const canConvoy: boolean =
    unit?.unit?.type === "Fleet" &&
    mTerr.provinceMapData.type === "Sea" &&
    legalOrders.hasAnyLegalConvoysByUnitID[order.unitID];

  const canVia: boolean =
    unit?.unit?.type === "Army" &&
    legalOrders.legalViasByUnitID[order.unitID]?.length > 0;

  useKeyPressEvent("h", () => clickHandler("Hold"));
  useKeyPressEvent("d", () => clickHandler("Hold"));
  useKeyPressEvent("m", () => clickHandler("Move"));
  useKeyPressEvent("a", () => clickHandler("Move"));
  useKeyPressEvent("v", () => canVia && clickHandler("Move", "Yes"));
  useKeyPressEvent("s", () => clickHandler("Support"));
  useKeyPressEvent("c", () => canConvoy && clickHandler("Convoy"));

  if (!order.inProgress || order.type || !order.unitID) {
    return <div />;
  }

  const { province, unitSlotName } = mTerr;

  return (
    <>
      <WDFlyoutButton
        province={province}
        unitSlotName={unitSlotName}
        position="left"
        text="Hold"
        clickHandler={() => clickHandler("Hold")}
      />
      <WDFlyoutButton
        province={province}
        unitSlotName={unitSlotName}
        position="right"
        text="Move"
        clickHandler={() => clickHandler("Move")}
      />
      <WDFlyoutButton
        province={province}
        unitSlotName={unitSlotName}
        position="top"
        text="Support"
        clickHandler={() => clickHandler("Support")}
      />
      {(canConvoy && (
        <WDFlyoutButton
          province={province}
          unitSlotName={unitSlotName}
          position="bottom"
          text="Convoy"
          clickHandler={() => clickHandler("Convoy")}
        />
      )) || <g />}
      {canVia && (
        <WDFlyoutButton
          province={province}
          unitSlotName={unitSlotName}
          position="bottom"
          text="Via Convoy"
          clickHandler={() => clickHandler("Move", "Yes")}
        />
      )}
    </>
  );
};

export default WDFlyoutContainer;
