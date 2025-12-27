import ChartComparePlayers from "./ChartComparePlayers.jsx";
import SelectorComparePlayers from "./SelectorComparePlayers.jsx";
import { useToggleSet } from "../../hooks";
import { useChartFilter } from "../../hooks";
import React, { memo } from "react";
const ComparePlayers = ({ rData }) => {
  const { toggle: handleSelectedPlayer, has: isPlayersSelected, set: playersSelected } = useToggleSet([], { maxSize: 5 });
  const { toggle: handleSelectedWar, has: isWarsSelected, set: warsSelected } = useToggleSet([], { maxSize: 1 });
  const { filteredData } = useChartFilter(playersSelected, warsSelected, rData?.data.playersAnalysisStats, rData?.data.warsStats);

  const playersAnalysisStats = rData?.data.playersAnalysisStats;
  const warsStats = rData?.data.warsStats;
  return (
    <React.Fragment>
      <ChartComparePlayers
        warsStats={warsStats}
        filteredData={filteredData}
        playersSelected={playersSelected}
        warsSelected={warsSelected}
      />
      <SelectorComparePlayers
        playersAnalysisStats={playersAnalysisStats}
        warsStats={warsStats}
        isWarsSelected={isWarsSelected}
        handleSelectedWar={handleSelectedWar}
        isPlayersSelected={isPlayersSelected}
        handleSelectedPlayer={handleSelectedPlayer}
      />
    </React.Fragment>
  );
};

export default ComparePlayers;
