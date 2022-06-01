import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import { IContext } from "../../models/Interfaces";

const getPhaseKey = function (
  data: GameOverviewResponse | GameStatusResponse | IContext | undefined,
): string {
  if (!data) {
    return "<BAD>";
  }
  return `${data.turn}.${data.phase}`;
};

export default getPhaseKey;
