import { useState, useCallback, useMemo, useEffect } from "react";
const useChartPlayerByWar = (warsSelected, playersSelected, warsStats, playersAnalysisStats) => {
  //const currentWar = Array.from(warsSelected)[0];
  const currentWar = useMemo(() => (warsSelected.size > 0 ? Array.from(warsSelected)[0] : null), [warsSelected]);

  const selectablePlayers = useMemo(() => {
    const processedData = {};
    if (!playersAnalysisStats) return processedData;
    if (!warsStats) return processedData;
    if (warsSelected.size === 0) return processedData;
    if (!warsStats[currentWar].players) return processedData;

    for (const playerTag of warsStats[currentWar].players) {
      if (processedData[playerTag]) continue;
      const playerStats = playersAnalysisStats.find(([key]) => key === playerTag)?.[1];
      if (playerStats?.scoresFinal?.[currentWar]) {
        processedData[playerTag] = playerStats;
      }
    }
    return processedData;
  }, [currentWar, warsStats, warsSelected, playersAnalysisStats]);

  const filteredPlayers = useMemo(() => {
    let processedData = {};
    if (!playersAnalysisStats) return processedData;
    if (!warsStats) return processedData;
    if (warsSelected.size === 0) return processedData;
    if (playersSelected.size === 0) return processedData;

    for (const playerTag of playersSelected) {
      const playerStats = selectablePlayers[playerTag];
      if (playerStats !== undefined) {
        processedData[playerTag] = playerStats;
      }
    }
    return processedData;
  }, [playersAnalysisStats, warsStats, playersSelected, warsSelected, selectablePlayers]);

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
    currentWar,
    selectablePlayers,
    filteredPlayers,
    getValidSelectedPlayers,
  };
};

export default useChartPlayerByWar;
