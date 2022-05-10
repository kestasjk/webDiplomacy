import {
  GameCommand,
  GameCommandType,
} from "../../../../state/interfaces/GameCommands";
import setCommand from "../../setCommand";

export default function dispatchCommand(
  state,
  action: {
    type: string;
    payload: {
      command: GameCommand;
      container: GameCommandType;
      identifier: string;
    };
  },
) {
  const { command, container, identifier } = action.payload;
  setCommand(state, command, container, identifier);
}
