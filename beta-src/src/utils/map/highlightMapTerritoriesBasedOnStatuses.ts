import { current } from "@reduxjs/toolkit";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Territory from "../../enums/map/variants/classic/Territory";
import { GameCommand } from "../../state/interfaces/GameCommands";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoriesMeta, {
  TerritoryMeta,
} from "../../state/interfaces/TerritoriesState";
import setCommand from "../state/setCommand";

/* eslint-disable no-param-reassign */
export default function highlightMapTerritoriesBasedOnStatuses(state): void {
  const {
    territoriesMeta,
    overview: { members },
  }: {
    territoriesMeta: TerritoriesMeta;
    overview: {
      members: GameOverviewResponse["members"];
    };
  } = current(state);
  if (Object.keys(territoriesMeta).length) {
    const membersMap = {};
    members.forEach((member) => {
      membersMap[member.countryID] = member.country;
    });
    Object.values(territoriesMeta).forEach((terr: TerritoryMeta) => {
      const { ownerCountryID, territory } = terr;
      const country = ownerCountryID ? membersMap[ownerCountryID] : undefined;
      if (territory) {
        const command: GameCommand = {
          command: "CAPTURED",
          data: { country: country ? countryMap[country] : "none" },
        };
        setCommand(state, command, "territoryCommands", Territory[territory]);
      }
    });
  }
}
