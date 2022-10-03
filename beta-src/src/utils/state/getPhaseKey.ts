import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import { IContext, IPhaseDataHistorical } from "../../models/Interfaces";
import { GameData } from "../../state/interfaces/GameDataResponse";

const getPhaseKey = function (
  data:
    | GameOverviewResponse
    | GameStatusResponse
    | GameData
    | IPhaseDataHistorical
    | undefined,
  valueIfUndefined: string,
): string {
  if (!data) {
    return valueIfUndefined;
  }
  return `${data.turn}.${data.phase}`;
};

export default getPhaseKey;
