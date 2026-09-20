import * as React from "react";
import { useAppSelector } from "../../state/hooks";
import {
  gameOverview,
  playerActiveGames,
} from "../../state/game/game-api-slice";
import WDVerticalScroll from "./WDVerticalScroll";
import { formatPSYForDisplay } from "../../utils/formatPhaseForDisplay";
import { getPhaseSeasonYear } from "../../utils/state/getPhaseSeasonYear";
import WDOrderStatusIcon from "./WDOrderStatusIcon";
import getOrderStates from "../../utils/state/getOrderStates";
import { getFormattedTimeLeft } from "../../utils/formatTime";

interface GameProps {
  game: any;
  className?: string;
}

function Game({ game, className }: GameProps) {
  return (
    <div
      className={`${className} rounded-lg border border-gray-300 mb-3 px-4 py-2 text-xs drop-shadow-sm bg-white`}
    >
      <a className="flex" href={`?gameID=${game.gameID}`}>
        <div className="flex-1">
          <div className="text-lg font-bold">{game.name}</div>
          <div className="mt-0">
            {/* <div>Starts in 44:00:00, 2 days / phase,</div> */}
            <div>
              {formatPSYForDisplay(getPhaseSeasonYear(game.turn, game.phase))}
            </div>
            {/* <div>Pot: 35 - Pre-Game Phase,</div>
            <div>Classic, Bot Game, Unranked</div> */}
          </div>
        </div>
        <div className="text-right">
          <div className="text-sm font-bold">
            {getFormattedTimeLeft(game.processTime).replace(" remaining", "")}
          </div>
          <div className="mt-2">
            {/* Players <br />
            {game.players.length} of 7 */}
          </div>
        </div>
      </a>
      <div className="mt-3">
        {/* {game.players.map((player: any, index: number) => (
          <span key={player.id}>
            {player.name}
            {index < game.players.length - 1 && <>,&nbsp;</>}
          </span>
        ))} */}
      </div>
      {/* {game.moves && (
        <div className="mt-3 uppercase">
          {game.moves.map((move: any, index: number) => (
            <span key={move.country} className={`${index > 0 && "ml-3"}`}>
              {move.country}&nbsp;{move.number}
            </span>
          ))}
        </div>
      )} */}
    </div>
  );
}

Game.defaultProps = {
  className: undefined,
};

const WDGamesList: React.FC = function (): React.ReactElement {
  const activeGames = useAppSelector(playerActiveGames);
  const overview = useAppSelector(gameOverview); // just for country mapping

  const countries = Object.fromEntries(
    overview.members.map((m) => [m.countryID, m.country]),
  );

  return (
    <WDVerticalScroll>
      <div className="items-center px-4 mt-4">
        {activeGames
          .filter((game) => game.gameID !== overview.gameID)
          .map((game) => (
            <Game key={game.gameID} game={game} />
          ))}
      </div>
    </WDVerticalScroll>
  );
};

export default WDGamesList;
