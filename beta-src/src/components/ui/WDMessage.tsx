import * as React from "react";
import { Box } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { GameMessage } from "../../state/interfaces/GameMessages";
import countryMap from "../../data/map/variants/classic/CountryMap";
import { CountryTableData } from "../../interfaces/CountryTableData";

interface WDMessageProps {
  message: GameMessage;
  userCountry: CountryTableData;
  countries: CountryTableData[];
}

const WDMessage: React.FC<WDMessageProps> = function ({
  message,
  userCountry,
  countries,
}): React.ReactElement {
  const padding = "6px";
  const margin = "6px";

  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;

  const getCountry = (countryID: number) =>
    countries.find((cand) => cand.countryID === countryID);
  const fromCountry = getCountry(message.fromCountryID);
  const toCountry = getCountry(message.toCountryID);
  const msgWidth = mobileLandscapeLayout ? "170px" : "250px";
  const justify =
    message.fromCountryID === userCountry.countryID ? "flex-end" : "flex";

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
        <span style={{ color: fromCountry?.color, fontWeight: "bold" }}>
          {fromCountry?.country.toUpperCase().slice(0, 3)}
        </span>
        {": "}
        {message.message}
      </Box>
    </Box>
  );
};

export default WDMessage;
