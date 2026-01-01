import ChartRankingPlayers from "./ChartRankingPlayers.jsx";
import SelectorByWar from "./SelectorByWar.jsx";
import React, { useState, useCallback } from "react";
const RankingPlayers = ({ rData }) => {
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
        maxPlayers={5}
        maxWars={1}
        enablePlayerSelectAll={true}
        enableWarSelectAll={false}
      />
      <ChartRankingPlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
    </React.Fragment>
  );
};

export default RankingPlayers;
