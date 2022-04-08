import Country from "../../enums/Country";

type Command = "HOLD" | "CAPTURED";

export interface GameCommand {
  command: Command;
  data?: {
    country?: keyof Country;
  };
}

export interface GameCommandContainer {
  [key: string]: Map<string, GameCommand>;
}

export type GameContainerType = "territoryCommands" | "unitCommands";

type GameCommands = {
  [key in GameContainerType]: GameCommandContainer;
};

export default GameCommands;
