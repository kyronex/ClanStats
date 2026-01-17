import { useCallback, useState } from "react";
const useChartDataSelection = (rData) => {
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
  return {
    filteredPlayers,
    warsSelected,
    handlePlayersSelect,
    handleWarsSelect,
    playersAnalysisStats,
    warsStats,
  };
};

export { useChartDataSelection };
