import ChartCompareTopPlayers from "./ChartCompareTopPlayers.jsx";
import ChartCompareScorePlayers from "./ChartCompareScorePlayers.jsx";
import { SelectorPlayersContainer, WarsList, PlayersList } from "../../components";
import React, { useState, useCallback } from "react";

//TODO decouper ChartComparePlayers en ChartCompareTopPlayers et ChartCompareScorePlayers
const ComparePlayers = ({ rData }) => {
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
        handlePlayersSelect={handlePlayersSelect}
        handleWarsSelect={handleWarsSelect}
        maxPlayers={5}
        maxWars={1}
        enablePlayerSelectAll={true}
        enableWarSelectAll={false}
      >
        <WarsList />
        <div style={{ display: "flex", gap: "20px", padding: "20px" }}>
          <ChartCompareScorePlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
          <ChartCompareTopPlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
        </div>
        <PlayersList />
      </SelectorPlayersContainer>
    </React.Fragment>
  );
};

export default ComparePlayers;
