import * as React from "react";
import { Box, LinearProgress } from "@mui/material";

const WDTransition: React.FC = function (): React.ReactElement {
  return (
    <Box
      sx={{
        backgroundColor: "rgba(0,0,0,.8)",
        height: "100%",
        width: "100%",
        position: "fixed",
        left: "0px",
        top: "0px",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        flexDirection: "column",
      }}
    >
      <Box
        sx={{
          display: "flex",
          flexDirection: "column",
          justifyContent: "center",
          alignItems: "center",
        }}
      >
        <Box
          component="img"
          src="beta-src/src/assets/png/web-diplomacy-logo.png"
          sx={{
            filter: `box-shadow: "0px 0px 250px rgba(255, 255, 255, 0.5)"`,
            height: "568px",
            width: "568px",
          }}
        />
        <Box
          sx={{
            color: "#ffffff",
            position: "relative",
            height: "21px",
            width: "150px",
            fontSize: "14px",
            letterSpacing: "0.6em",
            textAlign: "center",
            top: "-130px",
          }}
        >
          LOADING
          <Box
            sx={{
              position: "relative",
              top: "16px",
            }}
          >
            <LinearProgress
              sx={{
                background: `linear-gradient(to right, #8D7C41, #CBB97B, #FFFFFF)`,
              }}
            />
          </Box>
        </Box>
      </Box>
      <Box
        sx={{
          borderRadius: "20px",
          backgroundColor: "#31312D",
          bottom: 30,
          color: "#ffffff",
          fontSize: "14px",
          fontWeight: 400,
          height: 60,
          left: 30,
          lineHeight: 2.5,
          m: 1,
          position: "fixed",
          textAlign: "center",
          width: "145px",
        }}
      >
        {/* Nested box to be able to add an icon in later easily if wanted */}
        <Box
          sx={{
            height: "42px",
            width: "117px",
            lineHeight: "18px",
            whiteSpace: "normal",
            position: "relative",
            top: "11px",
            textAlign: "left",
            left: "21px",
          }}
        >
          Seasons change, so does loyalty
        </Box>
      </Box>
    </Box>
  );
};

export default WDTransition;
