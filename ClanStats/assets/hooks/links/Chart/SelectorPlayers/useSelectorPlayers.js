import { useEffect, useMemo, useRef } from "react";
import { useToggleSet, useSelectorByWar } from "../../../../hooks";
// TODO revoir les exposition des variable dans value
// 🔹 Hook principal : retourne contexte
function useSelectorPlayers({
  warsStats,
  playersAnalysisStats,
  handleWarsSelect, // callback optionnel
  handlePlayersSelect,
  maxWars = 1,
  maxPlayers = null,
  enableWarSelectAll = false,
  enablePlayerSelectAll = false,
}) {
  const playersToggleOpts = maxPlayers ? { maxSize: maxPlayers } : {};
  const warsToggleOpts = maxWars ? { maxSize: maxWars } : {};

  const {
    set: playersSelected,
    hash: playersSelectedHash,
    has: isPlayersSelected,
    toggle: handleSelectedPlayer,
    replace: replacePlayersSelected,
    clear: clearPlayersSelected,
  } = useToggleSet([], playersToggleOpts);

  const {
    set: warsSelected,
    hash: warsSelectedHash,
    has: isWarsSelected,
    toggle: handleSelectedWar,
    replace: replaceWarsSelected,
    clear: clearWarsSelected,
  } = useToggleSet([], warsToggleOpts);

  const { currentWars, selectablePlayers, filteredPlayers, getValidSelectedPlayers } = useSelectorByWar(
    warsSelected,
    playersSelected,
    warsStats,
    playersAnalysisStats
  );

  const selectAllPlayers = useMemo(() => {
    if (!enablePlayerSelectAll || !currentWars) return false;
    const selectableCount = Object.keys(selectablePlayers).length;
    const effectiveMax = maxPlayers || selectableCount;
    return selectableCount > 0 && playersSelected.size === Math.min(selectableCount, effectiveMax);
  }, [enablePlayerSelectAll, currentWars, selectablePlayers, playersSelected.size, maxPlayers]);

  const selectAllWars = useMemo(() => {
    if (!enableWarSelectAll) return false;
    const availableWars = Object.entries(warsStats).filter(([key]) => key.match(/^\d+_\d+$/));
    const selectableCount = availableWars.length;
    const effectiveMax = maxWars || selectableCount;
    return selectableCount > 0 && warsSelected.size === Math.min(selectableCount, effectiveMax);
  }, [enableWarSelectAll, warsStats, warsSelected.size, maxWars]);

  const handleSelectAllPlayers = () => {
    if (!enablePlayerSelectAll) return;
    const selectableCount = Object.keys(selectablePlayers).length;
    const effectiveMax = maxPlayers ? Math.min(maxPlayers, selectableCount) : selectableCount;
    const currentlyAllPlayersSelected = playersSelected.size === effectiveMax;

    if (currentlyAllPlayersSelected) {
      clearPlayersSelected();
    } else {
      const playersToSelect = maxPlayers ? Object.keys(selectablePlayers).slice(0, maxPlayers) : Object.keys(selectablePlayers);
      replacePlayersSelected(playersToSelect);
    }
  };

  const handleSelectAllWars = () => {
    if (!enableWarSelectAll) return;
    const availableWars = Object.entries(warsStats)
      .filter(([key]) => key.match(/^\d+_\d+$/))
      .map(([key]) => key);
    const selectableCount = availableWars.length;
    const effectiveMax = maxWars ? Math.min(maxWars, selectableCount) : selectableCount;
    const currentlyAllSelected = warsSelected.size === effectiveMax;

    if (currentlyAllSelected) {
      clearWarsSelected();
    } else {
      const warsToSelect = maxWars ? availableWars.slice(0, maxWars) : availableWars;
      replaceWarsSelected(warsToSelect);
    }
  };

  useEffect(() => {
    if (!currentWars) return;
    const validPlayersSet = getValidSelectedPlayers(playersSelected);
    const needsSync = validPlayersSet.size !== playersSelected.size || ![...validPlayersSet].every((tag) => playersSelected.has(tag));
    if (needsSync) {
      replacePlayersSelected([...validPlayersSet]);
    }
  }, [currentWars, playersSelected, getValidSelectedPlayers, replacePlayersSelected]);

  const prevHashsRef = useRef({ wars: null, players: null });

  useEffect(() => {
    if (warsSelected.size === 0) return;
    const hashesChanged = prevHashsRef.current.wars !== warsSelectedHash || prevHashsRef.current.players !== playersSelectedHash;
    if (!hashesChanged) return;
    prevHashsRef.current = { wars: warsSelectedHash, players: playersSelectedHash };
    if (playersSelected.size === 0) {
      handlePlayersSelect({});
    } else if (Object.keys(filteredPlayers).length > 0) {
      handlePlayersSelect(filteredPlayers);
      handleWarsSelect(warsSelected);
    }
  }, [playersSelectedHash, warsSelectedHash, filteredPlayers, playersSelected.size, warsSelected]);

  const value = {
    warsStats,
    playersAnalysisStats,
    handleWarsSelect,
    handlePlayersSelect,
    maxWars,
    maxPlayers,
    enableWarSelectAll,
    enablePlayerSelectAll,

    playersSelected,
    isPlayersSelected,
    handleSelectedPlayer,
    replacePlayersSelected,
    clearPlayersSelected,

    warsSelected,
    isWarsSelected,
    handleSelectedWar,
    replaceWarsSelected,
    clearWarsSelected,

    currentWars,
    selectablePlayers,
    filteredPlayers,
    getValidSelectedPlayers,

    selectAllPlayers,
    selectAllWars,

    handleSelectAllPlayers,
    handleSelectAllWars,
  };

  return { value };
}
export { useSelectorPlayers };
