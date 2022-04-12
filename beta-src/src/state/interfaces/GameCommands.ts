import Country from "../../enums/Country";
import Territory from "../../enums/map/variants/classic/Territory";

type Command =
  | "HOLD"
  | "CAPTURED"
  | "DRAW_ARROW"
  | "REMOVE_ARROW"
  | "INVALID_CLICK";

interface DrawArrowCommand {
  from: Territory;
  to: Territory;
  type: "move";
}

interface ClickCommand {
  evt: unknown;
  territoryName: string;
}

export interface GameCommand {
  command: Command;
  data?: {
    click?: ClickCommand;
    orderID?: string;
    country?: keyof Country | "none";
    arrow?: DrawArrowCommand;
  };
}

export interface GameCommandContainer {
  [key: string]: Map<string, GameCommand>;
}

export type GameCommandType =
  | "territoryCommands"
  | "unitCommands"
  | "mapCommands";

type GameCommands = {
  [key in GameCommandType]: GameCommandContainer;
};

export default GameCommands;
