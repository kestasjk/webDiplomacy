import * as React from "react";
import { Box } from "@mui/material";
import { GameMessage } from "../../state/interfaces/GameMessages";
import countryMap from "../../data/map/variants/classic/CountryMap";
import { CountryTableData } from "../../interfaces/CountryTableData";

interface WDMessageProps {
  message: GameMessage;
  countries: CountryTableData[];
}

const WDMessage: React.FC<WDMessageProps> = function ({
  message,
  countries,
}): React.ReactElement {
  const padding = "6px";
  const margin = "6px";

  const getCountry = (countryID: number) =>
    countries.find((cand) => cand.countryID === countryID);
  const fromCountry = getCountry(message.fromCountryID);
  const toCountry = getCountry(message.toCountryID);
  const msgWidth = "200px";
  const justify =
    message.fromCountryID === fromCountry?.countryID ? "flex-end" : "flex";

  return (
    <Box>
      <Box
        id={`message-${String(message.timeSent)}`}
        sx={{
          p: padding,
          m: margin,
          bgcolor: "#eeeeee",
          borderRadius: 3,
          display: "flex-right",
          justifyContent: justify,
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
