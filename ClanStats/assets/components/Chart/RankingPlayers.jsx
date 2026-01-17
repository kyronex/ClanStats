import ChartRankingPlayers from "./ChartRankingPlayers.jsx";
import ChartSingleRankingPlayers from "./ChartSingleRankingPlayers.jsx";
import { SelectorPlayersContainer, WarsList, PlayersList } from "../../components";

import React from "react";
import { useChartDataSelection } from "../../hooks";

const RankingPlayers = ({ rData }) => {
  const { filteredPlayers, warsSelected, handlePlayersSelect, handleWarsSelect, playersAnalysisStats, warsStats } =
    useChartDataSelection(rData);

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

        {warsSelected.size === 1 ? (
          <ChartSingleRankingPlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
        ) : (
          <ChartRankingPlayers warsStats={warsStats} filteredData={filteredPlayers} warsSelected={warsSelected} />
        )}
        <PlayersList />
      </SelectorPlayersContainer>
    </React.Fragment>
  );
};

export default RankingPlayers;
