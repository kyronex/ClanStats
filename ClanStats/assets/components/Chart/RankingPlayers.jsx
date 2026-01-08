import ChartRankingPlayers from "./ChartRankingPlayers.jsx";
import { SelectorPlayersContainer, WarsList, PlayersList } from "../../components";

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
      <SelectorPlayersContainer
        warsStats={warsStats}
        playersAnalysisStats={playersAnalysisStats}
        handlePlayersSelect={handlePlayersSelect}
        handleWarSelect={handleWarSelect}
        maxPlayers={null}
        maxWars={null}
        enablePlayerSelectAll={true}
        enableWarSelectAll={true}
      >
        <WarsList />
        <ChartRankingPlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
        <PlayersList />
      </SelectorPlayersContainer>
    </React.Fragment>
  );
};

export default RankingPlayers;
