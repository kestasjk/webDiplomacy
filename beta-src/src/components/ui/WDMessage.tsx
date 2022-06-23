import * as React from "react";
import { Box, Stack } from "@mui/material";
import DOMPurify from "dompurify";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { GameMessage } from "../../state/interfaces/GameMessages";
import { CountryTableData } from "../../interfaces/CountryTableData";

interface WDMessageProps {
  message: GameMessage;
  userCountry: CountryTableData | null;
  allCountries: CountryTableData[];
}

const WDMessage: React.FC<WDMessageProps> = function ({
  message,
  userCountry,
  allCountries,
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
      ? "flex-end"
      : "flex";
  const msgTime = new Date(0);
  msgTime.setUTCSeconds(message.timeSent);
  return (
    <Box sx={{ display: "flex", justifyContent: justify }}>
      <Box
        id={`message-${String(message.timeSent)}`}
        sx={{
          p: padding,
          m: margin,
          bgcolor: "#eeeeee",
          borderRadius: 3,
          maxWidth: msgWidth,
        }}
      >
        <Stack direction="column">
          <Box>
            <span style={{ color: fromCountry?.color, fontWeight: "bold" }}>
              {fromCountry?.country.toUpperCase().slice(0, 3)}
            </span>
            {": "}
            <span
              dangerouslySetInnerHTML={{
                __html: DOMPurify.sanitize(message.message, {
                  ALLOWED_TAGS: ["br", "strong"],
                }),
              }}
            />
          </Box>
          <Box style={{ color: "#888888", fontStyle: "italic" }}>
            {msgTime.toLocaleTimeString([], {
              hour: "2-digit",
              minute: "2-digit",
            })}
          </Box>
        </Stack>
      </Box>
    </Box>
  );
};

export default WDMessage;
