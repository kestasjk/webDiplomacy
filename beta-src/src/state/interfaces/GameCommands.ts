import Country from "../../enums/Country";
import Territory from "../../enums/map/variants/classic/Territory";

interface DrawArrowCommand {
  from: Territory;
  to: Territory;
  type: "move";
}

interface ClickCommand {
  evt: unknown;
  territoryName: string;
}
type GetArrayElementType<T extends readonly string[]> =
  T extends readonly (infer U)[] ? U : never;

export const ValidCommands = [
  "CAPTURED",
  "DRAW_ARROW",
  "HOLD",
  "INVALID_CLICK",
  "REMOVE_ARROW",
] as const;

export type Command = GetArrayElementType<typeof ValidCommands>;

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
