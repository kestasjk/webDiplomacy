import { v4 as uuidv4 } from "uuid";
import { current } from "@reduxjs/toolkit";
import {
  GameCommand,
  GameCommandType,
} from "../../state/interfaces/GameCommands";

/* eslint-disable no-param-reassign */
export default function setCommand(
  state,
  command: GameCommand,
  container: GameCommandType,
  id: string,
): void {
  console.log(`setCommand ${container} ${command.command}`);
  const { commands } = current(state);
  const commandsContainer = commands[container];
  const newCommand = new Map(commandsContainer[id]) || new Map();
  newCommand.set(uuidv4(), command);
  state.commands[container][id] = newCommand;
}
