import Country from "../../enums/Country";

type GetArrayElementType<T extends readonly string[]> =
  T extends readonly (infer U)[] ? U : never;

export const ValidCommands = ["HOLD", "CAPTURED"] as const;

export type Command = GetArrayElementType<typeof ValidCommands>;

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
