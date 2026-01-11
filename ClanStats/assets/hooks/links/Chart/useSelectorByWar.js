import { useCallback, useMemo } from "react";
const useSelectorByWar = (warsSelected, playersSelected, warsStats, playersAnalysisStats) => {
  const currentWars = useMemo(() => (warsSelected.size > 0 ? Array.from(warsSelected) : null), [warsSelected]);

  const selectablePlayers = useMemo(() => {
    const processedData = {};
    if (!playersAnalysisStats) return processedData;
    if (!warsStats) return processedData;
    if (!currentWars) return processedData;
    for (const warKey of currentWars) {
      if (!warsStats[warKey]?.players) continue;
      for (const playerTag of warsStats[warKey].players) {
        if (processedData[playerTag]) continue;
        const playerStats = playersAnalysisStats[playerTag];
        if (playerStats?.scoresFinal?.[warKey]) {
          processedData[playerTag] = playerStats;
        }
      }
    }
    return processedData;
  }, [currentWars, warsStats, playersAnalysisStats]);

  const filteredPlayers = useMemo(() => {
    let processedData = {};
    if (!playersAnalysisStats) return processedData;
    if (!warsStats) return processedData;
    if (!currentWars) return processedData;
    if (playersSelected.size === 0) return processedData;

    for (const playerTag of playersSelected) {
      const playerStats = selectablePlayers[playerTag];
      if (playerStats !== undefined) {
        processedData[playerTag] = playerStats;
      }
    }
    return processedData;
  }, [currentWars, warsStats, playersAnalysisStats, playersSelected, selectablePlayers]);

  const getValidSelectedPlayers = useCallback(
    (playerTagsSet = null) => {
      if (!playerTagsSet) {
        playerTagsSet = playersSelected;
      }
      if (Object.keys(selectablePlayers).length === 0) {
        return new Set();
      }
      const filteredSet = new Set();
      for (const playerTag of playerTagsSet) {
        if (selectablePlayers[playerTag] !== undefined) {
          filteredSet.add(playerTag);
        }
      }
      return filteredSet;
    },
    [playersSelected, selectablePlayers]
  );

  return {
    currentWars,
    selectablePlayers,
    filteredPlayers,
    getValidSelectedPlayers,
  };
};

export { useSelectorByWar };
