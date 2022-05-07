import { current } from "@reduxjs/toolkit";
import { GameCommandType } from "../../../../state/interfaces/GameCommands";

/* eslint-disable no-param-reassign */
export default function deleteCommand(
  state,
  {
    payload: { type, command, id },
  }: {
    payload: {
      command: string;
      id: string;
      type: GameCommandType;
    };
  },
) {
  const { commands } = current(state);
  const commandsType = commands[type];
  const commandSet = new Map(commandsType[id]);
  const deleteKey = command;
  if (commandSet && commandSet.has(deleteKey)) {
    const newCommandSet = new Map(commandSet);
    newCommandSet.delete(deleteKey);
    state.commands[type][id] = newCommandSet;
  }
}
