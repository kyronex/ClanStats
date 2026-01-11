import ChartRankingPlayers from "./ChartRankingPlayers.jsx";
import { SelectorPlayersContainer, WarsList, PlayersList } from "../../components";

import React, { useState, useCallback } from "react";
const RankingPlayers = ({ rData }) => {
  const [filteredPlayers, setFilteredPlayers] = useState({});
  const [warsSelected, setWarsSelected] = useState({});

  const handlePlayersSelect = useCallback((players) => {
    setFilteredPlayers(players);
  }, []);

  const handleWarsSelect = useCallback((wars) => {
    setWarsSelected(wars);
  }, []);
  const playersAnalysisStats = rData?.data.playersAnalysisStats;
  const warsStats = rData?.data.warsStats;
  return (
    <React.Fragment>
      <SelectorPlayersContainer
        warsStats={warsStats}
        playersAnalysisStats={playersAnalysisStats}
        handleWarsSelect={handleWarsSelect}
        handlePlayersSelect={handlePlayersSelect}
        maxWars={null}
        maxPlayers={null}
        enableWarSelectAll={true}
        enablePlayerSelectAll={true}
      >
        <WarsList />
        <ChartRankingPlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
        <PlayersList />
      </SelectorPlayersContainer>
    </React.Fragment>
  );
};

export default RankingPlayers;
