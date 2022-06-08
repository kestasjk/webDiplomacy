import * as React from "react";
import WDProvince from "../../../components/WDProvince";
import WDProvinceOverlay from "../../../components/WDProvinceOverlay";
import { Unit } from "../../../../../utils/map/getUnits";
import provincesMapData from "../../../../../data/map/ProvincesMapData";
import Province from "../../../../../enums/map/variants/classic/Province";
import {
  gameData,
  gameLegalOrders,
  gameMaps,
  gameOrder,
  gameOverview,
} from "../../../../../state/game/game-api-slice";
import { useAppSelector } from "../../../../../state/hooks";
import { IProvinceStatus } from "../../../../../models/Interfaces";
import OrderState from "../../../../../state/interfaces/OrderState";
import { LegalOrders } from "../../../../../utils/state/gameApiSlice/extraReducers/fetchGameData/precomputeLegalOrders";
import TerritoryMap from "../../../../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../../../../data/map/variants/classic/CountryMap";

interface WDBoardMapProps {
  units: Unit[];
  centersByProvince: { [key: string]: { ownerCountryID: string } };
  phase: string;
  isLatestPhase: boolean;
}

const WDBoardMap: React.FC<WDBoardMapProps> = function ({
  units,
  centersByProvince,
  phase,
  isLatestPhase,
}): React.ReactElement {
  const gameDataResponse = useAppSelector(gameData);
  const maps = useAppSelector(gameMaps);
  const provinceStatusByProvID: { [key: string]: IProvinceStatus } = {};
  gameDataResponse.data.territoryStatuses.forEach((provinceStatus) => {
    provinceStatusByProvID[maps.terrIDToProvince[provinceStatus.id]] =
      provinceStatus;
  });

  const curOrder: OrderState = useAppSelector(gameOrder);
  const legalOrders: LegalOrders = useAppSelector(gameLegalOrders);

  const overview = useAppSelector(gameOverview);
  const { members, user } = overview;

  let provincesToHighlight: Province[] = [];
  let provincesToChoose: Province[] = [];
  if (isLatestPhase && user) {
    if (phase === "Diplomacy") {
      if (!curOrder.inProgress) {
        provincesToHighlight = [];
        provincesToChoose = [];
      } else if (curOrder.type === "Move") {
        if (curOrder.viaConvoy) {
          provincesToHighlight = [
            maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
          ];
          provincesToChoose = legalOrders.legalViasByUnitID[
            curOrder.unitID
          ].map((via) => TerritoryMap[via.dest].province);
        } else {
          provincesToHighlight = [
            maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
          ];
          provincesToChoose = legalOrders.legalMoveDestsByUnitID[
            curOrder.unitID
          ].map((territory) => TerritoryMap[territory].province);
        }
      } else if (curOrder.type === "Support") {
        if (curOrder.fromTerrID) {
          provincesToHighlight = [
            maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
            maps.terrIDToProvince[curOrder.fromTerrID],
          ];
          provincesToChoose = legalOrders.legalSupportsByUnitID[
            curOrder.unitID
          ][maps.terrIDToProvince[curOrder.fromTerrID]].map(
            (support) => support.dest,
          );
        } else {
          provincesToHighlight = [
            maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
          ];
          provincesToChoose = Object.keys(
            legalOrders.legalSupportsByUnitID[curOrder.unitID],
          ) as Province[];
        }
      } else if (curOrder.type === "Convoy") {
        if (curOrder.fromTerrID) {
          provincesToHighlight = [
            maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
            maps.terrIDToProvince[curOrder.fromTerrID],
          ];
          provincesToChoose = Object.keys(
            legalOrders.legalConvoysByUnitID[curOrder.unitID][
              maps.terrIDToProvince[curOrder.fromTerrID]
            ],
          ) as Province[];
        } else {
          provincesToHighlight = [
            maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
          ];
          provincesToChoose = Object.keys(
            legalOrders.legalConvoysByUnitID[curOrder.unitID],
          ) as Province[];
        }
      }
    } else if (phase === "Retreats") {
      if (!curOrder.inProgress) {
        provincesToHighlight = [];
        provincesToChoose = Object.keys(
          legalOrders.legalRetreatDestsByUnitID,
        ).map((unitID) => maps.terrIDToProvince[maps.unitToTerrID[unitID]]);
      } else if (curOrder.type === "Retreat") {
        provincesToHighlight = [
          maps.terrIDToProvince[maps.unitToTerrID[curOrder.unitID]],
        ];
        provincesToChoose = legalOrders.legalRetreatDestsByUnitID[
          curOrder.unitID
        ].map((territory) => TerritoryMap[territory].province);
        provincesToChoose.push(provincesToHighlight[0]);
      }
    } else if (phase === "Builds") {
      if (user.member.supplyCenterNo < user.member.unitNo) {
        provincesToChoose = units
          .filter((unit) => unit.country === user.member.country)
          .map((unit) => unit.mappedTerritory.province);
      } else if (user.member.supplyCenterNo > user.member.unitNo) {
        provincesToChoose = legalOrders.possibleBuildDests.map(
          (territory) => TerritoryMap[territory].province,
        );
      }
    }
  }
  const provincesToHighlightSet = new Set(provincesToHighlight);
  const provincesToChooseSet = new Set(provincesToChoose);
  // console.log({ provincesToChooseSet });

  const unplayableProvinces = Object.values(provincesMapData)
    .filter((data) => !data.playable)
    .map((data) => {
      return (
        <WDProvince
          provinceMapData={data}
          ownerCountryID={centersByProvince[data.province]?.ownerCountryID}
          playerCountryID={user?.member.countryID}
          highlightSelection={false}
          key={`${data.province}-province`}
        />
      );
    });
  // Hack - Rome and Naples need to be sorted to the end or else their label will get cut
  // off by neighboring territories drawn on top of it.
  const playableProvincesData = Object.values(provincesMapData).filter(
    (data) =>
      data.playable &&
      data.province !== Province.NAPLES &&
      data.province !== Province.ROME,
  );
  playableProvincesData.push(provincesMapData[Province.NAPLES]);
  playableProvincesData.push(provincesMapData[Province.ROME]);

  const playableProvinces = playableProvincesData.map((data) => {
    const highlightSelection = provincesToHighlightSet.has(data.province);
    return (
      <WDProvince
        provinceMapData={data}
        ownerCountryID={centersByProvince[data.province]?.ownerCountryID}
        playerCountryID={user?.member.countryID}
        highlightSelection={highlightSelection}
        key={`${data.province}-province`}
      />
    );
  });

  const playableProvinceOverlays = playableProvincesData.map((data) => {
    const highlightChoice = provincesToChooseSet.has(data.province);
    return (
      <WDProvinceOverlay
        provinceMapData={data}
        units={units}
        highlightChoice={highlightChoice}
        key={`${data.province}-province-overlay`}
      />
    );
  });

  return (
    <g id="wD-boardmap-v10.3.4 1">
      <g id="unplayable">{unplayableProvinces}</g>
      <g id="playableProvinces">{playableProvinces}</g>
      <g id="playableProvinceOverlays">{playableProvinceOverlays}</g>
    </g>
  );
};

export default WDBoardMap;
