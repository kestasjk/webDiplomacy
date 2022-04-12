import { Command, GameCommand } from "../state/interfaces/GameCommands";

type CommandActions = { [key in Command]?: (key: string) => void };

export default function processNextCommand(
  commands: Map<string, GameCommand>,
  commandActions: CommandActions,
): void {
  if (commands?.size > 0) {
    const firstCommand = commands.entries().next().value;
    if (firstCommand) {
      const [, value] = firstCommand;
      const fn = commandActions[value.command];
      if (fn) {
        fn(firstCommand);
      }
    }
  }
}
