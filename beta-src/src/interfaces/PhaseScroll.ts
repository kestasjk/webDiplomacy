import ScrollButtonState from "../enums/UIState";

interface gameState {
  seasons: string[];
  currentSeason: string;
}

export interface gameStateProps {
  onChangeSeason: (season: string) => void;
  gameState: gameState;
  disabled?: boolean;
}

export interface scrollButtonProps {
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  direction: ScrollButtonState;
  disabled?: boolean;
}
