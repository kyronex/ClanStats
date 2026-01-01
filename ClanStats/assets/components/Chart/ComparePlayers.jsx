import ChartComparePlayers from "./ChartComparePlayers.jsx";
import SelectorByWar from "./SelectorByWar.jsx";
import React, { useState, useCallback } from "react";
const ComparePlayers = ({ rData }) => {
  const [filteredPlayers, setFilteredPlayers] = useState({});
  const [warsSelected, setWarsSelected] = useState({});

  const handlePlayersSelect = useCallback((players) => {
    setFilteredPlayers(players);
  }, []);

  const handleWarSelect = useCallback((wars) => {
    setWarsSelected(wars);
  }, []);
  const playersAnalysisStats = rData?.data.playersAnalysisStats;
  const warsStats = rData?.data.warsStats;
  return (
    <React.Fragment>
      <SelectorByWar
        playersAnalysisStats={playersAnalysisStats}
        warsStats={warsStats}
        handlePlayersSelect={handlePlayersSelect}
        handleWarSelect={handleWarSelect}
      />
      <ChartComparePlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
    </React.Fragment>
  );
};

export default ComparePlayers;
