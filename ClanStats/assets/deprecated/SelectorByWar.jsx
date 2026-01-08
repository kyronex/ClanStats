import { useToggleSet, useSelectorByWar } from "../hooks";
import React, { memo, useEffect, useRef, useState } from "react";

const SelectorByWar = ({
  warsStats,
  playersAnalysisStats,
  handlePlayersSelect,
  handleWarSelect,
  maxPlayers = null, // null = illimité, 5 = limite à 5
  maxWars = 1,
  enablePlayerSelectAll = false, // true = affiche checkbox "Tous"
  enableWarSelectAll = false,
}) => {
  const playersToggleOptions = maxPlayers ? { maxSize: maxPlayers } : {};
  const warsToggleOptions = maxWars ? { maxSize: maxWars } : {};
  const {
    set: playersSelected,
    hash: playersSelectedHash,
    has: isPlayersSelected,
    toggle: handleSelectedPlayer,
    replace: replacePlayersSelected,
    clear: clearPlayersSelected,
  } = useToggleSet([], playersToggleOptions);

  const {
    set: warsSelected,
    hash: warsSelectedHash,
    has: isWarsSelected,
    toggle: handleSelectedWar,
    replace: replaceWarsSelected,
    clear: clearWarsSelected,
  } = useToggleSet([], warsToggleOptions);

  const { currentWar, selectablePlayers, filteredPlayers, getValidSelectedPlayers } = useSelectorByWar(
    warsSelected,
    playersSelected,
    warsStats,
    playersAnalysisStats
  );

  const prevHashsRef = useRef({ wars: "", players: "" });
  const [selectAllPlayers, setSelectAllPlayers] = useState(false);
  const [selectAllWars, setSelectAllWars] = useState(false);

  const handleSelectAllPlayers = () => {
    if (!enablePlayerSelectAll) return;
    const selectableCount = Object.keys(selectablePlayers).length;
    const effectiveMax = maxPlayers ? Math.min(maxPlayers, selectableCount) : selectableCount;
    const currentlyAllPlayersSelected = playersSelected.size === effectiveMax;

    if (currentlyAllPlayersSelected) {
      setSelectAllPlayers(false);
      clearPlayersSelected();
    } else {
      const playersToSelect = maxPlayers ? Object.keys(selectablePlayers).slice(0, maxPlayers) : Object.keys(selectablePlayers);
      console.log("playersToSelect", playersToSelect);
      setSelectAllPlayers(true);
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
      setSelectAllWars(false);
      clearWarsSelected();
    } else {
      const warsToSelect = maxWars ? availableWars.slice(0, maxWars) : availableWars;
      setSelectAllWars(true);
      replaceWarsSelected(warsToSelect);
    }
  };

  useEffect(() => {
    if (!enablePlayerSelectAll || !currentWar) {
      setSelectAllPlayers(false);
      return;
    }
    const selectableCount = Object.keys(selectablePlayers).length;
    const effectiveMax = maxPlayers || selectableCount;
    const allSelected = selectableCount > 0 && playersSelected.size === Math.min(selectableCount, effectiveMax);
    setSelectAllPlayers(allSelected);
  }, [enablePlayerSelectAll, currentWar, selectablePlayers, playersSelected.size, maxPlayers]);

  useEffect(() => {
    if (!enableWarSelectAll) {
      setSelectAllWars(false);
      return;
    }
    const availableWars = Object.entries(warsStats).filter(([key]) => key.match(/^\d+_\d+$/));
    const selectableCount = availableWars.length;
    const effectiveMax = maxWars || selectableCount;
    const allSelected = selectableCount > 0 && warsSelected.size === Math.min(selectableCount, effectiveMax);
    setSelectAllWars(allSelected);
  }, [enableWarSelectAll, warsStats, warsSelected.size, maxWars]);

  useEffect(() => {
    if (!currentWar) return;
    const validPlayersSet = getValidSelectedPlayers(playersSelected);
    const needsSync = validPlayersSet.size !== playersSelected.size || ![...validPlayersSet].every((tag) => playersSelected.has(tag));
    if (needsSync) {
      replacePlayersSelected([...validPlayersSet]);
    }
  }, [currentWar, playersSelected, getValidSelectedPlayers, replacePlayersSelected]);

  useEffect(() => {
    if (warsSelected.size === 0) return;
    const hashesChanged = prevHashsRef.current.wars !== warsSelectedHash || prevHashsRef.current.players !== playersSelectedHash;
    if (!hashesChanged) return;
    prevHashsRef.current = { wars: warsSelectedHash, players: playersSelectedHash };
    if (playersSelected.size === 0) {
      handlePlayersSelect({});
    } else if (Object.keys(filteredPlayers).length > 0) {
      handlePlayersSelect(filteredPlayers);
      handleWarSelect(warsSelected);
    }
  }, [playersSelectedHash, warsSelectedHash, filteredPlayers, playersSelected.size, warsSelected]);

  return (
    <React.Fragment>
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
          gap: "10px",
          padding: "10px",
        }}
      >
        {enableWarSelectAll && (
          <div>
            <label>
              <input type="checkbox" checked={selectAllWars} onChange={handleSelectAllWars} />
              <span>Toutes {maxWars ? `(max ${maxWars})` : ""}</span>
            </label>
          </div>
        )}
        {Object.entries(warsStats)
          .filter(([key]) => key.match(/^\d+_\d+$/))
          .map(([warKey, warData], index) => {
            return (
              <WarItem key={warKey} rowId={warKey} war={warData} isWarsSelected={isWarsSelected} handleSelectedWar={handleSelectedWar} />
            );
          })}
      </div>
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(120px, 1fr))",
          gap: "10px",
          padding: "10px",
        }}
      >
        {enablePlayerSelectAll && currentWar && (
          <div>
            <label>
              <input type="checkbox" checked={selectAllPlayers} onChange={handleSelectAllPlayers} />
              <span>
                Tous
                {maxPlayers && ` (max ${maxPlayers})`}
                {` (${Object.keys(selectablePlayers).length})`}
              </span>
            </label>
          </div>
        )}
        {Object.entries(selectablePlayers)?.map(([playerTag, playerData], index) => {
          return (
            <PlayerItem
              key={playerTag}
              rowId={playerTag}
              player={playerData}
              isPlayersSelected={isPlayersSelected}
              handleSelectedPlayer={handleSelectedPlayer}
            />
          );
        })}
      </div>
    </React.Fragment>
  );
};

const WarItem = memo(function WarItem({ rowId, war, isWarsSelected, handleSelectedWar }) {
  const handleChange = () => handleSelectedWar(rowId);
  return (
    <div>
      <span>{war.sessionId}</span>
      <input type="checkbox" value={rowId} checked={isWarsSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

const PlayerItem = memo(function PlayerItem({ rowId, player, isPlayersSelected, handleSelectedPlayer }) {
  const handleChange = () => handleSelectedPlayer(rowId);
  return (
    <div>
      <span>{player.originalStats.name}</span>
      <input type="checkbox" value={rowId} checked={isPlayersSelected(rowId)} onChange={handleChange} />
    </div>
  );
});

export default SelectorByWar;
