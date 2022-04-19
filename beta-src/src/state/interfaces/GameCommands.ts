import { ITerritory } from "../../data/map/variants/classic/TerritoryMap";
import Country from "../../enums/Country";
import Territory from "../../enums/map/variants/classic/Territory";
import UIState from "../../enums/UIState";
import { IUnit } from "../../models/Interfaces";
import UnitSlotName from "../../types/map/UnitSlotName";
import UnitType from "../../types/UnitType";

export const ValidCommands = [
  "CAPTURED",
  "DRAW_ARROW",
  "HOLD",
  "INVALID_CLICK",
  "MOVE",
  "REMOVE_ARROW",
  "SET_UNIT",
] as const;

type GetArrayElementType<T extends readonly string[]> =
  T extends readonly (infer U)[] ? U : never;

interface DrawArrowCommand {
  from: Territory;
  to: Territory;
  type: "move";
}

interface ClickCommand {
  evt: unknown;
  territoryName: string;
}

interface BuildCommand {}

interface SetUnitCommand {
  componentType?: "Game" | "Icon";
  country?: Country;
  iconState?: UIState;
  mappedTerritory: ITerritory;
  unit?: IUnit;
  unitType?: UnitType;
  unitSlotName: UnitSlotName;
}

export type Command = GetArrayElementType<typeof ValidCommands>;

export interface GameCommand {
  command: Command;
  data?: {
    arrow?: DrawArrowCommand;
    click?: ClickCommand;
    country?: keyof Country | "none";
    orderID?: string;
    setUnit?: SetUnitCommand;
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
