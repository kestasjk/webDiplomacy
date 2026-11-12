import * as React from "react";
import axios from "axios";
import ApiRoute from "../../enums/ApiRoute";
import { gameOverview } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import { postGameApiFormRequest, QueryParams } from "../../utils/api";
import sendGameAnalyticsEvent from "../../utils/analytics";
import WDButton from "./WDButton";

const WDJoinPanel: React.FC = function (): React.ReactElement | null {
  const { gameID, join, leave, phase, phaseMinutes, playerTypes, variant } =
    useAppSelector(gameOverview);
  const [inviteCode, setInviteCode] = React.useState("");
  const [chosenCountryID, setChosenCountryID] = React.useState<number | null>(
    null,
  );
  const [error, setError] = React.useState<string | null>(null);
  const [isPosting, setIsPosting] = React.useState(false);

  const isPreGame = phase === "Pre-game";

  const post = async (
    route: ApiRoute,
    params: QueryParams,
    question: string,
    onSuccess: () => void,
  ) => {
    // eslint-disable-next-line no-alert
    if (!window.confirm(question)) return;
    setIsPosting(true);
    setError(null);
    try {
      await postGameApiFormRequest(route, params);
      onSuccess();
    } catch (e) {
      // api.php answers a refusal with its reason as plain text
      const reason =
        axios.isAxiosError(e) && typeof e.response?.data === "string"
          ? e.response.data
          : "";
      setError(reason || "Something went wrong, please try again.");
      setIsPosting(false);
    }
  };

  if (leave) {
    if (!leave.canLeave) {
      return (
        <div className="text-xs">
          (Can&apos;t leave game; {leave.cantLeaveReason}.)
        </div>
      );
    }
    return (
      <div className="flex flex-col items-center">
        <WDButton
          color="secondary"
          disabled={isPosting}
          onClick={() =>
            post(
              ApiRoute.LEAVE_GAME,
              { gameID: String(gameID) },
              "Are you sure you want to leave this game?",
              () => {
                // The game is erased if no-one else is in it, so go home as board.php does
                window.location.href = "/";
              },
            )
          }
        >
          Leave game
        </WDButton>
        {error && <div className="m-2 text-red-600 text-xs">{error}</div>}
      </div>
    );
  }

  if (!join) return null;

  const cdCountryIDs = join.civilDisorderCountryIDs;
  // A country chosen earlier may have been taken over by someone else since
  const countryID =
    chosenCountryID !== null && cdCountryIDs.includes(chosenCountryID)
      ? chosenCountryID
      : cdCountryIDs[0];
  const countryName = (id: number) => variant.countries[id - 1];

  const countryCount = variant.countries.length;
  const whenItStarts =
    phaseMinutes < 60
      ? `The game will start at the scheduled time even if all ${countryCount} players have joined.`
      : `The game will start when all ${countryCount} players have joined.`;
  const question = isPreGame
    ? `Are you sure you want to join this game?\n\n${whenItStarts}`
    : `Are you sure you want to take over ${countryName(countryID)}?`;

  const params: QueryParams = { gameID: String(gameID) };
  if (!isPreGame) params.countryID = String(countryID);
  if (join.needsInviteCode) params.gamepass = inviteCode;

  return (
    <div className="flex flex-col items-center text-sm space-y-2">
      {join.minimumReliabilityRating > 0 && (
        <div className="text-xs">
          Required reliability: {join.minimumReliabilityRating}%
        </div>
      )}
      {join.canJoin ? (
        <>
          {isPreGame ? (
            <div>
              Bet to join: <em>{join.bet} points</em>
            </div>
          ) : (
            <label htmlFor="wd-join-country">
              Take over:{" "}
              <select
                id="wd-join-country"
                // Room on the right for the drop-down arrow, which the form styles draw in the padding
                className="border border-black rounded pl-1 pr-8"
                value={countryID}
                onChange={(e) => setChosenCountryID(Number(e.target.value))}
              >
                {cdCountryIDs.map((id) => (
                  <option key={id} value={id}>
                    {countryName(id)}
                  </option>
                ))}
              </select>
            </label>
          )}
          {join.needsInviteCode && (
            <label htmlFor="wd-join-invite-code">
              Invite code:{" "}
              <input
                id="wd-join-invite-code"
                type="password"
                className="border border-black rounded px-1"
                size={10}
                value={inviteCode}
                onChange={(e) => setInviteCode(e.target.value)}
              />
            </label>
          )}
          <WDButton
            disabled={isPosting || (join.needsInviteCode && !inviteCode)}
            onClick={() =>
              post(ApiRoute.JOIN_GAME, params, question, () => {
                sendGameAnalyticsEvent("join_game", playerTypes);
                window.location.reload();
              })
            }
          >
            {isPreGame ? "Join" : "Take over"}
          </WDButton>
        </>
      ) : (
        <div className="text-xs">({join.cantJoinReason})</div>
      )}
      {error && <div className="text-red-600 text-xs">{error}</div>}
    </div>
  );
};

export default WDJoinPanel;
