import React, { ReactElement, FunctionComponent, useEffect } from "react";
import { useFormik, FormikProvider } from "formik";
import useLocalStorageState from "use-local-storage-state";
import WDVerticalScroll from "./WDVerticalScroll";
import { CountryTableData } from "../../interfaces/CountryTableData";
import WDVoteButtons from "./WDVoteButtons";
import Checkbox from "./form/Checkbox";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import { setVoteStatus } from "../../state/game/game-api-slice";
import Vote from "../../enums/Vote";
import { useAppSelector, useAppDispatch } from "../../state/hooks";

interface SettingsProps {
  autoSave: boolean;
}

interface WDSettingsProps {
  allCountries: CountryTableData[];
  gameID: GameOverviewResponse["gameID"];
  maxDelays: GameOverviewResponse["excusedMissedTurns"];
  userCountry: CountryTableData | null;
  gameIsFinished: boolean;
  gameIsPaused: boolean;
}

const WDHelp: FunctionComponent<WDSettingsProps> = function ({
  allCountries,
  gameID,
  maxDelays,
  userCountry,
  gameIsFinished,
  gameIsPaused,
}): ReactElement {
  const dispatch = useAppDispatch();

  const [settings, setSettings] = useLocalStorageState<SettingsProps>(
    "settings",
    { defaultValue: { autoSave: true } },
  );

  const votingInProgress = useAppSelector(
    (state) => state.game.votingInProgress,
  );

  const toggleVote = (voteKey: Vote) => {
    if (userCountry) {
      const desiredVoteOn = userCountry.votes.includes(voteKey) ? "No" : "Yes";
      dispatch(
        setVoteStatus({
          countryID: String(userCountry.countryID),
          gameID: String(gameID),
          vote: voteKey,
          voteOn: desiredVoteOn,
        }),
      );
    }
  };
  const form = useFormik({
    initialValues: settings,
    onSubmit: (values: SettingsProps) => {
      setSettings(values);
    },
  });

  useEffect(() => {
    if (form.values !== form.initialValues) {
      form.submitForm();
    }
  }, [form.values]);

  return (
    <WDVerticalScroll>
      <FormikProvider value={form}>
        {userCountry && !gameIsFinished && (
          <div className="pl-4">
            <WDVoteButtons
              toggleVote={toggleVote}
              voteState={userCountry.votes}
              votingInProgress={votingInProgress}
              gameIsPaused={gameIsPaused}
            />
          </div>
        )}
        <div className="mt-3 px-3 sm:px-4">
          <div className="text-xs">Automatically Save Game:</div>
          <Checkbox
            name="autoSave"
            label={`Auto-Save ${form.values.autoSave ? "On" : "Off"}`}
            checked={form.values.autoSave}
            className="mt-3"
          />
        </div>
      </FormikProvider>
    </WDVerticalScroll>
  );
};

export default WDHelp;
