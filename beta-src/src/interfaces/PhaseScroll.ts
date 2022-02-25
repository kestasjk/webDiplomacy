import { ScrollButtonState } from "../enums/UIState";

export interface scrollButtonProps {
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  direction: ScrollButtonState;
  disabled?: boolean;
}
