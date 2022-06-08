import * as React from "react";
import { useState, useEffect } from "react";
import { Box, Stack } from "@mui/material";
import {
  gameOrdersMeta,
  gameOverview,
  gameStatus,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import WDArmyIcon from "./units/WDArmyIcon";
import UIState from "../../enums/UIState";
import Country from "../../enums/Country";
import { UNIT_HEIGHT, UNIT_WIDTH } from "./units/WDUnit";

const range = (N: number) => Array.from(Array(N).keys());

const WDBuildCounts: React.FC = function (): React.ReactElement {
  const { user, phase } = useAppSelector(gameOverview);
  const { phases } = useAppSelector(gameStatus);
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const ordersMeta = useAppSelector(gameOrdersMeta);

  const isCurrent = viewedPhaseIdx >= phases.length - 1;
  if (phase !== "Builds" || !isCurrent) return <Box />;
  const extraSCs = user ? user.member.supplyCenterNo - user.member.unitNo : 0;
  const numBuildOrders = Object.values(ordersMeta).filter((o) =>
    o.update?.type.startsWith("Build"),
  ).length;
  const numDestroyOrders = Object.values(ordersMeta).filter((o) =>
    o.update?.type.startsWith("Destroy"),
  ).length;
  const numRemainingBuilds = Math.max(extraSCs - numBuildOrders, 0);
  const numRemainingDestroys = Math.max(-extraSCs - numDestroyOrders, 0);
  const UNIT_WIDTH_SQUOOSHED = 40; // need to squoosh to avoid increasing HUD width
  return (
    <Box
      sx={{
        display: "block",
        p: 0,
        mt: 2,
      }}
    >
      <Stack>
        {range(numRemainingBuilds).map((buildIdx) => (
          <svg
            key={buildIdx}
            style={{
              height: UNIT_HEIGHT,
              width: UNIT_WIDTH_SQUOOSHED,
              overflow: "visible",
            }}
          >
            <WDArmyIcon country={Country.FRANCE} iconState={UIState.BUILD} />
          </svg>
        ))}
        {range(numRemainingDestroys).map((buildIdx) => (
          <svg
            key={buildIdx}
            style={{
              height: UNIT_HEIGHT,
              width: UNIT_WIDTH_SQUOOSHED,
              overflow: "visible",
            }}
          >
            <WDArmyIcon country={Country.FRANCE} iconState={UIState.DESTROY} />
          </svg>
        ))}
      </Stack>
    </Box>
  );
};

export default WDBuildCounts;
