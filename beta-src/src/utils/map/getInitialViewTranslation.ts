import { Viewport } from "../../interfaces";
import BBox from "../../types/BBox";
import Translation from "../../types/Translation";

export default function getInitialViewTranslation(
  containedRect: BBox,
  gameBoardRect: BBox,
  scale: number,
  viewport: Viewport,
): Translation {
  let newScale = scale;
  const containedHeight = containedRect.height * scale;

  if (containedHeight < viewport.height) {
    newScale = scale + (1 - containedHeight / viewport.height);
  }

  const translatedGameBoardAreaHeight = gameBoardRect.height * newScale;
  const translatedGameBoardAreaY = gameBoardRect.y * newScale;

  const translatedGameBoardAreaWidth = gameBoardRect.width * newScale;
  const translatedGameBoardAreaX = gameBoardRect.x * newScale;

  const nonPlayableHorizontalArea = Math.abs(
    viewport.width - translatedGameBoardAreaWidth,
  );
  const horizontalPadding = Math.abs(nonPlayableHorizontalArea / 2);

  const nonPlayableVerticalArea = Math.abs(
    viewport.height - translatedGameBoardAreaHeight,
  );
  const verticalPadding = Math.abs(nonPlayableVerticalArea / 2);
  const verticalBottomPadding =
    (containedRect.y +
      containedRect.height -
      (gameBoardRect.y + gameBoardRect.height)) *
    newScale;

  let x: number;
  let y: number;

  if (viewport.height >= translatedGameBoardAreaHeight) {
    y = -translatedGameBoardAreaY + verticalPadding;
    if (verticalPadding > verticalBottomPadding) {
      y += verticalPadding - verticalBottomPadding;
    }
  } else {
    y = -translatedGameBoardAreaY - verticalPadding;
  }

  if (viewport.width >= translatedGameBoardAreaWidth) {
    x = -translatedGameBoardAreaX + horizontalPadding;
  } else {
    x = -translatedGameBoardAreaX - horizontalPadding;
  }

  return {
    scale: newScale,
    x,
    y,
  };
}
