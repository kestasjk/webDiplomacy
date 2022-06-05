import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import { IContext } from "../../models/Interfaces";

const getPhaseKey = function (
  data: GameOverviewResponse | GameStatusResponse | IContext | undefined,
  valueIfUndefined: string,
): string {
  if (!data) {
    return valueIfUndefined;
  }
  return `${data.turn}.${data.phase}`;
};

export default getPhaseKey;
