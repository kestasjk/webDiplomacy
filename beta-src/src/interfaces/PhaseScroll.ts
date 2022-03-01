import ScrollButtonState from "../enums/ScrollButton";

export interface scrollButtonProps {
  direction: ScrollButtonState;
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
}
