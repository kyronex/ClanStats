import ChartCompareTopPlayers from "./ChartCompareTopPlayers.jsx";
import ChartCompareScorePlayers from "./ChartCompareScorePlayers.jsx";
import { SelectorPlayersContainer, WarsList, PlayersList } from "../../components";
import React from "react";
import { useChartDataSelection } from "../../hooks";

const ComparePlayers = ({ rData }) => {
  const { filteredPlayers, warsSelected, handlePlayersSelect, handleWarsSelect, playersAnalysisStats, warsStats } =
    useChartDataSelection(rData);

  return (
    <React.Fragment>
      <SelectorPlayersContainer
        warsStats={warsStats}
        playersAnalysisStats={playersAnalysisStats}
        handleWarsSelect={handleWarsSelect}
        handlePlayersSelect={handlePlayersSelect}
        maxWars={1}
        maxPlayers={5}
        enableWarSelectAll={false}
        enablePlayerSelectAll={true}
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
