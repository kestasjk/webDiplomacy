import * as React from "react";
import DOMPurify from "dompurify";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { turnAsDate } from "../../utils/formatTime";
import { GameMessage } from "../../state/interfaces/GameMessages";
import { CountryTableData } from "../../interfaces/CountryTableData";

interface WDMessageProps {
  message: GameMessage;
  userCountry: CountryTableData | null;
  allCountries: CountryTableData[];
  viewedPhaseIdx: number;
}

const WDMessage: React.FC<WDMessageProps> = function ({
  message,
  userCountry,
  allCountries,
  viewedPhaseIdx,
}): React.ReactElement {
  const padding = "10px";
  const margin = "6px";

  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;

  const getCountry = (countryID: number) =>
    allCountries.find((cand) => cand.countryID === countryID);
  const fromCountry = getCountry(message.fromCountryID);
  const msgWidth = mobileLandscapeLayout ? "170px" : "250px";
  const justify =
    userCountry && message.fromCountryID === userCountry.countryID
      ? "end"
      : "start";
  const msgTime = new Date(0);
  msgTime.setUTCSeconds(message.timeSent);
  return (
    <div className={`flex justify-${justify}`}>
      <div
        id={`message-${message.timeSent}`}
        className={`p-3 m-2 bg-slate-100 max-w-[${msgWidth}] rounded-lg`}
      >
        <div className="flex-col">
          <div>
            {/* Dynamic JIT is not working if is not previously declared.
            https://github.com/tailwindlabs/tailwindcss/discussions/6763
            Do not delete the line below. */}
            <div className="hidden max-w-[170px] max-w-[250px] justify-end justify-start" />
            <span style={{ color: fromCountry?.color, fontWeight: "bold" }}>
              {fromCountry?.country.toUpperCase().slice(0, 3)}
            </span>
            {": "}
            {/* Here's a robust but dangerous choice... 
            The messages are all sanitized in gamemessage.php, and newlines
            converted to <br/>
            */}
            <span
              dangerouslySetInnerHTML={{
                __html: DOMPurify.sanitize(message.message, {
                  ALLOWED_TAGS: ["br", "strong"],
                }),
              }}
            />
          </div>
          <div className="flex text-xs mt-1 text-gray-500 italic">
            <div className="flex-1">
              {message.turn < viewedPhaseIdx && (
                <>{turnAsDate(message.turn, "Classic")}</>
              )}
            </div>
            <div className="ml-4">
              {msgTime.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WDMessage;
