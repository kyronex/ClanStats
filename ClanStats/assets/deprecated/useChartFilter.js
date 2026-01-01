import { useMemo } from "react";
const useChartFilter = (playersSelected, warsSelected, playersAnalysisStats, warsStats) => {
  const filteredData = useMemo(() => {
    let processedData = {};
    if (!playersAnalysisStats) return processedData;
    if (!warsStats) return processedData;
    if (playersSelected.size === 0) return processedData;
    if (warsSelected.size === 0) return processedData;

    for (const playerTag of Object.keys(playersSelected)) {
      if (processedData[playerTag]) continue;
      const playerStats = playersAnalysisStats[playerTag];
      if (!playerStats?.originalStats?.wars) continue;
      for (const warKey of warsSelected) {
        if (playerStats.originalStats.wars[warKey]) {
          processedData[playerTag] = playerStats;
          break;
        }
      }
    }
    return processedData;
  }, [playersAnalysisStats, warsStats, playersSelected, warsSelected]);

  return {
    filteredData,
  };
};

export default useChartFilter;
