import { useCallback, useState } from "react";
import { StatsHistoriqueClanWarApiResponse, PlayerStats } from "../../../types";

const useChartDataSelection = (rData: StatsHistoriqueClanWarApiResponse | null) => {
  const [filteredPlayers, setFilteredPlayers] = useState<{ [key: string]: PlayerStats }>({});
  const [warsSelected, setWarsSelected] = useState<Set<string>>(new Set());
  const handlePlayersSelect = useCallback((players: { [key: string]: PlayerStats }) => {
    setFilteredPlayers(players);
  }, []);

  const handleWarsSelect = useCallback((wars: Set<string>) => {
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
